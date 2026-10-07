// === Alpine Plugin & Integrasi Resmi Livewire ===
import collapse from '@alpinejs/collapse';
import intersect from '@alpinejs/intersect';

// Daftarkan plugin Alpine ke instance resmi Alpine bawaan Livewire
document.addEventListener('livewire:init', () => {
    if (window.Alpine) {
        window.Alpine.plugin(collapse);
        window.Alpine.plugin(intersect);
    }
});

// Fallback universal password toggle (berfungsi seketika di semua halaman auth/form)
document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('click', (e) => {
        const toggleBtn = e.target.closest('[data-toggle-password]');
        if (!toggleBtn) return;

        const container = toggleBtn.closest('.relative');
        if (!container) return;

        const input = container.querySelector('input');
        if (!input) return;

        // Jika Alpine belum / tidak mengontrol atribut :type, fallback manual
        const currentType = input.getAttribute('type');
        const willBeText = currentType === 'password';
        input.setAttribute('type', willBeText ? 'text' : 'password');

        const eyeIcon = toggleBtn.querySelector('.icon-eye');
        const eyeSlashIcon = toggleBtn.querySelector('.icon-eye-slash');

        if (eyeIcon && eyeSlashIcon) {
            eyeIcon.style.display = willBeText ? 'none' : 'block';
            eyeSlashIcon.style.display = willBeText ? 'block' : 'none';
        }
    });
});



