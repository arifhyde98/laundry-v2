import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useCartStore = defineStore('cart', () => {
  const customer = ref(null);
  const items = ref([]);
  const discountType = ref('fixed'); // fixed (Rp) or percentage (%)
  const discountValue = ref(0);
  const deliveryFee = ref(0);
  const paymentMethod = ref('cash');
  const paymentType = ref('paid'); // 'paid' (Lunas), 'partial' (DP), 'unpaid' (Bayar Nanti)
  const paidAmount = ref(0);
  const notes = ref('');

  // Add Item to Cart
  function addItem(service, qty = 1, customName = '', customPrice = null) {
    const unitPrice = customPrice !== null ? customPrice : Number(service.price);
    const itemName = customName || service.name;
    
    // Check if already in cart with same service
    const existingIndex = items.value.findIndex(i => i.service_id === service.id && i.item_name === itemName);
    if (existingIndex > -1) {
      items.value[existingIndex].quantity += Number(qty);
      items.value[existingIndex].subtotal = items.value[existingIndex].quantity * items.value[existingIndex].unit_price;
    } else {
      items.value.push({
        service_id: service.id,
        service_name: service.name,
        unit: service.unit,
        item_name: itemName,
        quantity: Number(qty),
        unit_price: unitPrice,
        subtotal: Number(qty) * unitPrice,
        notes: '',
      });
    }
  }

  function updateQty(index, newQty) {
    if (newQty <= 0) {
      removeItem(index);
      return;
    }
    items.value[index].quantity = Number(newQty);
    items.value[index].subtotal = items.value[index].quantity * items.value[index].unit_price;
  }

  function removeItem(index) {
    items.value.splice(index, 1);
  }

  function clearCart() {
    customer.value = null;
    items.value = [];
    discountValue.value = 0;
    deliveryFee.value = 0;
    paidAmount.value = 0;
    notes.value = '';
    paymentType.value = 'paid';
    paymentMethod.value = 'cash';
  }

  // Computed Totals
  const subtotal = computed(() => {
    return items.value.reduce((sum, item) => sum + item.subtotal, 0);
  });

  const totalWeightQty = computed(() => {
    return items.value.reduce((sum, item) => sum + item.quantity, 0);
  });

  const discountAmount = computed(() => {
    if (discountType.value === 'percentage') {
      return (subtotal.value * Number(discountValue.value)) / 100;
    }
    return Math.min(subtotal.value, Number(discountValue.value));
  });

  const grandTotal = computed(() => {
    return Math.max(0, subtotal.value - discountAmount.value + Number(deliveryFee.value));
  });

  return {
    customer,
    items,
    discountType,
    discountValue,
    deliveryFee,
    paymentMethod,
    paymentType,
    paidAmount,
    notes,
    addItem,
    updateQty,
    removeItem,
    clearCart,
    subtotal,
    totalWeightQty,
    discountAmount,
    grandTotal,
  };
});

