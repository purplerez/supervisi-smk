<x-layouts.super-admin :title="$sekolah->nama">

    <x-page-header
        :title="$sekolah->nama"
        :subtitle="'Detail sekolah dan manajemen akun admin.'"
        :back="route('super-admin.sekolah.index')"
        backLabel="Kembali ke Daftar Sekolah"
    >
        <x-slot:actions>
            <x-button variant="edit" href="{{ route('super-admin.sekolah.edit', $sekolah) }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                </svg>
                Ubah Data Sekolah
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ===== KOLOM KIRI: Info Sekolah ===== --}}
        <div class="lg:col-span-1 space-y-4">

            {{-- Info Dasar --}}
            <x-card>
                <h2 class="text-lg font-bold mb-4" style="color: var(--color-navy-900);">Informasi Sekolah</h2>

                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider mb-1" style="color: var(--color-muted);">Status</dt>
                        <dd>
                            <x-badge-status :status="$sekolah->status === 'aktif' ? 'final' : 'belum'">
                                {{ $sekolah->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                            </x-badge-status>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider mb-1" style="color: var(--color-muted);">Kode Sekolah</dt>
                        <dd>
                            <code class="text-sm px-2 py-0.5 rounded font-mono" style="background: var(--color-navy-50); color: var(--color-navy-900);">
                                {{ $sekolah->kode }}
                            </code>
                        </dd>
                    </div>

                    @if($sekolah->npsn)
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider mb-1" style="color: var(--color-muted);">NPSN</dt>
                        <dd class="font-medium" style="color: var(--color-ink);">{{ $sekolah->npsn }}</dd>
                    </div>
                    @endif

                    @if($sekolah->alamat)
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider mb-1" style="color: var(--color-muted);">Alamat</dt>
                        <dd class="text-sm leading-relaxed" style="color: var(--color-ink);">{{ $sekolah->alamat }}</dd>
                    </div>
                    @endif
                </dl>
            </x-card>

            {{-- URL Login dengan tombol salin --}}
            <x-card>
                <h2 class="text-base font-bold mb-3" style="color: var(--color-navy-900);">Alamat Login Sekolah</h2>
                <p class="text-sm mb-3" style="color: var(--color-muted);">
                    Bagikan URL ini kepada admin dan guru sekolah untuk login.
                </p>

                <div
                    x-data="{
                        tersalin: false,
                        salin() {
                            const url = '{{ $urlLogin }}';
                            if (navigator.clipboard && window.isSecureContext) {
                                navigator.clipboard.writeText(url).then(() => {
                                    this.tersalin = true;
                                    setTimeout(() => this.tersalin = false, 2000);
                                }).catch(() => {
                                    this.fallbackSalin(url);
                                });
                            } else {
                                this.fallbackSalin(url);
                            }
                        },
                        fallbackSalin(url) {
                            try {
                                const textArea = document.createElement('textarea');
                                textArea.value = url;
                                textArea.style.position = 'fixed';
                                textArea.style.left = '-999999px';
                                textArea.style.top = '-999999px';
                                document.body.appendChild(textArea);
                                textArea.focus();
                                textArea.select();
                                document.execCommand('copy');
                                textArea.remove();
                                this.tersalin = true;
                                setTimeout(() => this.tersalin = false, 2000);
                            } catch (e) {
                                console.error('Gagal menyalin:', e);
                            }
                        }
                    }"
                    class="flex items-stretch gap-0 rounded-xl overflow-hidden"
                    style="border: 1px solid var(--color-border);"
                >
                    <div
                        class="flex-1 px-3 py-2.5 text-sm font-mono break-all select-all"
                        style="background: var(--color-navy-50); color: var(--color-navy-900);"
                        id="url-login-sekolah"
                    >{{ $urlLogin }}</div>
                    <button
                        type="button"
                        @click="salin()"
                        class="px-4 flex items-center gap-2 text-sm font-semibold transition-colors flex-shrink-0 cursor-pointer"
                        style="background: var(--color-navy-900); color: white;"
                        :class="tersalin ? 'opacity-80' : 'hover:opacity-90'"
                        aria-label="Salin URL login sekolah"
                        id="btn-salin-url-login"
                    >
                        <svg x-show="!tersalin" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M8 2a1 1 0 000 2h2a1 1 0 100-2H8z"/>
                            <path d="M3 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v6h-4.586l1.293-1.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L10.414 13H15v3a2 2 0 01-2 2H5a2 2 0 01-2-2V5zM15 11h2a1 1 0 110 2h-2v-2z"/>
                        </svg>
                        <svg x-show="tersalin" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" style="display:none;">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span x-text="tersalin ? 'Tersalin!' : 'Salin'"></span>
                    </button>
                </div>
            </x-card>
        </div>

        {{-- ===== KOLOM KANAN: Manajemen Admin ===== --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Form Tambah Admin --}}
            <x-card>
                <h2 class="text-lg font-bold mb-4" style="color: var(--color-navy-900);">Tambah Akun Admin</h2>

                <form
                    method="POST"
                    action="{{ route('super-admin.sekolah.admin.store', $sekolah) }}"
                    id="form-tambah-admin"
                    novalidate
                >
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-input
                            name="nama"
                            label="Nama Lengkap"
                            :value="old('nama')"
                            required
                            autocomplete="off"
                        />
                        <x-input
                            name="username"
                            label="Username"
                            :value="old('username')"
                            required
                            autocomplete="off"
                            :hint="'Unik per sekolah. Digunakan untuk login.'"
                        />
                        <x-input
                            name="email"
                            label="Email (opsional)"
                            type="email"
                            :value="old('email')"
                            autocomplete="off"
                        />
                        <x-input
                            name="password"
                            label="Password Awal"
                            type="password"
                            required
                            :hint="'Admin wajib mengganti password saat pertama login.'"
                        />
                    </div>

                    <div class="mt-5 flex items-center gap-3">
                        <x-button type="submit" variant="tambah" id="btn-tambah-admin">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"/>
                            </svg>
                            Tambah Admin
                        </x-button>
                        <span class="text-sm" style="color: var(--color-muted);">
                            Sekolah boleh memiliki lebih dari satu admin aktif.
                        </span>
                    </div>
                </form>
            </x-card>

            {{-- Daftar Admin --}}
            <x-card>
                <h2 class="text-lg font-bold mb-4" style="color: var(--color-navy-900);">
                    Akun Admin Sekolah
                    <span class="ml-2 text-sm font-normal" style="color: var(--color-muted);">({{ $admins->count() }} akun)</span>
                </h2>

                @if($admins->isEmpty())
                    <x-empty-state
                        title="Belum ada admin"
                        description="Sekolah ini belum memiliki akun admin. Tambahkan menggunakan form di atas."
                    />
                @else
                    <div class="space-y-3">
                        @foreach($admins as $admin)
                            <div
                                class="rounded-xl p-4 border"
                                style="
                                    border-color: {{ $admin->aktif ? 'var(--color-border)' : '#FDE8D4' }};
                                    background: {{ $admin->aktif ? '#ffffff' : '#FFF8F5' }};
                                "
                                id="admin-{{ $admin->id }}"
                            >
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    {{-- Info admin --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-semibold text-base" style="color: var(--color-navy-900);">
                                                {{ $admin->nama }}
                                            </span>
                                            @if(!$admin->aktif)
                                                <x-badge-status status="belum">Nonaktif</x-badge-status>
                                            @elseif($admin->must_change_password)
                                                <x-badge-status status="draft">Wajib Ganti Password</x-badge-status>
                                            @else
                                                <x-badge-status status="final">Aktif</x-badge-status>
                                            @endif
                                        </div>
                                        <div class="mt-1 text-sm space-y-0.5" style="color: var(--color-muted);">
                                            <div>Username: <code class="text-xs px-1 rounded" style="background: var(--color-navy-50); color: var(--color-navy-900);">{{ $admin->username }}</code></div>
                                            @if($admin->email)
                                                <div>Email: {{ $admin->email }}</div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Aksi admin --}}
                                    <div class="flex flex-wrap items-center gap-2" x-data="{ modalReset{{ $admin->id }}: false }">

                                        {{-- Tombol Reset Password --}}
                                        <button
                                            type="button"
                                            @click="modalReset{{ $admin->id }} = true"
                                            class="btn btn-outline btn-sm"
                                            id="btn-reset-password-{{ $admin->id }}"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                            </svg>
                                            Atur Ulang Password
                                        </button>

                                        {{-- Tombol Nonaktifkan / Aktifkan --}}
                                        @if($admin->aktif)
                                            <x-modal-konfirmasi
                                                :id="'modal-nonaktifkan-' . $admin->id"
                                                title="Nonaktifkan Admin"
                                                :pesan="'Apakah Anda yakin ingin menonaktifkan akun ' . $admin->nama . '? Admin ini tidak akan bisa login sampai diaktifkan kembali.'"
                                                labelKonfirm="Nonaktifkan"
                                                variantKonfirm="hapus"
                                                :action="route('super-admin.sekolah.admin.nonaktifkan', [$sekolah, $admin])"
                                                method="PATCH"
                                            />

                                            <button
                                                type="button"
                                                class="btn btn-hapus btn-sm"
                                                @click="$dispatch('buka-modal-modal-nonaktifkan-{{ $admin->id }}')"
                                                id="btn-nonaktifkan-{{ $admin->id }}"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524L13.477 14.89zm1.414-1.414A6 6 0 006.524 5.11L14.89 13.476zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                                                </svg>
                                                Nonaktifkan
                                            </button>
                                        @else
                                            <form method="POST" action="{{ route('super-admin.sekolah.admin.aktifkan', [$sekolah, $admin]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <x-button
                                                    type="submit"
                                                    variant="primary"
                                                    size="sm"
                                                    id="btn-aktifkan-{{ $admin->id }}"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Aktifkan
                                                </x-button>
                                            </form>
                                        @endif

                                        {{-- Modal Reset Password (inline) --}}
                                        <div
                                            x-show="modalReset{{ $admin->id }}"
                                            class="fixed inset-0 z-50 flex items-center justify-center p-4"
                                            style="background: rgba(0,0,0,0.5); display:none;"
                                            @keydown.escape.window="modalReset{{ $admin->id }} = false"
                                            role="dialog"
                                            aria-modal="true"
                                            aria-labelledby="modal-reset-title-{{ $admin->id }}"
                                        >
                                            <div
                                                class="w-full max-w-md rounded-2xl shadow-2xl p-6"
                                                style="background: #ffffff;"
                                                @click.stop
                                            >
                                                <h3 class="text-lg font-bold mb-2" style="color: var(--color-navy-900);" id="modal-reset-title-{{ $admin->id }}">
                                                    Atur Ulang Password
                                                </h3>
                                                <p class="text-sm mb-4" style="color: var(--color-muted);">
                                                    Atur password baru untuk akun <strong>{{ $admin->nama }}</strong>.
                                                    Admin akan wajib mengganti password saat login berikutnya.
                                                </p>

                                                <form
                                                    method="POST"
                                                    action="{{ route('super-admin.sekolah.admin.reset-password', [$sekolah, $admin]) }}"
                                                    id="form-reset-{{ $admin->id }}"
                                                >
                                                    @csrf
                                                    @method('PATCH')

                                                    <div class="mb-4">
                                                        <label
                                                            for="password-reset-{{ $admin->id }}"
                                                            class="form-label"
                                                        >Password Baru</label>
                                                        <input
                                                            type="password"
                                                            id="password-reset-{{ $admin->id }}"
                                                            name="password"
                                                            required
                                                            minlength="8"
                                                            class="form-input"
                                                            placeholder="Minimal 8 karakter"
                                                            autocomplete="new-password"
                                                        >
                                                        @error('password')
                                                            <p class="form-error" role="alert">{{ $message }}</p>
                                                        @enderror
                                                    </div>

                                                    <div class="flex gap-3 justify-end">
                                                        <x-button
                                                            type="button"
                                                            variant="outline"
                                                            @click="modalReset{{ $admin->id }} = false"
                                                            id="btn-batal-reset-{{ $admin->id }}"
                                                        >
                                                            Batal
                                                        </x-button>
                                                        <x-button
                                                            type="submit"
                                                            variant="primary"
                                                            id="btn-konfirmasi-reset-{{ $admin->id }}"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                            </svg>
                                                            Simpan Password Baru
                                                        </x-button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>
        </div>
    </div>

</x-layouts.super-admin>
