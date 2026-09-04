<?php

namespace App\Http\Middleware;

use App\Models\Outlet;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'username' => $request->user()->username,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role ?? 'owner',
                    'phone' => $request->user()->phone,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'message' => fn () => $request->session()->get('message'),
            ],
            'outlet' => fn () => rescue(fn () => Outlet::first([
                'id',
                'name',
                'phone',
                'address',
                'receipt_header',
                'receipt_footer',
                'receipt_paper_size',
                'is_wa_enabled',
            ]), null, false),
            'appName' => config('app.name'),
        ];
    }
}
