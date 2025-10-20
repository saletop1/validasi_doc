<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Menampilkan formulir untuk membuat pengguna baru.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Menyimpan pengguna baru yang dibuat oleh admin.
     */
    public function store(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'name' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // 2. Buat user baru
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // --- PERBAIKAN: Login otomatis dihapus ---
        // Baris `Auth::login($user);` telah dihapus dari sini
        // agar admin tidak login sebagai user baru.

        // 3. Redirect kembali dengan pesan sukses
        return redirect()->route('users.create')->with('success', 'Pengguna baru berhasil dibuat!');
    }
}

