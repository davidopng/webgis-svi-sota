import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

/**
 * SOTA-GIS — Modul Ambil Koordinat GPS & Penanganan Unggah Foto
 * Sesuai PRD Bagian 10.4 (Bagian 2 Formulir Survei)
 */

let previewMap = null;
let previewMarker = null;

// Konfigurasi Default Lokasi Sota
const DEFAULT_LAT = -8.44271;
const DEFAULT_LNG = 140.86935;

/**
 * Inisialisasi Peta Pratinjau 240px
 */
export function initPreviewMap(initialLat, initialLng) {
    const mapEl = document.getElementById('map-preview');
    if (!mapEl) return;

    const lat = initialLat || DEFAULT_LAT;
    const lng = initialLng || DEFAULT_LNG;

    if (!previewMap) {
        previewMap = L.map('map-preview', {
            center: [lat, lng],
            zoom: 17,
            zoomControl: true,
            attributionControl: false
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(previewMap);

        // Marker khusus dengan SVG lingkaran brand
        const iconSvg = `
            <svg width="28" height="28" viewBox="0 0 28 28" class="drop-shadow-md">
                <circle cx="14" cy="14" r="10" fill="#175C6B" stroke="#FFFFFF" stroke-width="3" />
                <circle cx="14" cy="14" r="4" fill="#FFFFFF" />
            </svg>
        `;

        const customIcon = L.divIcon({
            html: `<div class="cursor-move">${iconSvg}</div>`,
            className: 'sota-preview-marker',
            iconSize: [28, 28],
            iconAnchor: [14, 14]
        });

        previewMarker = L.marker([lat, lng], {
            draggable: true,
            icon: customIcon
        }).addTo(previewMap);

        // Event saat marker digeser oleh kader
        previewMarker.on('dragend', function (event) {
            const position = previewMarker.getLatLng();
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            const accuracyInfo = document.getElementById('gps-accuracy-info');

            if (latInput) latInput.value = position.lat.toFixed(5);
            if (lngInput) lngInput.value = position.lng.toFixed(5);

            if (accuracyInfo) {
                accuracyInfo.textContent = 'Posisi disesuaikan manual · WGS 84';
                accuracyInfo.classList.add('text-brand', 'font-medium');
                accuracyInfo.classList.remove('text-muted');
            }

            // Trigger input event agar validasi & Alpine mendeteksi perubahan
            if (latInput) latInput.dispatchEvent(new Event('input', { bubbles: true }));
        });
    } else {
        previewMap.setView([lat, lng], 17);
        if (previewMarker) {
            previewMarker.setLatLng([lat, lng]);
        }
        setTimeout(() => {
            previewMap.invalidateSize();
        }, 200);
    }
}

/**
 * Pengambilan Koordinat GPS via Geolocation API
 */
export function ambilGPS(callbacks) {
    const { onStart, onSuccess, onError } = callbacks || {};

    if (!navigator.geolocation) {
        if (onError) onError('Peramban tidak mendukung fitur GPS Geolocation.');
        return;
    }

    if (onStart) onStart();

    const options = {
        enableHighAccuracy: true,
        timeout: 15000,
        maximumAge: 0
    };

    navigator.geolocation.getCurrentPosition(
        (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            const accuracy = Math.round(position.coords.accuracy);
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            const accuracyInfo = document.getElementById('gps-accuracy-info');
            const mapContainer = document.getElementById('map-preview-container');

            if (latInput) {
                latInput.value = lat.toFixed(5);
                latInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            if (lngInput) {
                lngInput.value = lng.toFixed(5);
                lngInput.dispatchEvent(new Event('input', { bubbles: true }));
            }

            if (accuracyInfo) {
                accuracyInfo.textContent = `Akurasi ±${accuracy} meter · diambil ${timeStr} · WGS 84`;
                accuracyInfo.classList.remove('text-tinggi-tx', 'text-brand');
                accuracyInfo.classList.add('text-muted');
            }

            // Tampilkan container peta pratinjau
            if (mapContainer) {
                mapContainer.classList.remove('hidden');
            }

            // Inisialisasi atau geser peta
            initPreviewMap(lat, lng);

            if (onSuccess) onSuccess({ lat, lng, accuracy, timeStr });
        },
        (error) => {
            let errorMsg = 'Sinyal GPS belum didapat. Pastikan berada di luar ruangan, lalu coba lagi.';
            let isPermissionDenied = false;

            if (error.code === error.PERMISSION_DENIED) {
                errorMsg = 'Akses lokasi ditolak. Aktifkan izin lokasi di pengaturan peramban, lalu coba lagi.';
                isPermissionDenied = true;
            } else if (error.code === error.TIMEOUT) {
                errorMsg = 'Waktu permintaan lokasi habis (timeout). Silakan tekan coba lagi.';
            }

            if (onError) onError(errorMsg, isPermissionDenied);
        },
        options
    );
}

/**
 * Kompresi Gambar di Sisi Klien Menggunakan Canvas
 * Membatasi dimensi maksimum ke 1600px sesuai PRD Bagian 10.4
 */
export function compressImage(file, maxWidthOrHeight = 1600, quality = 0.8) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = (e) => {
            const img = new Image();
            img.src = e.target.result;
            img.onload = () => {
                let width = img.width;
                let height = img.height;

                if (width > height) {
                    if (width > maxWidthOrHeight) {
                        height = Math.round((height * maxWidthOrHeight) / width);
                        width = maxWidthOrHeight;
                    }
                } else {
                    if (height > maxWidthOrHeight) {
                        width = Math.round((width * maxWidthOrHeight) / height);
                        height = maxWidthOrHeight;
                    }
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob(
                    (blob) => {
                        if (!blob) {
                            reject(new Error('Gagal mengompresi gambar.'));
                            return;
                        }
                        const compressedFile = new File([blob], file.name, {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        });
                        resolve({
                            file: compressedFile,
                            dataUrl: canvas.toDataURL('image/jpeg', quality)
                        });
                    },
                    'image/jpeg',
                    quality
                );
            };
            img.onerror = (err) => reject(err);
        };
        reader.onerror = (err) => reject(err);
    });
}

// Ekspos ke window agar Alpine.js di Blade dapat memanggilnya
window.sotaAmbilGPS = ambilGPS;
window.sotaInitPreviewMap = initPreviewMap;
window.sotaCompressImage = compressImage;
