<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request; // Tambahkan ini

class Authenticate extends Middleware
{

    protected function redirectTo($request) // Hapus type hint Request jika ada error
    {
        // Jika request BUKAN AJAX, arahkan ke halaman login
        if (! $request->expectsJson()) {
            return route('login');
        }
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
