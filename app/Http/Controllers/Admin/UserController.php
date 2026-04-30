<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Users/Index', [
            'users' => User::orderBy('name')->get(['id', 'name', 'email', 'role', 'created_at']),
            'roles' => array_column(Role::cases(), 'value'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/Users/Form', [
            'roles' => array_column(Role::cases(), 'value'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role'     => ['required', 'in:user,colaborador,admin,super'],
        ]);

        User::create([
            ...$data,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index', [], 303)->with('success', 'Usuario creado.');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('admin/Users/Form', [
            'user'  => $user->only('id', 'name', 'email', 'role'),
            'roles' => array_column(Role::cases(), 'value'),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', "unique:users,email,{$user->id}"],
            'role'     => ['required', 'in:user,colaborador,admin,super'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user->name  = $data['name'];
        $user->email = $data['email'];
        $user->role  = $data['role'];

        if (filled($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return redirect()->route('admin.users.index', [], 303)->with('success', 'Usuario actualizado.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $user->delete();

        return redirect()->route('admin.users.index', [], 303)->with('success', 'Usuario eliminado.');
    }
}
