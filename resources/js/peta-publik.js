import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';
import { SotaStorage } from './sota-storage.js';

/**
 * SOTA-GIS — Peta Publik Interaktif
 * Sesuai PRD Bagian 7.1, 7.3, 8, dan 16
 */

// Data Warga Bersih tanpa dummy text (Diambil dinamis dari Database MySQL)
export const DATA_WARGA = [];


export function createCustomMarkerIcon(klasifikasi) {
    let fillColor = '#2C7A4B'; // Rendah: Hijau
    if (klasifikasi === 'sedang') {
        fillColor = '#E0A32E'; // Sedang: Kuning / Emas
    } else if (klasifikasi === 'tinggi') {
        fillColor = '#B3352C'; // Tinggi: Merah
    }

    const svgContent = `
        <svg width="18" height="18" viewBox="0 0 18 18" class="drop-shadow-sm">
            <circle cx="9" cy="9" r="7" fill="${fillColor}" stroke="#FFFFFF" stroke-width="2" />
        </svg>
    `;

    return L.divIcon({
        html: `<div class="marker-container hover:scale-125 transition-transform cursor-pointer flex items-center justify-center">${svgContent}</div>`,
        className: 'sota-map-marker',
        iconSize: [18, 18],
        iconAnchor: [9, 9]
    });
}

