import Chart from 'chart.js/auto';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Pustaka Ikon Vektor Lucide SVG Resmi (Tanpa Emotikon)
function getLucideSvg(type, size = 16, strokeColor = 'currentColor') {
    const s = size;
    const c = strokeColor;
    const b = `width="${s}" height="${s}" viewBox="0 0 24 24" fill="none" stroke="${c}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"`;
    switch (type) {
        case 'landmark':
            return `<svg ${b}><line x1="3" x2="21" y1="22" y2="22"/><line x1="6" x2="6" y1="18"/><line x1="10" x2="10" y1="18"/><line x1="14" x2="14" y1="18"/><line x1="18" x2="18" y1="18"/><polygon points="12 2 20 7 4 7"/></svg>`;
        case 'building':
            return `<svg ${b}><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M8 10h.01"/><path d="M16 10h.01"/><path d="M8 14h.01"/><path d="M16 14h.01"/></svg>`;
        case 'pin':
            return `<svg ${b}><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>`;
        case 'shield':
            return `<svg ${b}><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>`;
        case 'cross':
            return `<svg ${b}><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M8 12h8"/><path d="M12 8v8"/></svg>`;
        case 'sprout':
            return `<svg ${b}><path d="M7 20h10"/><path d="M10 20c5.5-2.5.8-6.4 3-10"/><path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z"/><path d="M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4 1-4.9 2z"/></svg>`;
        case 'zap':
            return `<svg ${b}><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>`;
        case 'home':
            return `<svg ${b}><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>`;
        case 'church':
            return `<svg ${b}><path d="M12 3v18"/><path d="M8 8h8"/><path d="M4 21h16"/></svg>`;
        case 'moon':
            return `<svg ${b}><path d="M12 3a9 9 0 1 0 9 9c0-.46-.04-.92-.1-1.36a5.389 5.389 0 0 1-4.4 2.26 5.403 5.403 0 0 1-3.14-9.8c-.44-.06-.9-.1-1.36-.1z"/></svg>`;
        case 'fuel':
            return `<svg ${b}><line x1="3" x2="15" y1="22" y2="22"/><line x1="4" x2="14" y1="9" y2="9"/><path d="M14 22V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v18"/><path d="M14 13h2a2 2 0 0 1 2 2v2a2 2 0 0 0 2 2h0a2 2 0 0 0 2-2V9.83a2 2 0 0 0-.59-1.42L18 5"/></svg>`;
        case 'school':
            return `<svg ${b}><path d="M14 22v-4a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v4"/><path d="m18 10 4 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-8l4-2"/><path d="M18 5v17"/><path d="m4 6 8-4 8 4"/><path d="M6 5v17"/><circle cx="12" cy="9" r="2"/></svg>`;
        case 'graduation':
            return `<svg ${b}><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>`;
        case 'mail':
            return `<svg ${b}><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>`;
        default:
            return `<svg ${b}><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>`;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // ========================================================================
    // 1. PETA KAMPUNG SOTA — DENGAN 26 FASILITAS TERVERIFIKASI QGIS
    // ========================================================================
    const mapContainer = document.getElementById('peta-kampung-sota');
    if (mapContainer) {
        // Fix Leaflet Default Marker Icon Paths
        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
            iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
            shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        });

        // Koordinat Pusat Kampung Sota Berdasarkan 26 Titik QGIS
        const defaultCenter = [-8.4285, 141.0070];
        const defaultZoom = 15;

        const map = L.map('peta-kampung-sota', {
            center: defaultCenter,
            zoom: defaultZoom,
            zoomControl: false,
            scrollWheelZoom: true
        });

        // Simpan instance map ke window agar dapat diakses dari Alpine.js (invalidateSize)
        window.leafletMapSota = map;

        // Zoom Control di Kanan Atas
        L.control.zoom({ position: 'topright' }).addTo(map);

        // Tile Layers
        const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        });

        const satelitLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19,
            attribution: '© Esri, Maxar, Earthstar Geographics'
        });

        // Default Layer: Satelit (Sesuai tampilan SS1 / Peta Kampung Sota)
        satelitLayer.addTo(map);
        let currentLayer = 'satelit';

        // Toggle Peta / Satelit
        const btnPeta = document.getElementById('btn-layer-peta');
        const btnSatelit = document.getElementById('btn-layer-satelit');

        function updateLayerButtons(active) {
            if (btnPeta && btnSatelit) {
                if (active === 'peta') {
                    btnPeta.className = 'px-3 py-1.5 text-xs font-bold rounded-lg bg-brand text-white shadow-xs transition-colors';
                    btnSatelit.className = 'px-3 py-1.5 text-xs font-semibold rounded-lg text-ink/70 hover:text-ink hover:bg-paper transition-colors';
                } else {
                    btnSatelit.className = 'px-3 py-1.5 text-xs font-bold rounded-lg bg-brand text-white shadow-xs transition-colors';
                    btnPeta.className = 'px-3 py-1.5 text-xs font-semibold rounded-lg text-ink/70 hover:text-ink hover:bg-paper transition-colors';
                }
            }
        }
        updateLayerButtons('satelit');

        if (btnPeta) {
            btnPeta.addEventListener('click', () => {
                if (currentLayer !== 'peta') {
                    map.removeLayer(satelitLayer);
                    map.addLayer(osmLayer);
                    currentLayer = 'peta';
                    updateLayerButtons('peta');
                }
            });
        }

        if (btnSatelit) {
            btnSatelit.addEventListener('click', () => {
                if (currentLayer !== 'satelit') {
                    map.removeLayer(osmLayer);
                    map.addLayer(satelitLayer);
                    currentLayer = 'satelit';
                    updateLayerButtons('satelit');
                }
            });
        }


        // 26 Fasilitas Resmi Kampung Sota dari Data Spasial Wilayah
        const FASILITAS_SOTA = [
            {
                id: 1,
                nama: "Pos Lintas Batas Negara Sota",
                kategori: "Pemerintahan & PLBN",
                kategori_group: "pemerintahan",
                lat: -8.4283475208400773,
                long: 141.01804666218013,
                iconType: "landmark",
                warna: "#0D9488",
                deskripsi: "Pos Lintas Batas Negara (PLBN) terpadu RI - PNG di Distrik Sota."
            },
            {
                id: 2,
                nama: "Tugu 0 KM Merauke - Sabang",
                kategori: "Landmark & Wisata",
                kategori_group: "layanan_wisata",
                lat: -8.4277593017852706,
                long: 141.01600096702282,
                iconType: "pin",
                warna: "#2563EB",
                deskripsi: "Monumen Titik Nol Kilometer Merauke - Sabang di perbatasan Sota."
            },
            {
                id: 3,
                nama: "Pos Satgas Pamtas",
                kategori: "Keamanan & Pertahanan",
                kategori_group: "pemerintahan",
                lat: -8.4276081621670205,
                long: 141.01401245982919,
                iconType: "shield",
                warna: "#475569",
                deskripsi: "Pos Pengamanan Perbatasan Satuan Tugas TNI AD RI - PNG."
            },
            {
                id: 4,
                nama: "Polsek Sota",
                kategori: "Keamanan & Ketertiban",
                kategori_group: "pemerintahan",
                lat: -8.4283695790546282,
                long: 141.01159749382086,
                iconType: "shield",
                warna: "#1E3A8A",
                deskripsi: "Kantor Kepolisian Sektor Sota Polres Merauke."
            },
            {
                id: 5,
                nama: "Kantor Kesehatan Pelabuhan - PLBDN",
                kategori: "Kesehatan",
                kategori_group: "kesehatan",
                lat: -8.4278793965089704,
                long: 141.01214323038838,
                iconType: "cross",
                warna: "#E11D48",
                deskripsi: "Fasilitas Pengawasan Kesehatan Lintas Batas Darat Negara."
            },
            {
                id: 6,
                nama: "Karantina Pertanian Sota",
                kategori: "Layanan Publik",
                kategori_group: "layanan_wisata",
                lat: -8.4288270827639078,
                long: 141.01150027428261,
                iconType: "sprout",
                warna: "#16A34A",
                deskripsi: "Balai Karantina Pertanian Wilker Perbatasan Sota."
            },
            {
                id: 7,
                nama: "PLN Kampung Sota",
                kategori: "Layanan Publik",
                kategori_group: "layanan_wisata",
                lat: -8.4300803161389943,
                long: 141.01068983914047,
                iconType: "zap",
                warna: "#D97706",
                deskripsi: "Kantor Pelayanan & Distribusi Listrik PLN Kampung Sota."
            },
            {
                id: 8,
                nama: "Kantor Kampung Sota",
                kategori: "Pemerintahan",
                kategori_group: "pemerintahan",
                lat: -8.4291179244076755,
                long: 141.00946234034902,
                iconType: "building",
                warna: "#0D9488",
                deskripsi: "Kantor Balai Pertemuan & Pemerintahan Kampung Sota."
            },
            {
                id: 9,
                nama: "Perumahan ASN Kampung Sota",
                kategori: "Fasilitas Umum",
                kategori_group: "layanan_wisata",
                lat: -8.4325426664600318,
                long: 141.00984795061825,
                iconType: "home",
                warna: "#64748B",
                deskripsi: "Kompleks Perumahan Aparatur Sipil Negara Kampung Sota."
            },
            {
                id: 10,
                nama: "Puskesmas Sota",
                kategori: "Kesehatan",
                kategori_group: "kesehatan",
                lat: -8.4315230867650612,
                long: 141.00924992791258,
                iconType: "cross",
                warna: "#E11D48",
                deskripsi: "Pusat Kesehatan Masyarakat Sota dengan unit rawat inap."
            },
            {
                id: 11,
                nama: "Masjid Nurul Huda Sota",
                kategori: "Tempat Ibadah",
                kategori_group: "ibadah",
                lat: -8.4308458178810728,
                long: 141.00762252186081,
                iconType: "moon",
                warna: "#059669",
                deskripsi: "Masjid Nurul Huda Sota, sarana peribadatan umat Muslim."
            },
            {
                id: 12,
                nama: "Kantor Distrik Sota",
                kategori: "Pemerintahan",
                kategori_group: "pemerintahan",
                lat: -8.4311105164557532,
                long: 141.00665196042033,
                iconType: "building",
                warna: "#0D9488",
                deskripsi: "Pusat Pelayanan Administrasi Pemerintahan Distrik Sota."
            },
            {
                id: 13,
                nama: "Gereja Katolik St. Arnoldus Janssen",
                kategori: "Tempat Ibadah",
                kategori_group: "ibadah",
                lat: -8.4285321562653479,
                long: 141.00857837782493,
                iconType: "church",
                warna: "#6366F1",
                deskripsi: "Gereja Katolik Paroki St. Arnoldus Janssen Sota."
            },
            {
                id: 14,
                nama: "Gereja GPI Papua Jemaat EDEN",
                kategori: "Tempat Ibadah",
                kategori_group: "ibadah",
                lat: -8.4275746663594084,
                long: 141.00857837782493,
                iconType: "church",
                warna: "#6366F1",
                deskripsi: "Gereja Protestan Indonesia (GPI) Papua Jemaat EDEN Sota."
            },
            {
                id: 15,
                nama: "Gereja GBI Jemaat Agape",
                kategori: "Tempat Ibadah",
                kategori_group: "ibadah",
                lat: -8.4270501710355141,
                long: 141.00727939407875,
                iconType: "church",
                warna: "#6366F1",
                deskripsi: "Gereja Bethel Indonesia (GBI) Jemaat Agape Sota."
            },
            {
                id: 16,
                nama: "Gereja GKAI Jemaat Imanuel",
                kategori: "Tempat Ibadah",
                kategori_group: "ibadah",
                lat: -8.4231875325753727,
                long: 141.00245599782903,
                iconType: "church",
                warna: "#6366F1",
                deskripsi: "Gereja Kemah Daud / GKAI Jemaat Imanuel Sota."
            },
            {
                id: 17,
                nama: "SPBU Sota",
                kategori: "Layanan Publik",
                kategori_group: "layanan_wisata",
                lat: -8.4219326652583799,
                long: 140.99887439736165,
                iconType: "fuel",
                warna: "#EA580C",
                deskripsi: "Stasiun Pengisian Bahan Bakar Umum (SPBU) Trans Papua Sota."
            },
            {
                id: 18,
                nama: "Gereja GPDI Jemaat Karmel",
                kategori: "Tempat Ibadah",
                kategori_group: "ibadah",
                lat: -8.4228705478624804,
                long: 140.99914236381997,
                iconType: "church",
                warna: "#6366F1",
                deskripsi: "Gereja Pantekosta di Indonesia (GPdI) Jemaat Karmel Sota."
            },
            {
                id: 19,
                nama: "Tugu Kembar Sabang - Merauke",
                kategori: "Landmark & Wisata",
                kategori_group: "layanan_wisata",
                lat: -8.4225127146041299,
                long: 140.99837931299049,
                iconType: "pin",
                warna: "#2563EB",
                deskripsi: "Monumen Bersejarah Tugu Kembar Sabang - Merauke."
            },
            {
                id: 20,
                nama: "SMP Negeri 11 Sota",
                kategori: "Pendidikan",
                kategori_group: "pendidikan",
                lat: -8.4235347452119118,
                long: 140.99687445257518,
                iconType: "school",
                warna: "#7C3AED",
                deskripsi: "SMP Negeri 11 Merauke di Kampung Sota."
            },
            {
                id: 21,
                nama: "SMK Negeri 1 Sota",
                kategori: "Pendidikan",
                kategori_group: "pendidikan",
                lat: -8.4237863722520405,
                long: 140.9959627130402,
                iconType: "graduation",
                warna: "#7C3AED",
                deskripsi: "SMK Negeri 1 Sota, sekolah kejuruan perbatasan."
            },
            {
                id: 22,
                nama: "Gereja GIDI Jemaat Kasih",
                kategori: "Tempat Ibadah",
                kategori_group: "ibadah",
                lat: -8.4382647307102925,
                long: 141.00177137620673,
                iconType: "church",
                warna: "#6366F1",
                deskripsi: "Gereja Injili di Indonesia (GIDI) Jemaat Kasih Sota."
            },
            {
                id: 23,
                nama: "Gereja GKI Shalom Sota",
                kategori: "Tempat Ibadah",
                kategori_group: "ibadah",
                lat: -8.4343203951591903,
                long: 141.00501311677564,
                iconType: "church",
                warna: "#6366F1",
                deskripsi: "GKI di Tanah Papua Jemaat Shalom Sota."
            },
            {
                id: 24,
                nama: "Gereja Advent Sota",
                kategori: "Tempat Ibadah",
                kategori_group: "ibadah",
                lat: -8.4326080241328665,
                long: 141.007294099555,
                iconType: "church",
                warna: "#6366F1",
                deskripsi: "Gereja Masehi Advent Hari Ketujuh (GMAHK) Sota."
            },
            {
                id: 25,
                nama: "Masjid Darul Falah Sota",
                kategori: "Tempat Ibadah",
                kategori_group: "ibadah",
                lat: -8.4321570561908246,
                long: 141.00155569588665,
                iconType: "moon",
                warna: "#059669",
                deskripsi: "Masjid Darul Falah Sota, tempat ibadah umat Muslim."
            },
            {
                id: 26,
                nama: "Kantor Pos Sota",
                kategori: "Layanan Publik",
                kategori_group: "layanan_wisata",
                lat: -8.4264382598242946,
                long: 141.0021733258942,
                iconType: "mail",
                warna: "#D97706",
                deskripsi: "Kantor Pos Indonesia Unit Layanan Sota."
            }
        ];

        // Layer Group untuk Markers dan Kamus Penampung Marker
        const markersGroup = L.layerGroup().addTo(map);
        const markerMap = {};

        // Custom Marker Pin HTML Icon (Dikecilkan proporsional 20px dengan SVG Lucide resmi)
        function createCustomPin(item) {
            return L.divIcon({
                className: 'custom-poi-marker',
                html: `
                    <div style="
                        background-color: ${item.warna};
                        width: 20px;
                        height: 20px;
                        border-radius: 50% 50% 50% 0;
                        transform: rotate(-45deg);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        box-shadow: 0 2px 6px rgba(0,0,0,0.35);
                        border: 1.5px solid #FFFFFF;
                        cursor: pointer;
                        transition: transform 0.2s ease;
                    ">
                        <span style="
                            transform: rotate(45deg);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: #FFFFFF;
                        ">${getLucideSvg(item.iconType, 10, '#FFFFFF')}</span>
                    </div>
                `,
                iconSize: [20, 20],
                iconAnchor: [10, 20],
                popupAnchor: [0, -22]
            });
        }

        // Popup HTML dengan Detail Nama, Kategori, dan Latitude Longitude
        function createPopupHtml(item) {
            return `
                <div style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif; min-width: 230px; padding: 2px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 6px;">
                        <span style="font-size: 10px; font-weight: 700; text-transform: uppercase; background: ${item.warna}18; color: ${item.warna}; padding: 2px 8px; border-radius: 9999px; border: 1px solid ${item.warna}35;">
                            ${item.kategori}
                        </span>
                        <div style="width: 24px; height: 24px; border-radius: 6px; background-color: ${item.warna}18; border: 1px solid ${item.warna}35; display: flex; align-items: center; justify-content: center; color: ${item.warna};">
                            ${getLucideSvg(item.iconType, 13, item.warna)}
                        </div>
                    </div>
                    <h4 style="font-weight: 800; color: #0F172A; font-size: 13.5px; margin: 0 0 4px 0; line-height: 1.35;">${item.nama}</h4>
                    <p style="color: #64748B; font-size: 11.5px; margin: 0 0 8px 0; line-height: 1.4;">${item.deskripsi}</p>
                    
                    <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 6px 8px; margin-bottom: 8px;">
                        <div style="font-size: 9.5px; font-weight: 700; text-transform: uppercase; color: #64748B; letter-spacing: 0.05em; margin-bottom: 3px;">
                            Koordinat Geografis (WGS84)
                        </div>
                        <div style="font-size: 11.5px; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; color: #0F172A; line-height: 1.45;">
                            <div><span style="color: #64748B;">Latitude:</span> <strong>${item.lat.toFixed(6)}</strong></div>
                            <div><span style="color: #64748B;">Longitude:</span> <strong>${item.long.toFixed(6)}</strong></div>
                        </div>
                    </div>

                    <button onclick="window.zoomToFasilitas(${item.id})" type="button" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 11px; font-weight: 700; background: #0D9488; color: #FFFFFF; padding: 6px 10px; border-radius: 6px; border: none; cursor: pointer; transition: opacity 0.2s;">
                        <span>🔍 Perbesar Lokasi (Zoom To)</span>
                    </button>
                </div>
            `;
        }

        // Render Markers ke Leaflet Map
        function renderMarkers() {
            markersGroup.clearLayers();

            FASILITAS_SOTA.forEach(item => {
                const marker = L.marker([item.lat, item.long], {
                    icon: createCustomPin(item),
                    title: item.nama
                });

                marker.bindPopup(createPopupHtml(item), { maxWidth: 280 });

                marker.on('click', () => {
                    highlightSidePanelItem(item.id);
                });

                markersGroup.addLayer(marker);
                markerMap[item.id] = marker;
            });
        }

        // Highlight Item di Side Panel
        function highlightSidePanelItem(id) {
            document.querySelectorAll('.side-panel-item').forEach(el => {
                el.classList.remove('bg-brand/10', '!border-l-brand');
            });
            const activeEl = document.getElementById(`side-panel-item-${id}`);
            if (activeEl) {
                activeEl.classList.add('bg-brand/10', '!border-l-brand');
                activeEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }

        // Zoom to Fasilitas (Global function untuk pemanggilan dari side panel & popup)
        window.zoomToFasilitas = function(id) {
            const item = FASILITAS_SOTA.find(f => f.id === id);
            if (!item) return;

            map.flyTo([item.lat, item.long], 17, {
                animate: true,
                duration: 1.1
            });

            const marker = markerMap[item.id];
            if (marker) {
                setTimeout(() => {
                    marker.openPopup();
                }, 500);
            }

            highlightSidePanelItem(item.id);
        };

        // Render Daftar Fasilitas di Collapsible Side Panel (SS2)
        const listContainer = document.getElementById('side-panel-facility-list');
        const countLabel = document.getElementById('side-panel-count-label');
        const searchInput = document.getElementById('fasil-search-input');
        const filterButtons = document.querySelectorAll('.fasil-filter-btn');

        let currentCategory = 'all';
        let currentSearch = '';

        function renderSidePanelList() {
            if (!listContainer) return;
            listContainer.innerHTML = '';

            const q = currentSearch.toLowerCase().trim();
            const filtered = FASILITAS_SOTA.filter(item => {
                const matchCategory = currentCategory === 'all' || item.kategori_group === currentCategory;
                const matchSearch = q === '' || item.nama.toLowerCase().includes(q) || item.kategori.toLowerCase().includes(q) || item.deskripsi.toLowerCase().includes(q);
                return matchCategory && matchSearch;
            });

            if (countLabel) {
                countLabel.textContent = `${filtered.length} Fasilitas Ditampilkan`;
            }

            if (filtered.length === 0) {
                listContainer.innerHTML = `
                    <div class="p-6 text-center text-muted text-xs">
                        <p class="font-bold text-ink mb-1">Tidak ada fasilitas ditemukan</p>
                        <p>Coba kata kunci pencarian atau kategori lain.</p>
                    </div>
                `;
                return;
            }

            filtered.forEach(item => {
                const div = document.createElement('div');
                div.id = `side-panel-item-${item.id}`;
                div.className = 'side-panel-item p-3 hover:bg-slate-50 cursor-pointer transition-colors border-l-4 border-l-transparent';
                div.innerHTML = `
                    <div class="flex items-start gap-2.5">
                        <div class="w-8 h-8 rounded-lg shrink-0 flex items-center justify-center shadow-2xs"
                             style="background-color: ${item.warna}18; border: 1px solid ${item.warna}35; color: ${item.warna};">
                            ${getLucideSvg(item.iconType, 16, item.warna)}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1 mb-0.5">
                                <span class="text-[9.5px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded"
                                      style="background-color: ${item.warna}15; color: ${item.warna};">
                                    ${item.kategori}
                                </span>
                            </div>
                            <h4 class="text-xs font-bold text-ink leading-snug truncate hover:text-brand" title="${item.nama}">
                                ${item.nama}
                            </h4>
                            <div class="mt-1 flex items-center gap-1.5 text-[10px] font-mono text-muted">
                                <span>Lat: ${item.lat.toFixed(4)}</span>
                                <span>•</span>
                                <span>Long: ${item.long.toFixed(4)}</span>
                            </div>
                        </div>
                        <button type="button" class="shrink-0 p-1 text-muted hover:text-brand transition-colors" title="Zoom ke Lokasi Peta">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </button>
                    </div>
                `;

                div.addEventListener('click', () => {
                    window.zoomToFasilitas(item.id);
                });

                listContainer.appendChild(div);
            });
        }

        // Event Listener Search & Filter Side Panel
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                currentSearch = e.target.value;
                renderSidePanelList();
            });
        }

        filterButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                filterButtons.forEach(b => {
                    b.className = 'fasil-filter-btn px-2 py-1 rounded-md font-medium text-muted hover:text-ink hover:bg-white whitespace-nowrap transition-colors';
                });
                btn.className = 'fasil-filter-btn px-2.5 py-1 rounded-md font-bold bg-brand text-white shadow-2xs whitespace-nowrap transition-colors';
                currentCategory = btn.getAttribute('data-cat') || 'all';
                renderSidePanelList();
            });
        });

        // Fit Bounds Seluruh Titik Fasilitas
        function fitAllMarkers() {
            if (Object.keys(markerMap).length > 0) {
                const groupBounds = L.featureGroup(Object.values(markerMap)).getBounds();
                if (groupBounds.isValid()) {
                    map.fitBounds(groupBounds.pad(0.08));
                }
            }
        }

        // Reset View Button
        const btnResetMap = document.getElementById('btn-reset-map-view');
        if (btnResetMap) {
            btnResetMap.addEventListener('click', () => {
                fitAllMarkers();
            });
        }

        // Initial Executions
        renderMarkers();
        renderSidePanelList();

        // Fit Bounds pada render awal
        setTimeout(() => {
            fitAllMarkers();
            map.invalidateSize();
        }, 250);
    }

    // ========================================================================
    // LIGHTBOX / MODAL POPUP FOTO KANTOR KAMPUNG SOTA DI DALAM HALAMAN
    // ========================================================================
    window.openFotoKantorModal = function (
        imgSrc = '/images/kantor_kp.jpg',
        title = 'Kantor Balai Kampung Sota',
        desc = 'Jl. Poros Trans-Papua, Distrik Sota, Kab. Merauke, Papua Selatan'
    ) {
        let modal = document.getElementById('modal-foto-kantor');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'modal-foto-kantor';
            modal.className = 'fixed inset-0 z-[2000] flex items-center justify-center p-3 sm:p-6 bg-slate-950/85 backdrop-blur-md transition-all duration-200 opacity-0 pointer-events-none';
            modal.setAttribute('role', 'dialog');
            modal.setAttribute('aria-modal', 'true');
            modal.innerHTML = `
                <div id="modal-foto-kantor-card" 
                     class="relative w-full max-w-4xl bg-slate-900 text-white rounded-2xl overflow-hidden shadow-2xl border border-white/15 flex flex-col max-h-[92vh] transform scale-95 transition-all duration-200"
                     onclick="event.stopPropagation()">
                    
                    <!-- Modal Header -->
                    <div class="px-5 py-3.5 border-b border-white/10 bg-slate-900/95 flex items-center justify-between gap-3 shrink-0">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-lg bg-teal-500/20 text-teal-400 flex items-center justify-center font-bold text-sm shrink-0 border border-teal-500/30">
                                🏛️
                            </div>
                            <div class="min-w-0">
                                <h3 id="modal-foto-title" class="text-sm sm:text-base font-extrabold text-white leading-tight truncate">
                                    ${title}
                                </h3>
                                <p id="modal-foto-desc" class="text-xs text-slate-400 truncate mt-0.5">
                                    ${desc}
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2 shrink-0">
                            <button id="modal-foto-close-btn" 
                                    type="button" 
                                    class="p-2 rounded-xl bg-white/10 hover:bg-rose-600 text-white transition-colors flex items-center justify-center group"
                                    title="Tutup Modal (ESC)">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body (Foto Resolusi Penuh) -->
                    <div class="relative flex-1 bg-black/80 flex items-center justify-center overflow-auto p-2 sm:p-4 min-h-[300px]">
                        <img id="modal-foto-img" 
                             src="${imgSrc}" 
                             alt="${title}" 
                             class="max-w-full max-h-[70vh] object-contain rounded-xl shadow-2xl select-none" />
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-5 py-3 bg-slate-900/95 border-t border-white/10 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-300 shrink-0">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></span>
                            <span class="font-semibold text-teal-300">Pemerintah Kampung Sota, Distrik Sota</span>
                            <span class="text-slate-500 hidden sm:inline">•</span>
                            <span class="font-mono text-slate-300 text-[11px] hidden sm:inline">📍 -8.429164, 141.009468</span>
                        </div>
                        <button type="button" 
                                onclick="window.closeFotoKantorModal()"
                                class="px-4 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            const closeBtn = modal.querySelector('#modal-foto-close-btn');
            if (closeBtn) closeBtn.addEventListener('click', window.closeFotoKantorModal);

            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    window.closeFotoKantorModal();
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !modal.classList.contains('pointer-events-none')) {
                    window.closeFotoKantorModal();
                }
            });
        }

        const imgEl = modal.querySelector('#modal-foto-img');
        const titleEl = modal.querySelector('#modal-foto-title');
        const descEl = modal.querySelector('#modal-foto-desc');
        if (imgEl) imgEl.src = imgSrc;
        if (titleEl) titleEl.textContent = title;
        if (descEl) descEl.textContent = desc;

        modal.classList.remove('pointer-events-none', 'opacity-0');
        modal.classList.add('opacity-100');
        const card = modal.querySelector('#modal-foto-kantor-card');
        if (card) {
            card.classList.remove('scale-95');
            card.classList.add('scale-100');
        }
        document.body.style.overflow = 'hidden';
    };

    window.closeFotoKantorModal = function () {
        const modal = document.getElementById('modal-foto-kantor');
        if (!modal) return;
        modal.classList.add('opacity-0', 'pointer-events-none');
        modal.classList.remove('opacity-100');
        const card = modal.querySelector('#modal-foto-kantor-card');
        if (card) {
            card.classList.add('scale-95');
            card.classList.remove('scale-100');
        }
        document.body.style.overflow = '';
    };

    // ========================================================================
    // 2. MINI PETA LOKASI KANTOR BALAI KAMPUNG SOTA (SECTION KONTAK - SS2)
    // ========================================================================
    const kantorMapContainer = document.getElementById('peta-lokasi-kantor');
    if (kantorMapContainer) {
        // Titik Koordinat Balai Kampung Sota Akurat: -8.429164433945415, 141.00946823348727
        const kantorCoords = [-8.429164433945415, 141.00946823348727];
        const kantorMap = L.map('peta-lokasi-kantor', {
            center: kantorCoords,
            zoom: 16,
            zoomControl: true,
            scrollWheelZoom: false
        });

        // Google Maps Satellite Layer (No Label: lyrs=s) sesuai permintaan user
        const googleSatLayer = L.tileLayer('https://mt{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['0', '1', '2', '3'],
            attribution: '© Google Satellite'
        });

        // Fallback Esri World Imagery (Satellite) jika terjadi kendala koneksi tile Google
        const esriSatFallback = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19,
            attribution: '© Esri, Maxar, Earthstar Geographics'
        });

        googleSatLayer.on('tileerror', function () {
            if (!kantorMap.hasLayer(esriSatFallback)) {
                esriSatFallback.addTo(kantorMap);
            }
        });

        googleSatLayer.addTo(kantorMap);

        // Pin Kantor Elegan dengan Lucide SVG
        const kantorPin = L.divIcon({
            className: 'custom-kantor-marker',
            html: `
                <div style="
                    background-color: #0D9488;
                    width: 32px;
                    height: 32px;
                    border-radius: 50% 50% 50% 0;
                    transform: rotate(-45deg);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.45);
                    border: 2px solid #FFFFFF;
                    cursor: pointer;
                    transition: transform 0.2s ease;
                ">
                    <span style="
                        transform: rotate(45deg);
                        color: #FFFFFF;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    ">
                        ${getLucideSvg('landmark', 16, '#FFFFFF')}
                    </span>
                </div>
            `,
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -34]
        });

        const kantorMarker = L.marker(kantorCoords, { 
            icon: kantorPin,
            title: 'Kantor Balai Kampung Sota'
        }).addTo(kantorMap);
        
        // Popup dengan Foto Kantor Balai Kampung Sota (/images/kantor_kp.jpg)
        const popupHtml = `
            <div style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif; font-size: 12px; line-height: 1.45; min-width: 250px; max-width: 290px; padding: 2px;">
                <div onclick="window.openFotoKantorModal('/images/kantor_kp.jpg', 'Kantor Balai Kampung Sota', 'Jl. Poros Trans-Papua, Distrik Sota, Kab. Merauke, Papua Selatan')"
                     style="width: 100%; height: 135px; border-radius: 8px; overflow: hidden; margin-bottom: 8px; border: 1px solid #CBD5E1; background: #0F172A; position: relative; cursor: pointer;"
                     title="Klik untuk membuka foto besar di dalam halaman">
                    <img src="/images/kantor_kp.jpg" 
                         alt="Foto Kantor Balai Kampung Sota" 
                         style="width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.3s ease;"
                         onmouseover="this.style.transform='scale(1.05)'"
                         onmouseout="this.style.transform='scale(1)'"
                         onerror="this.onerror=null; this.src='/images/newplbn.jpg';" />
                    <span style="position: absolute; bottom: 5px; right: 6px; background: rgba(15,23,42,0.85); color: #fff; font-size: 9.5px; font-weight: 700; padding: 2.5px 7px; border-radius: 6px; pointer-events: none; backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,0.2);">
                        🔍 Klik Foto Perbesar
                    </span>
                </div>
                <div style="margin-bottom: 4px;">
                    <span style="font-size: 9.5px; font-weight: 700; text-transform: uppercase; background: #0D948818; color: #0D9488; padding: 2px 8px; border-radius: 9999px; border: 1px solid #0D948835; display: inline-block;">
                        Pemerintahan Kampung
                    </span>
                    <h4 style="font-weight: 800; color: #0F172A; font-size: 13.5px; margin: 4px 0 2px 0; line-height: 1.3;">Kantor Balai Kampung Sota</h4>
                </div>
                <p style="color: #475569; font-size: 11px; margin: 0 0 6px 0; line-height: 1.4;">
                    Jl. Poros Trans-Papua, Distrik Sota, Kab. Merauke, Papua Selatan (99614).
                </p>
                <div style="font-size: 10.5px; color: #0D9488; font-weight: 700; border-top: 1px solid #E2E8F0; padding-top: 5px; margin-bottom: 8px;">
                    📍 -8.429164, 141.009468
                </div>
                <a href="https://maps.app.goo.gl/h8QSBSEPQsRpMHL38" target="_blank" rel="noopener noreferrer" style="display: flex; align-items: center; justify-content: center; gap: 5px; font-size: 11px; font-weight: 700; background: #0D9488; color: #FFFFFF; padding: 6px 10px; border-radius: 6px; text-decoration: none; box-shadow: 0 2px 4px rgba(13,148,136,0.25);">
                    <span>Buka Rute Google Maps</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                </a>
            </div>
        `;

        kantorMarker.bindPopup(popupHtml, { maxWidth: 300, minWidth: 250 });
        kantorMarker.bindTooltip('Kantor Balai Kampung Sota', {
            direction: 'top',
            offset: [0, -34],
            className: 'font-sans font-semibold text-xs'
        });

        // Invalidate size saat dimuat dan resize agar basemap selalu tampil penuh
        setTimeout(() => {
            kantorMap.invalidateSize();
        }, 200);

        window.addEventListener('resize', () => {
            kantorMap.invalidateSize();
        });
    }

    // ========================================================================
    // 3. CHART BAR BERTUMPUK: SEBARAN SEKTOR PEKERJAAN PER WILAYAH (LEBAR PENUH)
    // ========================================================================
    const barCanvas = document.getElementById('chart-infografis-bar');
    if (barCanvas) {
        const stats = window.SOTA_PEKERJAAN_STATS || {
            labels: ['Jalur A', 'Jalur B', 'Poros Trans'],
            informal: [15, 29, 15],
            formal: [1, 7, 21],
            harian: [2, 1, 4]
        };

        new Chart(barCanvas, {
            type: 'bar',
            data: {
                labels: stats.labels,
                datasets: [
                    {
                        label: 'Sektor Informal & Pertanian',
                        data: stats.informal,
                        backgroundColor: '#0D9488',
                        borderRadius: 6,
                        maxBarThickness: 64
                    },
                    {
                        label: 'Sektor Formal / Bergaji Tetap',
                        data: stats.formal,
                        backgroundColor: '#4F46E5',
                        borderRadius: 6,
                        maxBarThickness: 64
                    },
                    {
                        label: 'Sektor Harian / Rentan',
                        data: stats.harian,
                        backgroundColor: '#D97706',
                        borderRadius: 6,
                        maxBarThickness: 64
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        stacked: true,
                        grid: { display: false }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: { stepSize: 10 }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 15,
                            font: { size: 11 }
                        }
                    }
                }
            }
        });
    }
});
