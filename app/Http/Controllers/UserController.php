<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function __construct(private UserService $userService) {}

    /**
     * Display a listing of all users.
     */
    public function index(Request $request)
    {
        return Inertia::render('Users/Index', $this->userService->getIndexData($request));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return Inertia::render('Users/Create', $this->userService->getFormData());
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nik_nim' => ['nullable', 'string', 'max:50', 'unique:users,nik_nim'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'divisi_id' => ['nullable', 'exists:divisi,id'],
            'role' => ['required', 'exists:roles,name'],
            'status_akun' => ['required', 'in:Aktif,Suspended,Deactivated'],
        ]);

        $user = $this->userService->create($request->all());

        return redirect()->route('users.index')->with('success', "Pengguna '{$user->name}' berhasil dibuat.");
    }

    /**
     * Show the form for editing a user.
     */
    public function edit(User $user)
    {
        return Inertia::render('Users/Edit', $this->userService->getEditData($user));
    }

    /**
     * Update an existing user.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nik_nim' => ['nullable', 'string', 'max:50', 'unique:users,nik_nim,'.$user->id],
            'email' => ['required', 'email', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'divisi_id' => ['nullable', 'exists:divisi,id'],
            'role' => ['required', 'exists:roles,name'],
            'status_akun' => ['required', 'in:Aktif,Suspended,Deactivated'],
        ]);

        $user = $this->userService->update($request->all(), $user);

        return redirect()->route('users.index')->with('success', "Data pengguna '{$user->name}' berhasil diperbarui.");
    }

    /**
     * Deactivate (soft delete) a user account.
     */
    public function destroy(User $user)
    {
        $result = $this->userService->deactivate($user);

        return redirect()->route('users.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Import users in bulk from a CSV file.
     * Expected CSV columns: name, nik_nim, email, password, divisi_id, role
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $message = $this->userService->importCsv($request);

        return redirect()->route('users.index')->with('success', $message);
    }
}
