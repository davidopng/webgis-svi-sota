import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

/**
 * SOTA-GIS — Modul Ambil Koordinat GPS & Penanganan Unggah Foto
 * Sesuai PRD Bagian 10.4 (Bagian 2 Formulir Survei)
 */

let previewMap = null;
let previewMarker = null;
let osmLayer = null;
let satelliteLayer = null;
let currentBasemap = 'peta';

// Konfigurasi Default Lokasi Sota
const DEFAULT_LAT = -8.42827;
const DEFAULT_LNG = 141.00373;

/**
 * Inisialisasi Peta Pratinjau 240px
 */
export function initPreviewMap(initialLat, initialLng, options = {}) {
    const mapEl = document.getElementById('map-preview');
    if (!mapEl) return;

    const lat = (initialLat !== undefined && initialLat !== null && !isNaN(initialLat)) ? initialLat : DEFAULT_LAT;
    const lng = (initialLng !== undefined && initialLng !== null && !isNaN(initialLng)) ? initialLng : DEFAULT_LNG;

    function updateCoordState(newLat, newLng, source = 'drag') {
        const latVal = typeof newLat === 'number' ? newLat.toFixed(5) : parseFloat(newLat).toFixed(5);
        const lngVal = typeof newLng === 'number' ? newLng.toFixed(5) : parseFloat(newLng).toFixed(5);

        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        const accuracyInfo = document.getElementById('gps-accuracy-info');

        if (latInput) latInput.value = latVal;
        if (lngInput) lngInput.value = lngVal;

        if (accuracyInfo) {
            accuracyInfo.textContent = source === 'click'
                ? 'Posisi disesuaikan dari klik peta · WGS 84'
                : 'Posisi disesuaikan manual · WGS 84';
            accuracyInfo.classList.add('text-brand', 'font-medium');
            accuracyInfo.classList.remove('text-muted');
        }

        // Kirim event kustom ke Alpine.js secara atomik (lat dan lng sekaligus)
        window.dispatchEvent(new CustomEvent('sota:coord-updated', {
            detail: { lat: latVal, lng: lngVal, source: source }
        }));
    }

    if (!previewMap) {
        previewMap = L.map('map-preview', {
            center: [lat, lng],
            zoom: 17,
            zoomControl: true,
            scrollWheelZoom: true,
            doubleClickZoom: true,
            attributionControl: false
        });

        // Layer Peta (OpenStreetMap) sebagai bawaan
        osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        });

        // Layer Satelit (Esri World Imagery)
        satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19
        });

        osmLayer.addTo(previewMap);
        currentBasemap = 'peta';

        // Kontrol Pengalih Basemap (Peta & Satelit) di pojok kanan atas
        const BasemapControl = L.Control.extend({
            options: { position: 'topright' },
            onAdd: function () {
                const container = L.DomUtil.create('div', 'leaflet-bar sota-preview-basemap-control');
                container.style.border = 'none';
                container.style.background = 'transparent';
                container.style.margin = '10px 10px 0 0';

                container.innerHTML = `
                    <div class="inline-flex items-center rounded-lg bg-white/95 backdrop-blur-sm shadow-md border border-slate-200 p-0.5 text-xs font-semibold" style="pointer-events: auto;">
                        <button type="button" id="btn-basemap-peta" class="px-2.5 py-1 rounded-md text-xs font-bold transition-all bg-brand text-white shadow-xs">Peta</button>
                        <button type="button" id="btn-basemap-satelit" class="px-2.5 py-1 rounded-md text-xs font-medium text-slate-600 hover:text-ink hover:bg-slate-100 transition-all">Satelit</button>
                    </div>
                `;

                L.DomEvent.disableClickPropagation(container);
                L.DomEvent.disableScrollPropagation(container);

                setTimeout(() => {
                    const btnPeta = container.querySelector('#btn-basemap-peta');
                    const btnSatelit = container.querySelector('#btn-basemap-satelit');

                    if (btnPeta && btnSatelit) {
                        btnPeta.onclick = (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            if (currentBasemap === 'peta') return;
                            if (previewMap.hasLayer(satelliteLayer)) {
                                previewMap.removeLayer(satelliteLayer);
                            }
                            if (!previewMap.hasLayer(osmLayer)) {
                                previewMap.addLayer(osmLayer);
                            }
                            currentBasemap = 'peta';
                            btnPeta.className = 'px-2.5 py-1 rounded-md text-xs font-bold transition-all bg-brand text-white shadow-xs';
                            btnSatelit.className = 'px-2.5 py-1 rounded-md text-xs font-medium text-slate-600 hover:text-ink hover:bg-slate-100 transition-all';
                        };

                        btnSatelit.onclick = (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            if (currentBasemap === 'satelit') return;
                            if (previewMap.hasLayer(osmLayer)) {
                                previewMap.removeLayer(osmLayer);
                            }
                            if (!previewMap.hasLayer(satelliteLayer)) {
                                previewMap.addLayer(satelliteLayer);
                            }
                            currentBasemap = 'satelit';
                            btnSatelit.className = 'px-2.5 py-1 rounded-md text-xs font-bold transition-all bg-brand text-white shadow-xs';
                            btnPeta.className = 'px-2.5 py-1 rounded-md text-xs font-medium text-slate-600 hover:text-ink hover:bg-slate-100 transition-all';
                        };
                    }
                }, 50);

                return container;
            }
        });
        new BasemapControl().addTo(previewMap);

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

        // Event saat marker digeser oleh kader — perbarui input tanpa menarik balik
        previewMarker.on('dragend', function (event) {
            const position = previewMarker.getLatLng();
            updateCoordState(position.lat, position.lng, 'drag');
        });

        // Event saat pengguna mengklik peta — pindahkan pin langsung ke titik klik secara presisi
        previewMap.on('click', function (event) {
            const position = event.latlng;
            previewMarker.setLatLng(position);
            updateCoordState(position.lat, position.lng, 'click');
        });
    } else {
        const targetZoom = options.zoom || previewMap.getZoom() || 17;
        if (options.flyTo) {
            previewMap.flyTo([lat, lng], targetZoom, { duration: 0.8 });
        } else if (!options.keepView) {
            previewMap.setView([lat, lng], targetZoom);
        }
        if (previewMarker && !options.skipMarker) {
            previewMarker.setLatLng([lat, lng]);
        }
        setTimeout(() => {
            previewMap.invalidateSize();
        }, 200);
    }
}

