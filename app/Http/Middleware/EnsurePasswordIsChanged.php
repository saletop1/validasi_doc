<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // --- PERBAIKAN: Menggunakan nama kolom yang benar 'password_changed_at' ---
        if (is_null($user->password_changed_at) && !$request->routeIs(['password.edit', 'password.update', 'logout']))
        {
            // Arahkan paksa ke halaman ganti password
            return redirect()->route('password.edit')->with('warning', 'Anda harus mengubah password sementara Anda sebelum melanjutkan.');
        }

        return $next($request);
    }
}
