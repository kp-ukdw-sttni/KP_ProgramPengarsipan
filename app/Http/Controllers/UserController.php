<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Divisi;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of all users.
     */
    public function index(Request $request)
    {
        $query = User::with(['divisi', 'roles']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nik_nim', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $request->role));
        }

        if ($request->filled('status_akun')) {
            $query->where('status_akun', $request->status_akun);
        }

        $users = $query->latest()->paginate(15);
        $roles = Role::all();
        $divisi = Divisi::all();

        return view('users.index', compact('users', 'roles', 'divisi'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::all();
        $divisi = Divisi::all();
        return view('users.create', compact('roles', 'divisi'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'nik_nim'    => ['nullable', 'string', 'max:50', 'unique:users,nik_nim'],
            'email'      => ['required', 'email', 'unique:users,email'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
            'divisi_id'  => ['nullable', 'exists:divisi,id'],
            'role'       => ['required', 'exists:roles,name'],
            'status_akun'=> ['required', 'in:Aktif,Suspended,Deactivated'],
        ]);

        $user = User::create([
            'name'        => $request->name,
            'nik_nim'     => $request->nik_nim,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'divisi_id'   => $request->divisi_id,
            'status_akun' => $request->status_akun,
        ]);

        $user->assignRole($request->role);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'Create User',
            'arsip_id'   => null,
            'ip_address' => request()->ip(),
            'details'    => "Membuat pengguna baru '{$user->name}' dengan role '{$request->role}'.",
        ]);

        return redirect()->route('users.index')->with('success', "Pengguna '{$user->name}' berhasil dibuat.");
    }

    /**
     * Show the form for editing a user.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $divisi = Divisi::all();
        return view('users.edit', compact('user', 'roles', 'divisi'));
    }

    /**
     * Update an existing user.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'nik_nim'     => ['nullable', 'string', 'max:50', 'unique:users,nik_nim,' . $user->id],
            'email'       => ['required', 'email', 'unique:users,email,' . $user->id],
            'password'    => ['nullable', 'string', 'min:8', 'confirmed'],
            'divisi_id'   => ['nullable', 'exists:divisi,id'],
            'role'        => ['required', 'exists:roles,name'],
            'status_akun' => ['required', 'in:Aktif,Suspended,Deactivated'],
        ]);

        $data = [
            'name'        => $request->name,
            'nik_nim'     => $request->nik_nim,
            'email'       => $request->email,
            'divisi_id'   => $request->divisi_id,
            'status_akun' => $request->status_akun,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Sync role (replace old role)
        $user->syncRoles([$request->role]);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'Update User',
            'arsip_id'   => null,
            'ip_address' => request()->ip(),
            'details'    => "Memperbarui data pengguna '{$user->name}' (Status: {$request->status_akun}, Role: {$request->role}).",
        ]);

        return redirect()->route('users.index')->with('success', "Data pengguna '{$user->name}' berhasil diperbarui.");
    }

    /**
     * Deactivate (soft delete) a user account.
     */
    public function destroy(User $user)
    {
        // Prevent deleting own account
        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->update(['status_akun' => 'Deactivated']);

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'Deactivate User',
            'arsip_id'   => null,
            'ip_address' => request()->ip(),
            'details'    => "Menonaktifkan akun pengguna '{$user->name}' ({$user->email}).",
        ]);

        return redirect()->route('users.index')->with('success', "Akun pengguna '{$user->name}' telah dinonaktifkan.");
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

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        $imported = 0;
        $errors = [];
        $header = null;

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            // Skip header row
            if ($header === null) {
                $header = $row;
                continue;
            }

            $data = array_combine($header, $row);

            // Basic validation
            if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
                $errors[] = "Baris dengan email '{$data['email']}' dilewati: name, email, password wajib diisi.";
                continue;
            }

            if (User::where('email', $data['email'])->exists()) {
                $errors[] = "Email '{$data['email']}' sudah terdaftar, dilewati.";
                continue;
            }

            $user = User::create([
                'name'        => $data['name'],
                'nik_nim'     => $data['nik_nim'] ?? null,
                'email'       => $data['email'],
                'password'    => Hash::make($data['password']),
                'divisi_id'   => !empty($data['divisi_id']) ? $data['divisi_id'] : null,
                'status_akun' => 'Aktif',
            ]);

            $roleName = $data['role'] ?? 'Karyawan';
            if (Role::where('name', $roleName)->exists()) {
                $user->assignRole($roleName);
            } else {
                $user->assignRole('Karyawan');
            }

            $imported++;
        }

        fclose($handle);

        $message = "Berhasil mengimpor {$imported} pengguna.";
        if (!empty($errors)) {
            $message .= ' Beberapa baris dilewati: ' . implode(' | ', $errors);
        }

        return redirect()->route('users.index')->with('success', $message);
    }
}