/**
 * Pindahkan Penanda dan Pusatkan Peta Secara Halus
 */
export function setMarkerPosition(newLat, newLng, options = {}) {
    const lat = parseFloat(newLat);
    const lng = parseFloat(newLng);
    if (isNaN(lat) || isNaN(lng)) return;

    if (!previewMap) {
        initPreviewMap(lat, lng, options);
        return;
    }

    if (previewMarker) {
        previewMarker.setLatLng([lat, lng]);
    }

    const zoom = options.zoom || 17;
    if (options.flyTo) {
        previewMap.flyTo([lat, lng], zoom, { duration: 0.8 });
    } else {
        previewMap.setView([lat, lng], zoom);
    }

    const latVal = lat.toFixed(5);
    const lngVal = lng.toFixed(5);
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const accuracyInfo = document.getElementById('gps-accuracy-info');

    if (latInput) latInput.value = latVal;
    if (lngInput) lngInput.value = lngVal;

    if (accuracyInfo) {
        accuracyInfo.textContent = options.infoText || 'Posisi diarahkan ke wilayah Kampung Sota · WGS 84';
        accuracyInfo.classList.add('text-brand', 'font-medium');
        accuracyInfo.classList.remove('text-muted');
    }

    window.dispatchEvent(new CustomEvent('sota:coord-updated', {
        detail: { lat: latVal, lng: lngVal, source: 'preset' }
    }));
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
            const accuracyWarningEl = document.getElementById('gps-accuracy-warning');
            const accuracyWarningText = document.getElementById('gps-accuracy-warning-text');

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

            // Tampilkan peringatan kuning jika akurasi > 50 meter (PRD Bagian 10.4)
            if (accuracy > 50) {
                if (accuracyWarningEl) {
                    accuracyWarningEl.classList.remove('hidden');
                }
                if (accuracyWarningText) {
                    accuracyWarningText.textContent = `Akurasi ±${accuracy} meter. Tunggu beberapa detik lalu ambil ulang agar titik lebih tepat.`;
                }
            } else {
                if (accuracyWarningEl) {
                    accuracyWarningEl.classList.add('hidden');
                }
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
export function compressImage(file, maxWidthOrHeight = 800, quality = 0.65) {
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
window.sotaSetMarkerPosition = setMarkerPosition;
window.sotaCompressImage = compressImage;
