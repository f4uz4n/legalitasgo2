<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman edit profil pengguna.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        if ($user->relationLoaded('penghuni')) {
            $user->load('penghuni.perumahan');
        }

        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Memperbarui informasi profil dan kata sandi dalam satu formulir terpadu.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password'         => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required'                     => 'Nama lengkap wajib diisi.',
            'email.required'                    => 'Alamat email wajib diisi.',
            'email.unique'                      => 'Alamat email ini sudah digunakan oleh akun lain.',
            'current_password.required_with'    => 'Kata sandi saat ini wajib diisi jika Anda ingin mengubah kata sandi.',
            'current_password.current_password' => 'Kata sandi saat ini tidak cocok dengan catatan kami.',
            'password.min'                      => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed'                => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $user->name  = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('success', 'Profil dan informasi akun berhasil diperbarui!');
    }

    /**
     * Fitur Nonaktifkan Akun Pengguna (bukan hapus permanen).
     */
    public function deactivate(Request $request): RedirectResponse
    {
        $request->validate([
            'confirm_password' => ['required', 'current_password'],
        ], [
            'confirm_password.required'         => 'Masukkan kata sandi akun Anda untuk konfirmasi penonaktifan.',
            'confirm_password.current_password' => 'Kata sandi yang Anda masukkan salah.',
        ]);

        $user = $request->user();
        $user->is_active = false;
        $user->save();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/login')->with('status', 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator perumahan jika ingin mengaktifkannya kembali.');
    }
}
