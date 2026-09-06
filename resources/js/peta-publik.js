import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';

/**
 * SOTA-GIS — Peta Publik Interaktif
 * Sesuai PRD Bagian 7.1, 7.3, 8, dan 16
 */

// Data 15 Responden Terverifikasi (Sampel Representatif Sota)
export const DATA_WARGA = [
    {
        id: 1,
        nama_kk: 'Yohanis Kaize',
        jalur: 'Jalur B',
        rt_rw: 'RT 03 / RW 01',
        alamat: 'RT 03 / RW 01 No. 12',
        latitude: -8.44271,
        longitude: 140.86935,
        pekerjaan: 'Petani',
        jumlah_anggota: 4,
        total_skor: 20,
        skor_rata_rata: '2,22',
        klasifikasi: 'sedang',
        klasifikasi_label: 'Kerentanan Sedang',
        foto_rumah: null // Placeholder null untuk simulasi foto default
    },
    {
        id: 2,
        nama_kk: 'Petrus Gebze',
        jalur: 'Jalur A',
        rt_rw: 'RT 01 / RW 01',
        alamat: 'RT 01 / RW 01 No. 04',
        latitude: -8.43920,
        longitude: 140.86410,
        pekerjaan: 'Buruh harian lepas',
        jumlah_anggota: 5,
        total_skor: 23,
        skor_rata_rata: '2,56',
        klasifikasi: 'tinggi',
        klasifikasi_label: 'Kerentanan Tinggi',
        foto_rumah: null
    },
    {
        id: 3,
        nama_kk: 'Siti Rahayu',
        jalur: 'Poros Trans',
        rt_rw: 'RT 07 / RW 02',
        alamat: 'RT 07 / RW 02 No. 18',
        latitude: -8.44850,
        longitude: 140.87120,
        pekerjaan: 'Pedagang kecil',
        jumlah_anggota: 3,
        total_skor: 13,
        skor_rata_rata: '1,44',
        klasifikasi: 'rendah',
        klasifikasi_label: 'Kerentanan Rendah',
        foto_rumah: null
    },
    {
        id: 4,
        nama_kk: 'Markus Balagaize',
        jalur: 'Jalur B',
        rt_rw: 'RT 04 / RW 02',
        alamat: 'RT 04 / RW 02 No. 08',
        latitude: -8.44380,
        longitude: 140.86820,
        pekerjaan: 'Petani',
        jumlah_anggota: 6,
        total_skor: 22,
        skor_rata_rata: '2,44',
        klasifikasi: 'tinggi',
        klasifikasi_label: 'Kerentanan Tinggi',
        foto_rumah: null
    },
    {
        id: 5,
        nama_kk: 'Agustina Mahuze',
        jalur: 'Jalur B',
        rt_rw: 'RT 02 / RW 01',
        alamat: 'RT 02 / RW 01 No. 15',
        latitude: -8.44110,
        longitude: 140.87050,
        pekerjaan: 'Guru PNS',
        jumlah_anggota: 3,
        total_skor: 11,
        skor_rata_rata: '1,22',
        klasifikasi: 'rendah',
        klasifikasi_label: 'Kerentanan Rendah',
        foto_rumah: null
    },
    {
        id: 6,
        nama_kk: 'Kornelis Ndiken',
        jalur: 'Poros Trans',
        rt_rw: 'RT 09 / RW 03',
        alamat: 'RT 09 / RW 03 No. 27',
        latitude: -8.45120,
        longitude: 140.87350,
        pekerjaan: 'Petani',
        jumlah_anggota: 4,
        total_skor: 18,
        skor_rata_rata: '2,00',
        klasifikasi: 'sedang',
        klasifikasi_label: 'Kerentanan Sedang',
        foto_rumah: null
    },
    {
        id: 7,
        nama_kk: 'Fransiskus Samkakai',
        jalur: 'Jalur A',
        rt_rw: 'RT 02 / RW 01',
        alamat: 'RT 02 / RW 01 No. 09',
        latitude: -8.43810,
        longitude: 140.86330,
        pekerjaan: 'Pekerja serabutan',
        jumlah_anggota: 5,
        total_skor: 25,
        skor_rata_rata: '2,78',
        klasifikasi: 'tinggi',
        klasifikasi_label: 'Kerentanan Tinggi',
        foto_rumah: null
    },
    {
        id: 8,
        nama_kk: 'Maria Basik-Basik',
        jalur: 'Jalur A',
        rt_rw: 'RT 03 / RW 01',
        alamat: 'RT 03 / RW 01 No. 14',
        latitude: -8.43980,
        longitude: 140.86540,
        pekerjaan: 'Petani',
        jumlah_anggota: 4,
        total_skor: 17,
        skor_rata_rata: '1,89',
        klasifikasi: 'sedang',
        klasifikasi_label: 'Kerentanan Sedang',
        foto_rumah: null
    },
    {
        id: 9,
        nama_kk: 'Yosep Moyuend',
        jalur: 'Jalur B',
        rt_rw: 'RT 05 / RW 02',
        alamat: 'RT 05 / RW 02 No. 03',
        latitude: -8.44490,
        longitude: 140.86710,
        pekerjaan: 'Wiraswasta mandiri',
        jumlah_anggota: 4,
        total_skor: 12,
        skor_rata_rata: '1,33',
        klasifikasi: 'rendah',
        klasifikasi_label: 'Kerentanan Rendah',
        foto_rumah: null
    },
    {
        id: 10,
        nama_kk: 'Albertina Kwipalo',
        jalur: 'Poros Trans',
        rt_rw: 'RT 08 / RW 02',
        alamat: 'RT 08 / RW 02 No. 11',
        latitude: -8.44960,
        longitude: 140.87240,
        pekerjaan: 'Petani',
        jumlah_anggota: 4,
        total_skor: 19,
        skor_rata_rata: '2,11',
        klasifikasi: 'sedang',
        klasifikasi_label: 'Kerentanan Sedang',
        foto_rumah: null
    },
    {
        id: 11,
        nama_kk: 'Dominikus Mejai',
        jalur: 'Jalur A',
        rt_rw: 'RT 01 / RW 01',
        alamat: 'RT 01 / RW 01 No. 07',
        latitude: -8.44030,
        longitude: 140.86280,
        pekerjaan: 'Tidak bekerja',
        jumlah_anggota: 6,
        total_skor: 24,
        skor_rata_rata: '2,67',
        klasifikasi: 'tinggi',
        klasifikasi_label: 'Kerentanan Tinggi',
        foto_rumah: null
    },
    {
        id: 12,
        nama_kk: 'Theresia Yolmen',
        jalur: 'Poros Trans',
        rt_rw: 'RT 11 / RW 03',
        alamat: 'RT 11 / RW 03 No. 02',
        latitude: -8.45310,
        longitude: 140.87510,
        pekerjaan: 'Buruh serabutan',
        jumlah_anggota: 5,
        total_skor: 21,
        skor_rata_rata: '2,33',
        klasifikasi: 'tinggi',
        klasifikasi_label: 'Kerentanan Tinggi',
        foto_rumah: null
    },
    {
        id: 13,
        nama_kk: 'Barnabas Kaize',
        jalur: 'Jalur B',
        rt_rw: 'RT 03 / RW 01',
        alamat: 'RT 03 / RW 01 No. 22',
        latitude: -8.44210,
        longitude: 140.86880,
        pekerjaan: 'Petani',
        jumlah_anggota: 3,
        total_skor: 16,
        skor_rata_rata: '1,78',
        klasifikasi: 'sedang',
        klasifikasi_label: 'Kerentanan Sedang',
        foto_rumah: null
    },
    {
        id: 14,
        nama_kk: 'Yakobus Gebze',
        jalur: 'Poros Trans',
        rt_rw: 'RT 06 / RW 02',
        alamat: 'RT 06 / RW 02 No. 19',
        latitude: -8.44710,
        longitude: 140.87010,
        pekerjaan: 'Karyawan swasta',
        jumlah_anggota: 3,
        total_skor: 14,
        skor_rata_rata: '1,56',
        klasifikasi: 'rendah',
        klasifikasi_label: 'Kerentanan Rendah',
        foto_rumah: null
    },
    {
        id: 15,
        nama_kk: 'Paulina Mahuze',
        jalur: 'Poros Trans',
        rt_rw: 'RT 10 / RW 03',
        alamat: 'RT 10 / RW 03 No. 31',
        latitude: -8.45200,
        longitude: 140.87420,
        pekerjaan: 'Petani kebun',
        jumlah_anggota: 4,
        total_skor: 17,
        skor_rata_rata: '1,89',
        klasifikasi: 'sedang',
        klasifikasi_label: 'Kerentanan Sedang',
        foto_rumah: null
    }
];

