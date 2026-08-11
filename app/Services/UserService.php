<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Divisi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserService
{
    /**
     * Build the user listing data with search & filters.
     */
    public function getIndexData(Request $request): array
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

        return [
            'users' => $query->latest()->paginate(15)->withQueryString(),
            'roles' => Role::all(),
            'divisi' => Divisi::all(),
            'filters' => $request->only(['search', 'role', 'status_akun']),
        ];
    }

    /**
     * Build the form data (roles & divisions) for create/edit forms.
     */
    public function getFormData(): array
    {
        return [
            'roles' => Role::all(),
            'divisi' => Divisi::all(),
        ];
    }

    /**
     * Create a new user with role assignment and audit log.
     */
    public function create(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'nik_nim' => $data['nik_nim'] ?? null,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'divisi_id' => $data['divisi_id'] ?? null,
            'status_akun' => $data['status_akun'],
        ]);

        $user->assignRole($data['role']);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Create User',
            'arsip_id' => null,
            'ip_address' => request()->ip(),
            'details' => "Membuat pengguna baru '{$user->name}' dengan role '{$data['role']}'.",
        ]);

        return $user;
    }

    /**
     * Build the edit form data.
     */
    public function getEditData(User $user): array
    {
        return array_merge(
            ['user' => $user->load('roles')],
            $this->getFormData()
        );
    }

    /**
     * Update an existing user with role sync and audit log.
     */
    public function update(array $data, User $user): User
    {
        $updateData = [
            'name' => $data['name'],
            'nik_nim' => $data['nik_nim'] ?? null,
            'email' => $data['email'],
            'divisi_id' => $data['divisi_id'] ?? null,
            'status_akun' => $data['status_akun'],
        ];

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        // Sync role (replace old role)
        $user->syncRoles([$data['role']]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Update User',
            'arsip_id' => null,
            'ip_address' => request()->ip(),
            'details' => "Memperbarui data pengguna '{$user->name}' (Status: {$data['status_akun']}, Role: {$data['role']}).",
        ]);

        return $user;
    }

    /**
     * Deactivate (soft delete) a user account.
     *
     * @return array{success: bool, message: string}
     */
    public function deactivate(User $user): array
    {
        // Prevent deleting own account
        if ($user->id === Auth::id()) {
            return [
                'success' => false,
                'message' => 'Anda tidak dapat menghapus akun Anda sendiri.',
            ];
        }

        $user->update(['status_akun' => 'Deactivated']);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Deactivate User',
            'arsip_id' => null,
            'ip_address' => request()->ip(),
            'details' => "Menonaktifkan akun pengguna '{$user->name}' ({$user->email}).",
        ]);

        return [
            'success' => true,
            'message' => "Akun pengguna '{$user->name}' telah dinonaktifkan.",
        ];
    }

    /**
     * Import users in bulk from a CSV file.
     * Expected CSV columns: name, nik_nim, email, password, divisi_id, role
     */
    public function importCsv(Request $request): string
    {
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
                'name' => $data['name'],
                'nik_nim' => $data['nik_nim'] ?? null,
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'divisi_id' => ! empty($data['divisi_id']) ? $data['divisi_id'] : null,
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
        if (! empty($errors)) {
            $message .= ' Beberapa baris dilewati: '.implode(' | ', $errors);
        }

        return $message;
    }
}