// Inisialisasi Peta
export function initPetaPublik() {
    const mapContainer = document.getElementById('map-container');
    if (!mapContainer) return;

    // Batasan wilayah diperluas mencakup Merauke hingga Kampung Sota dan perbatasan
    const sotaCenter = [-8.42827, 141.00373];
    const sotaBounds = [
        [-8.70, 140.10],
        [-8.20, 141.20]
    ];

    const map = L.map('map-container', {
        center: sotaCenter,
        zoom: 16,
        minZoom: 9,
        maxZoom: 19,
        maxBounds: sotaBounds,
        maxBoundsViscosity: 0.5,
        zoomControl: false // Kita gunakan tombol kontrol vertikal kustom di kanan bawah (Google Maps style)
    });

    // Ekspos Kontrol Zoom Kustom untuk Tombol Blade
    window.sotaMapZoomIn = function() {
        if (map) map.zoomIn();
    };
    window.sotaMapZoomOut = function() {
        if (map) map.zoomOut();
    };

    // Skala Peta di Kanan Bawah
    L.control.scale({ position: 'bottomright', imperial: false }).addTo(map);

    // Basemaps: OpenStreetMap & Esri World Imagery
    const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap &middot; WGS 84'
    }).addTo(map);

    const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        maxZoom: 19,
        attribution: '&copy; Esri World Imagery &middot; WGS 84'
    });

    let currentBasemap = 'osm';
    window.toggleBasemap = function(type) {
        if (type === 'satellite' && currentBasemap !== 'satellite') {
            map.removeLayer(osmLayer);
            map.addLayer(satelliteLayer);
            currentBasemap = 'satellite';
        } else if (type === 'osm' && currentBasemap !== 'osm') {
            map.removeLayer(satelliteLayer);
            map.addLayer(osmLayer);
            currentBasemap = 'osm';
        }
    };

    // Marker Cluster Group
    const markersCluster = L.markerClusterGroup({
        showCoverageOnHover: false,
        spiderfyOnMaxZoom: true,
        maxClusterRadius: 40,
        iconCreateFunction: function(cluster) {
            const markers = cluster.getAllChildMarkers();
            let countTinggi = 0;
            let countSedang = 0;
            let countRendah = 0;

            markers.forEach(m => {
                if (m.options.klasifikasi === 'tinggi') countTinggi++;
                else if (m.options.klasifikasi === 'sedang') countSedang++;
                else countRendah++;
            });

            // Warnai bubble cluster sesuai kelas mayoritas (PRD 8.3)
            let clusterBg = '#E0A32E'; // default sedang
            if (countTinggi >= countSedang && countTinggi >= countRendah) {
                clusterBg = '#B3352C';
            } else if (countRendah >= countSedang && countRendah >= countTinggi) {
                clusterBg = '#2C7A4B';
            }

            return L.divIcon({
                html: `<div style="background-color: ${clusterBg};" class="flex items-center justify-center w-8 h-8 rounded-full text-white text-xs font-bold shadow-lg border-2 border-white">${markers.length}</div>`,
                className: 'custom-cluster-icon',
                iconSize: [32, 32]
            });
        }
    });

    map.addLayer(markersCluster);

    // Kumpulan marker aktif & referensi id
    const markerRegistry = new Map();

    // Fungsi Pengambil Data Warga Terverifikasi (Kombinasi Sampel + LocalStorage)
    function getDaftarWarga() {
        if (typeof window !== 'undefined' && Array.isArray(window.SOTA_REAL_WARGA)) {
            return window.SOTA_REAL_WARGA;
        }
        return [];
    }

    // Fungsi Render Marker Sesuai Filter
    function updateMarkers(activeFilters) {
        markersCluster.clearLayers();
        markerRegistry.clear();

        let visibleCount = 0;
        const currentData = getDaftarWarga();

        currentData.forEach(warga => {
            // 1. Filter Kerentanan
            const matchKerentanan = Array.isArray(activeFilters.kerentanan) && activeFilters.kerentanan.includes(warga.klasifikasi);

            // 2. Filter Jalur / Wilayah
            let matchWilayah = true;
            if (activeFilters.jalur && activeFilters.jalur !== 'semua') {
                matchWilayah = (warga.jalur || '').toLowerCase().includes(activeFilters.jalur.toLowerCase());
            } else if (activeFilters.wilayah) {
                matchWilayah = activeFilters.wilayah.includes('semua') ||
                    activeFilters.wilayah.includes(warga.jalur) ||
                    (warga.wilayah && activeFilters.wilayah.includes(warga.wilayah)) ||
                    (warga.jalur && warga.jalur.includes('A') && activeFilters.wilayah.includes('Jalur A')) ||
                    (warga.jalur && warga.jalur.includes('B') && activeFilters.wilayah.includes('Jalur B')) ||
                    (warga.jalur && warga.jalur.toLowerCase().includes('poros') && (activeFilters.wilayah.includes('Poros Trans') || activeFilters.wilayah.includes('Poros Sota PLBN')));
            }

            // 3. Filter Status Kependudukan (Tetap / Tidak Tetap)
            let matchStatus = true;
            if (activeFilters.status_penduduk && activeFilters.status_penduduk !== 'semua') {
                matchStatus = (warga.status_penduduk || '').toLowerCase() === activeFilters.status_penduduk.toLowerCase();
            }

            // 4. Filter 9 Indikator SVI Spesifik
            let matchSvi = true;
            if (activeFilters.svi && typeof activeFilters.svi === 'object') {
                const s = activeFilters.svi;
                // 1. Pendapatan
                if (s.pendapatan && s.pendapatan !== 'semua') {
                    const p = (warga.pendapatan || '').toLowerCase();
                    if (s.pendapatan === '1' && !p.includes('2.500.000')) matchSvi = false;
                    else if (s.pendapatan === '2' && !p.includes('1.000.000')) matchSvi = false;
                    else if (s.pendapatan === '3' && !p.includes('kurang')) matchSvi = false;
                }
                // 2. Pekerjaan
                if (matchSvi && s.pekerjaan && s.pekerjaan !== 'semua') {
                    const pk = (warga.pekerjaan || '').toLowerCase();
                    if (s.pekerjaan === '1' && !(pk.includes('pns') || pk.includes('tni') || pk.includes('polri') || pk.includes('tetap') || pk.includes('wiraswasta'))) matchSvi = false;
                    else if (s.pekerjaan === '2' && !(pk.includes('petani') || pk.includes('peternak') || pk.includes('pedagang') || pk.includes('honorer') || pk.includes('kontrak'))) matchSvi = false;
                    else if (s.pekerjaan === '3' && !(pk.includes('buruh') || pk.includes('serabutan') || pk.includes('tidak bekerja') || pk.includes('ibu rumah tangga'))) matchSvi = false;
                }
                // 3. Pendidikan
                if (matchSvi && s.pendidikan && s.pendidikan !== 'semua') {
                    const pd = (warga.pendidikan || '').toLowerCase();
                    if (s.pendidikan === '1' && !(pd.includes('perguruan') || pd.includes('diploma') || pd.includes('s1') || pd.includes('s2') || pd.includes('d3'))) matchSvi = false;
                    else if (s.pendidikan === '2' && !(pd.includes('sma') || pd.includes('smk') || pd.includes('sederajat'))) matchSvi = false;
                    else if (s.pendidikan === '3' && !(pd.includes('sd') || pd.includes('smp') || pd.includes('tidak tamat'))) matchSvi = false;
                }
                // 4. Jumlah Anggota
                if (matchSvi && s.anggota && s.anggota !== 'semua') {
                    const jml = parseInt(warga.jumlah_anggota_keluarga) || 0;
                    if (s.anggota === '1' && (jml < 1 || jml > 2)) matchSvi = false;
                    else if (s.anggota === '2' && (jml < 3 || jml > 4)) matchSvi = false;
                    else if (s.anggota === '3' && jml <= 4) matchSvi = false;
                }
                // 5. Dinding
                if (matchSvi && s.dinding && s.dinding !== 'semua') {
                    const d = (warga.jenis_dinding || '').toLowerCase();
                    if (s.dinding === 'tembok' && !d.includes('tembok')) matchSvi = false;
                    else if (s.dinding === 'kayu' && !d.includes('kayu')) matchSvi = false;
                }
                // 6. Lantai
                if (matchSvi && s.lantai && s.lantai !== 'semua') {
                    const l = (warga.jenis_lantai || '').toLowerCase();
                    if (s.lantai === 'keramik' && !(l.includes('keramik') || l.includes('ubin'))) matchSvi = false;
                    else if (s.lantai === 'semen' && !l.includes('semen')) matchSvi = false;
                    else if (s.lantai === 'tanah_kayu' && !(l.includes('tanah') || l.includes('papan') || l.includes('kayu'))) matchSvi = false;
                }
                // 7. Sanitasi
                if (matchSvi && s.sanitasi && s.sanitasi !== 'semua') {
                    const sn = (warga.fasilitas_sanitasi || '').toLowerCase();
                    if (s.sanitasi === 'pribadi' && !(sn.includes('pribadi') || sn.includes('sehat'))) matchSvi = false;
                    else if (s.sanitasi === 'umum' && !(sn.includes('umum') || sn.includes('tetangga') || sn.includes('berbagi'))) matchSvi = false;
                    else if (s.sanitasi === 'tidak_ada' && !(sn.includes('tidak') || sn.includes('menumpang'))) matchSvi = false;
                }
                // 8. Air Bersih
                if (matchSvi && s.air && s.air !== 'semua') {
                    const a = (warga.sumber_air || '').toLowerCase();
                    if (s.air === 'pdam' && !a.includes('pdam')) matchSvi = false;
                    else if (s.air === 'bor_galon' && !(a.includes('bor') || a.includes('galon'))) matchSvi = false;
                    else if (s.air === 'gali' && !(a.includes('gali') || a.includes('hujan'))) matchSvi = false;
                }
                // 9. Kepemilikan Lahan
                if (matchSvi && s.lahan && s.lahan !== 'semua') {
                    const lh = (warga.kepemilikan_lahan || '').toLowerCase();
                    if (s.lahan === 'sendiri' && !(lh.includes('sendiri') || lh.includes('warisan'))) matchSvi = false;
                    else if (s.lahan === 'sewa' && !(lh.includes('sewa') || lh.includes('pinjam') || lh.includes('garapan'))) matchSvi = false;
                    else if (s.lahan === 'tidak_ada' && !(lh.includes('tidak') || lh.includes('numpang'))) matchSvi = false;
                }
            }

            if (matchKerentanan && matchWilayah && matchStatus && matchSvi) {
                visibleCount++;
                const icon = createCustomMarkerIcon(warga.klasifikasi);
                const marker = L.marker([warga.latitude, warga.longitude], {
                    icon: icon,
                    klasifikasi: warga.klasifikasi,
                    title: warga.nama_kk,
                    alt: `Rumah tangga ${warga.nama_kk}, ${warga.klasifikasi_label || warga.klasifikasi}, ${warga.jalur}`
                });

                // Klik marker membuka modal detail (PRD 8.3)
                marker.on('click', () => {
                    window.bukaDetailModal(warga);
                });

                markersCluster.addLayer(marker);
                markerRegistry.set(warga.id, marker);
            }
        });

        // Update teks counter dinamis sesuai data warga terverifikasi
        const counterEl = document.getElementById('filter-counter-text');
        if (counterEl) {
            const totalWarga = currentData.length;
            counterEl.innerHTML = `Menampilkan <strong class="font-bold text-ink">${visibleCount}</strong> dari <strong class="font-bold text-ink">${totalWarga}</strong> rumah tangga`;
        }

        // Peringatan jika filter kosong
        const emptyWarning = document.getElementById('filter-empty-warning');
        if (emptyWarning) {
            if (activeFilters.kerentanan && activeFilters.kerentanan.length === 0) {
                emptyWarning.classList.remove('hidden');
            } else {
                emptyWarning.classList.add('hidden');
            }
        }
    }

    // State Filter Awal (Semua aktif secara default agar peta menampilkan data secara lengkap)
    const filters = {
        kerentanan: ['rendah', 'sedang', 'tinggi'],
        wilayah: ['semua', 'Jalur A', 'Jalur B', 'Poros Trans', 'Poros Sota PLBN'],
        jalur: 'semua',
        status_penduduk: 'semua',
        svi: {}
    };

    updateMarkers(filters);

    // Fungsi pengubah filter komprehensif langsung dari Alpine
    window.applyComprehensiveFilters = function(customFilters) {
        if (customFilters.kerentanan !== undefined) filters.kerentanan = customFilters.kerentanan;
        if (customFilters.jalur !== undefined) filters.jalur = customFilters.jalur;
        if (customFilters.status_penduduk !== undefined) filters.status_penduduk = customFilters.status_penduduk;
        if (customFilters.svi !== undefined) filters.svi = customFilters.svi;
        if (customFilters.wilayah !== undefined) filters.wilayah = customFilters.wilayah;
        updateMarkers(filters);
    };

    // Fungsi pengubah filter kerentanan langsung dari Alpine
    window.handleKerentananChange = function(activeList) {
        filters.kerentanan = activeList;
        updateMarkers(filters);
    };

    // Listener Filter Checkbox Legacy
    window.handleFilterChange = function() {
        const kerentananChecked = Array.from(document.querySelectorAll('input[name="filter_kerentanan"]:checked')).map(el => el.value);
        filters.kerentanan = kerentananChecked;
        updateMarkers(filters);
    };

    // Atur Ulang Filter
    window.resetFilter = function() {
        filters.kerentanan = ['rendah', 'sedang', 'tinggi'];
        filters.jalur = 'semua';
        filters.status_penduduk = 'semua';
        filters.svi = {};
        updateMarkers(filters);
    };

    // Fitur Pencarian Nama KK
    window.handleSearchInput = function(query) {
        const trimmed = query.trim().toLowerCase();
        const suggestionsBox = document.getElementById('search-suggestions');
        const emptyMsg = document.getElementById('search-empty-msg');
        const listEl = document.getElementById('search-suggestions-list');

        if (!suggestionsBox || !listEl) return;

        if (trimmed.length < 2) {
            suggestionsBox.classList.add('hidden');
            return;
        }

        const matches = getDaftarWarga().filter(w => w.nama_kk.toLowerCase().includes(trimmed)).slice(0, 8);

        if (matches.length === 0) {
            suggestionsBox.classList.remove('hidden');
            listEl.innerHTML = '';
            if (emptyMsg) emptyMsg.classList.remove('hidden');
        } else {
            if (emptyMsg) emptyMsg.classList.add('hidden');
            suggestionsBox.classList.remove('hidden');
            listEl.innerHTML = matches.map(w => {
                const badgeColor = w.klasifikasi === 'tinggi' ? 'bg-tinggi-bg text-tinggi-tx border-tinggi/30' :
                                   w.klasifikasi === 'sedang' ? 'bg-sedang-bg text-sedang-tx border-sedang/30' :
                                   'bg-rendah-bg text-rendah-tx border-rendah/30';
                const iconSymbol = '●';
                const labelKlas = w.klasifikasi_label || ('Kerentanan ' + (w.klasifikasi ? (w.klasifikasi.charAt(0).toUpperCase() + w.klasifikasi.slice(1)) : 'Sedang'));
                const alamatInfo = w.rt_rw || (w.rt && w.rw ? (w.rt + ' / ' + w.rw) : (w.alamat || 'Kampung Sota'));

                return `
                    <li onclick="window.pilihHasilCari('${w.id}')"
                        class="p-2.5 hover:bg-paper cursor-pointer border-b border-line last:border-0 flex items-center justify-between gap-2 text-xs">
                        <div>
                            <span class="font-bold text-ink block">${w.nama_kk}</span>
                            <span class="text-muted text-[11px]">${w.jalur || 'Kampung Sota'} &middot; ${alamatInfo}</span>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold border ${badgeColor}">
                            <span>${iconSymbol}</span>
                            <span>${labelKlas}</span>
                        </span>
                    </li>
                `;
            }).join('');
        }
    };

    // Memilih Saran Pencarian
    window.pilihHasilCari = function(id) {
        const item = getDaftarWarga().find(w => String(w.id) === String(id));
        if (!item) return;

        // Tutup kotak saran
        const suggestionsBox = document.getElementById('search-suggestions');
        if (suggestionsBox) suggestionsBox.classList.add('hidden');

        // Pastikan filter aktif mencakup item ini
        if (!filters.kerentanan.includes(item.klasifikasi)) {
            filters.kerentanan.push(item.klasifikasi);
            const el = document.querySelector(`input[name="filter_kerentanan"][value="${item.klasifikasi}"]`);
            if (el) el.checked = true;
        }
        if (!filters.wilayah.includes(item.jalur)) {
            filters.wilayah.push(item.jalur);
            const el = document.querySelector(`input[name="filter_wilayah"][value="${item.jalur}"]`);
            if (el) el.checked = true;
        }
        updateMarkers(filters);

        // Fly to titik zoom 18
        map.flyTo([item.latitude, item.longitude], 18, {
            animate: true,
            duration: 1.2
        });

        // Buka detail modal setelah selesai terbang
        setTimeout(() => {
            window.bukaDetailModal(item);
        }, 1300);
    };

    // Tombol Lokasi Saya (Geolocation)
    window.ambilLokasiSaya = function() {
        if (!navigator.geolocation) {
            alert('Peramban Anda tidak mendukung penentuan lokasi GPS.');
            return;
        }

        const btn = document.getElementById('btn-lokasi-saya');
        if (btn) btn.classList.add('animate-spin');

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                if (btn) btn.classList.remove('animate-spin');
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;

                map.flyTo([lat, lng], 17);

                // Tambahkan marker sementara
                L.circleMarker([lat, lng], {
                    radius: 8,
                    fillColor: '#175C6B',
                    color: '#FFFFFF',
                    weight: 3,
                    opacity: 1,
                    fillOpacity: 0.9
                }).addTo(map).bindPopup('Lokasi Anda saat ini').openPopup();
            },
            (err) => {
                if (btn) btn.classList.remove('animate-spin');
                alert('Tidak dapat mendeteksi lokasi GPS Anda. Pastikan izin lokasi telah diberikan.');
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    };
}

// Inisialisasi Otomatis Saat DOM Siap
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPetaPublik);
} else {
    initPetaPublik();
}
