<?php

namespace App\Http\Controllers\RealEstate\Global;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController
{
    public function showLogin()
    {
        if (session()->get('global_admin.authenticated')) {
            return redirect()->route('global.admin');
        }

        return view('realestate.global.admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $email = config('tenancy.super_admin.email');
        $password = config('tenancy.super_admin.password');

        if ($credentials['email'] !== $email || $credentials['password'] !== $password) {
            throw ValidationException::withMessages([
                'email' => __('Invalid credentials'),
            ]);
        }

        $request->session()->put('global_admin.authenticated', true);
        $request->session()->put('global_admin.email', $email);

        return redirect()->route('global.admin');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('global_admin');

        return redirect()->route('global.login');
    }
}

