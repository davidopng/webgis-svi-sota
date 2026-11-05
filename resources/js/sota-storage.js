/**
 * SOTA-GIS LocalStorage Storage Manager
 * Mengelola penyimpanan data survei warga secara lokal tanpa database
 */

const STORAGE_KEY = 'sota_warga_custom_v1';

export const SotaStorage = {
    getAll() {
        try {
            const data = localStorage.getItem(STORAGE_KEY);
            return data ? JSON.parse(data) : [];
        } catch (e) {
            console.error('Gagal membaca data dari localStorage:', e);
            return [];
        }
    },

    getById(id) {
        const list = this.getAll();
        return list.find(item => String(item.id) === String(id)) || null;
    },

    syncBadge() {
        // Badge antrean dikelola realtime oleh Blade dari database MySQL
    },

    save(item) {
        try {
            const list = this.getAll();
            const existingIndex = list.findIndex(d => String(d.id) === String(item.id));

            if (existingIndex >= 0) {
                list[existingIndex] = { ...list[existingIndex], ...item };
            } else {
                if (!item.id) item.id = 'warga_' + Date.now();
                list.unshift(item); // Masukkan ke paling awal
            }

            localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
            this.syncBadge();
            return item;
        } catch (e) {
            console.error('Gagal menyimpan ke localStorage:', e);
            return null;
        }
    },

    updateStatus(id, newStatus) {
        try {
            const list = this.getAll();
            const target = list.find(item => String(item.id) === String(id));
            if (target) {
                target.status = newStatus;
                target.status_verifikasi = newStatus;
                localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
                this.syncBadge();
                return target;
            }
            return null;
        } catch (e) {
            console.error('Gagal memperbarui status di localStorage:', e);
            return null;
        }
    },

    delete(id) {
        try {
            let list = this.getAll();
            list = list.filter(item => String(item.id) !== String(id));
            localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
            this.syncBadge();
            return true;
        } catch (e) {
            console.error('Gagal menghapus dari localStorage:', e);
            return false;
        }
    },

    clearAll() {
        localStorage.removeItem(STORAGE_KEY);
        this.syncBadge();
    }
};

// Pasang ke window agar dapat dipanggil dari Alpine.js di semua view Blade
if (typeof window !== 'undefined') {
    try {
        localStorage.removeItem(STORAGE_KEY);
    } catch (e) { }
    window.SotaStorage = SotaStorage;
}