// SVGs Marker Sesuai PRD 7.1 & 8.3 (22px, garis tepi putih 2px)
export function createCustomMarkerIcon(klasifikasi) {
    let svgContent = '';

    if (klasifikasi === 'rendah') {
        // Lingkaran Hijau #2C7A4B
        svgContent = `
            <svg width="24" height="24" viewBox="0 0 24 24" class="drop-shadow-md">
                <circle cx="12" cy="12" r="9" fill="#2C7A4B" stroke="#FFFFFF" stroke-width="2.5" />
            </svg>
        `;
    } else if (klasifikasi === 'sedang') {
        // Segitiga Kuning #E0A32E
        svgContent = `
            <svg width="24" height="24" viewBox="0 0 24 24" class="drop-shadow-md">
                <polygon points="12,3 21,20 3,20" fill="#E0A32E" stroke="#FFFFFF" stroke-width="2.5" stroke-linejoin="round" />
            </svg>
        `;
    } else {
        // Belah Ketupat Merah #B3352C
        svgContent = `
            <svg width="24" height="24" viewBox="0 0 24 24" class="drop-shadow-md">
                <polygon points="12,2 21,12 12,22 3,12" fill="#B3352C" stroke="#FFFFFF" stroke-width="2.5" stroke-linejoin="round" />
            </svg>
        `;
    }

    return L.divIcon({
        html: `<div class="marker-container hover:scale-125 transition-transform cursor-pointer">${svgContent}</div>`,
        className: 'sota-map-marker',
        iconSize: [24, 24],
        iconAnchor: [12, 12]
    });
}

