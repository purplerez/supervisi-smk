<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Sekolah;
use App\Models\User;
use App\Models\UserRole;
use App\Tenant\TenantContext;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AdminSekolahManager extends Component
{
    public Sekolah $sekolah;

    public $modalFormTerbuka = false;

    public $modalResetTerbuka = false;

    public $user_id = null;

    public $nama = '';

    public $username = '';

    public $email = '';

    public $aktif = true;

    // Untuk menampilkan password baru setelah reset atau buat baru
    public $newPassword = '';

    public $showPasswordModal = false;

    public function mount($id)
    {
        TenantContext::clear();
        $this->sekolah = Sekolah::findOrFail($id);
    }

    public function rules()
    {
        return [
            'nama' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')
                    ->where('sekolah_id', $this->sekolah->id)
                    ->ignore($this->user_id),
            ],
            'email' => 'nullable|email|max:255',
            'aktif' => 'boolean',
        ];
    }

    public function getAdminsProperty()
    {
        // Ambil semua user di sekolah ini yang memiliki role 'admin'
        return User::tanpaTenant()
            ->where('sekolah_id', $this->sekolah->id)
            ->whereHas('roles', function ($q) {
                $q->where('role', 'admin');
            })
            ->get();
    }

    public function tambah()
    {
        $this->reset(['user_id', 'nama', 'username', 'email']);
        $this->aktif = true;
        $this->resetValidation();
        $this->modalFormTerbuka = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $admin = User::tanpaTenant()->findOrFail($id);

        $this->user_id = $admin->id;
        $this->nama = $admin->nama;
        $this->username = $admin->username;
        $this->email = $admin->email;
        $this->aktif = $admin->aktif;

        $this->modalFormTerbuka = true;
    }

    public function simpan()
    {
        $this->validate();

        // Validasi minimal 1 admin aktif jika mencoba menonaktifkan
        if ($this->user_id && ! $this->aktif) {
            $activeAdminsCount = User::tanpaTenant()
                ->where('sekolah_id', $this->sekolah->id)
                ->where('id', '!=', $this->user_id)
                ->where('aktif', true)
                ->whereHas('roles', function ($q) {
                    $q->where('role', 'admin');
                })
                ->count();

            if ($activeAdminsCount < 1) {
                $this->addError('aktif', 'Sekolah harus selalu punya minimal satu admin aktif.');

                return;
            }
        }

        $passwordAsli = Str::random(8);

        if ($this->user_id) {
            $admin = User::tanpaTenant()->findOrFail($this->user_id);
            $admin->update([
                'nama' => $this->nama,
                'username' => $this->username,
                'email' => $this->email,
                'aktif' => $this->aktif,
            ]);
            $this->modalFormTerbuka = false;
            session()->flash('pesan', 'Data admin berhasil diperbarui.');
        } else {
            $admin = User::tanpaTenant()->create([
                'sekolah_id' => $this->sekolah->id,
                'nama' => $this->nama,
                'username' => $this->username,
                'email' => $this->email,
                'password' => Hash::make($passwordAsli),
                'must_change_password' => true,
                'aktif' => $this->aktif,
            ]);

            // Assign role
            UserRole::create([
                'sekolah_id' => $this->sekolah->id,
                'user_id' => $admin->id,
                'role' => 'admin',
            ]);

            $this->modalFormTerbuka = false;
            $this->newPassword = $passwordAsli;
            $this->showPasswordModal = true;
            session()->flash('pesan', 'Admin berhasil ditambahkan.');
        }
    }

    public function konfirmasiReset($id)
    {
        $this->user_id = $id;
        $this->modalResetTerbuka = true;
    }

    public function resetPassword()
    {
        $admin = User::tanpaTenant()->findOrFail($this->user_id);
        $passwordBaru = Str::random(8);

        $admin->update([
            'password' => Hash::make($passwordBaru),
            'must_change_password' => true,
        ]);

        $this->modalResetTerbuka = false;
        $this->newPassword = $passwordBaru;
        $this->showPasswordModal = true;
    }

    public function render()
    {
        return view('livewire.super-admin.admin-sekolah-manager')->layout('layouts.app', ['title' => 'Kelola Admin Sekolah']);
    }
}
