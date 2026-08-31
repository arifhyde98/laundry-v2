/**
 * Utility helper untuk memuat script Midtrans Snap secara dinamis
 */
export function loadMidtransScript(clientKey, mode = 'sandbox') {
  return new Promise((resolve, reject) => {
    if (window.snap) {
      resolve(window.snap);
      return;
    }

    const scriptId = 'midtrans-snap-script';
    const existingScript = document.getElementById(scriptId);

    if (existingScript) {
      existingScript.onload = () => resolve(window.snap);
      return;
    }

    const script = document.createElement('script');
    script.id = scriptId;
    script.src = mode === 'production' 
      ? 'https://app.midtrans.com/snap/snap.js'
      : 'https://app.sandbox.midtrans.com/snap/snap.js';
    
    if (clientKey) {
      script.setAttribute('data-client-key', clientKey);
    }

    script.onload = () => {
      if (window.snap) {
        resolve(window.snap);
      } else {
        reject(new Error('Gagal menginisialisasi Midtrans Snap SDK'));
      }
    };

    script.onerror = (err) => reject(err);
    document.body.appendChild(script);
  });
}

/**
 * Pemicu Pop-up Midtrans Snap
 */
export async function payWithMidtrans({ snapToken, clientKey, mode, onSuccess, onPending, onError, onClose }) {
  try {
    const snap = await loadMidtransScript(clientKey, mode);
    snap.pay(snapToken, {
      onSuccess: (result) => {
        if (onSuccess) onSuccess(result);
      },
      onPending: (result) => {
        if (onPending) onPending(result);
      },
      onError: (result) => {
        if (onError) onError(result);
      },
      onClose: () => {
        if (onClose) onClose();
      }
    });
  } catch (error) {
    console.error('Midtrans Snap Error:', error);
    alert('Gagal memuat sistem pembayaran Midtrans. Mohon periksa koneksi atau Server Key.');
  }
}
