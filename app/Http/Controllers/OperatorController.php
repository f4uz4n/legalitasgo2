<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OperatorController extends Controller
{
    public function index()
    {
        $operator = User::query()
            ->where('role', 'operator')
            ->orderBy('name')
            ->get();

        return view('operator.index', compact('operator'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'operator',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('operator.index')->with('success', 'Akun operator berhasil dibuat. Operator dapat login dan memakai fitur pengelolaan yang sama.');
    }

    public function update(Request $request, User $user)
    {
        $this->pastikanOperator($user);
        $operator = $user;

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($operator->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'is_active' => 'nullable|boolean',
        ]);

        $operator->name = $data['name'];
        $operator->email = $data['email'];
        $operator->is_active = $request->boolean('is_active');
        $operator->role = 'operator';
        if (!empty($data['password'])) {
            $operator->password = $data['password'];
        }
        $operator->save();

        return redirect()->route('operator.index')->with('success', 'Akun operator berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $this->pastikanOperator($user);
        $user->delete();

        return redirect()->route('operator.index')->with('success', 'Akun operator berhasil dihapus.');
    }

    private function pastikanOperator(User $operator): void
    {
        if (!$operator->isOperator()) {
            abort(404);
        }
    }
}