// Inisialisasi Peta
export function initPetaPublik() {
    const mapContainer = document.getElementById('map-container');
    if (!mapContainer) return;

    // Batasan wilayah Kampung Sota agar pengguna tidak tersesat
    const sotaCenter = [-8.444, 140.869];
    const sotaBounds = [
        [-8.49, 140.82],
        [-8.40, 140.92]
    ];

    const map = L.map('map-container', {
        center: sotaCenter,
        zoom: 15,
        minZoom: 13,
        maxZoom: 19,
        maxBounds: sotaBounds,
        maxBoundsViscosity: 0.8,
        zoomControl: false // Kita pindahkan tombol zoom ke kanan bawah
    });

    // Posisikan Zoom Control di Kanan Bawah
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    // Skala Peta
    L.control.scale({ position: 'bottomright', imperial: false }).addTo(map);

    // Basemaps: OpenStreetMap & Esri World Imagery
    const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap &middot; Data survei Okt 2025 &middot; WGS 84'
    }).addTo(map);

    const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        maxZoom: 19,
        attribution: '&copy; Esri World Imagery &middot; Data survei Okt 2025 &middot; WGS 84'
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

    // Fungsi Render Marker Sesuai Filter
    function updateMarkers(activeFilters) {
        markersCluster.clearLayers();
        markerRegistry.clear();

        let visibleCount = 0;

        DATA_WARGA.forEach(warga => {
            const matchKerentanan = activeFilters.kerentanan.includes(warga.klasifikasi);
            const matchWilayah = activeFilters.wilayah.includes(warga.jalur);

            if (matchKerentanan && matchWilayah) {
                visibleCount++;
                const icon = createCustomMarkerIcon(warga.klasifikasi);
                const marker = L.marker([warga.latitude, warga.longitude], {
                    icon: icon,
                    klasifikasi: warga.klasifikasi,
                    title: warga.nama_kk,
                    alt: `Rumah tangga ${warga.nama_kk}, ${warga.klasifikasi_label}, ${warga.jalur}`
                });

                // Klik marker membuka modal detail (PRD 8.3)
                marker.on('click', () => {
                    window.bukaDetailModal(warga);
                });

                markersCluster.addLayer(marker);
                markerRegistry.set(warga.id, marker);
            }
        });

        // Update teks counter
        const counterEl = document.getElementById('filter-counter-text');
        if (counterEl) {
            counterEl.textContent = `Menampilkan ${visibleCount} rumah tangga terverifikasi`;
        }

        // Peringatan jika filter kosong
        const emptyWarning = document.getElementById('filter-empty-warning');
        if (emptyWarning) {
            if (visibleCount === 0) {
                emptyWarning.classList.remove('hidden');
            } else {
                emptyWarning.classList.add('hidden');
            }
        }
    }

    // State Filter Awal
    const filters = {
        kerentanan: ['rendah', 'sedang', 'tinggi'],
        wilayah: ['Jalur A', 'Jalur B', 'Poros Trans']
    };

    updateMarkers(filters);

    // Listener Filter Checkbox
    window.handleFilterChange = function() {
        const kerentananChecked = Array.from(document.querySelectorAll('input[name="filter_kerentanan"]:checked')).map(el => el.value);
        const wilayahChecked = Array.from(document.querySelectorAll('input[name="filter_wilayah"]:checked')).map(el => el.value);

        filters.kerentanan = kerentananChecked;
        filters.wilayah = wilayahChecked;
        updateMarkers(filters);
    };

    // Atur Ulang Filter
    window.resetFilter = function() {
        document.querySelectorAll('input[name="filter_kerentanan"]').forEach(el => el.checked = true);
        document.querySelectorAll('input[name="filter_wilayah"]').forEach(el => el.checked = true);
        filters.kerentanan = ['rendah', 'sedang', 'tinggi'];
        filters.wilayah = ['Jalur A', 'Jalur B', 'Poros Trans'];
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

        const matches = DATA_WARGA.filter(w => w.nama_kk.toLowerCase().includes(trimmed)).slice(0, 8);

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
                const iconSymbol = w.klasifikasi === 'tinggi' ? '◆' :
                                   w.klasifikasi === 'sedang' ? '▲' : '●';

                return `
                    <li onclick="window.pilihHasilCari(${w.id})"
                        class="p-2.5 hover:bg-paper cursor-pointer border-b border-line last:border-0 flex items-center justify-between gap-2 text-xs">
                        <div>
                            <span class="font-bold text-ink block">${w.nama_kk}</span>
                            <span class="text-muted text-[11px]">${w.jalur} &middot; ${w.rt_rw}</span>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold border ${badgeColor}">
                            <span>${iconSymbol}</span>
                            <span>${w.klasifikasi_label}</span>
                        </span>
                    </li>
                `;
            }).join('');
        }
    };

    // Memilih Saran Pencarian
    window.pilihHasilCari = function(id) {
        const item = DATA_WARGA.find(w => w.id === id);
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
