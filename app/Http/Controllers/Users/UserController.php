<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::withCount('orders')
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'in:owner,cashier,washer,courier'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;

        User::create($validated);

        return back()->with('success', "Akun staf {$validated['name']} berhasil dibuat.");
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username,' . $user->id],
            'email' => ['nullable', 'email', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'in:owner,cashier,washer,courier'],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['required', 'boolean'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return back()->with('success', "Data staf {$user->name} berhasil diperbarui.");
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return back()->with('success', 'Akun staf berhasil dihapus.');
    }
}

