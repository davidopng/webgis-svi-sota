import Chart from 'chart.js/auto';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// 15 Titik Rumah Tangga Terverifikasi di Kampung Sota (PRD 16)
const samplePoints = [
    { id: 1, nama: 'Petrus Gebze', jalur: 'Jalur A', lat: -8.43920, lng: 140.86410, skor: 23, klas: 'tinggi', klasLabel: 'Kerentanan Tinggi' },
    { id: 2, nama: 'Fransiskus Samkakai', jalur: 'Jalur A', lat: -8.43810, lng: 140.86330, skor: 25, klas: 'tinggi', klasLabel: 'Kerentanan Tinggi' },
    { id: 3, nama: 'Dominikus Mejai', jalur: 'Jalur A', lat: -8.44030, lng: 140.86280, skor: 24, klas: 'tinggi', klasLabel: 'Kerentanan Tinggi' },
    { id: 4, nama: 'Kornelis Basik-Basik', jalur: 'Jalur A', lat: -8.44110, lng: 140.86550, skor: 19, klas: 'sedang', klasLabel: 'Kerentanan Sedang' },
    { id: 5, nama: 'Yosep Mahuze', jalur: 'Jalur A', lat: -8.44200, lng: 140.86600, skor: 18, klas: 'sedang', klasLabel: 'Kerentanan Sedang' },
    { id: 6, nama: 'Yohanis Kaize', jalur: 'Jalur B', lat: -8.44271, lng: 140.86935, skor: 20, klas: 'sedang', klasLabel: 'Kerentanan Sedang' },
    { id: 7, nama: 'Agustina Mahuze', jalur: 'Jalur B', lat: -8.44450, lng: 140.86820, skor: 16, klas: 'sedang', klasLabel: 'Kerentanan Sedang' },
    { id: 8, nama: 'Markus Balagaize', jalur: 'Jalur B', lat: -8.44390, lng: 140.86750, skor: 22, klas: 'tinggi', klasLabel: 'Kerentanan Tinggi' },
    { id: 9, nama: 'Bonaventura Ndiken', jalur: 'Jalur B', lat: -8.44520, lng: 140.87010, skor: 14, klas: 'rendah', klasLabel: 'Kerentanan Rendah' },
    { id: 10, nama: 'Simon Kaize', jalur: 'Jalur B', lat: -8.44610, lng: 140.86900, skor: 13, klas: 'rendah', klasLabel: 'Kerentanan Rendah' },
    { id: 11, nama: 'Siti Rahayu', jalur: 'Poros Trans', lat: -8.44850, lng: 140.87120, skor: 13, klas: 'rendah', klasLabel: 'Kerentanan Rendah' },
    { id: 12, nama: 'Albertina Kwipalo', jalur: 'Poros Trans', lat: -8.44960, lng: 140.87240, skor: 19, klas: 'sedang', klasLabel: 'Kerentanan Sedang' },
    { id: 13, nama: 'Kornelis Ndiken', jalur: 'Poros Trans', lat: -8.45120, lng: 140.87350, skor: 18, klas: 'sedang', klasLabel: 'Kerentanan Sedang' },
    { id: 14, nama: 'Theresia Yolmen', jalur: 'Poros Trans', lat: -8.45310, lng: 140.87510, skor: 21, klas: 'tinggi', klasLabel: 'Kerentanan Tinggi' },
    { id: 15, nama: 'Paulus Mahuze', jalur: 'Poros Trans', lat: -8.45420, lng: 140.87620, skor: 17, klas: 'sedang', klasLabel: 'Kerentanan Sedang' },
];

// Helper Icon Generator Sesuai PRD 7.1
function createIcon(klas) {
    let svgHtml = '';
    if (klas === 'rendah') {
        svgHtml = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="12" r="9" fill="#1B8C5E" stroke="#FFFFFF" stroke-width="2.5" />
        </svg>`;
    } else if (klas === 'sedang') {
        svgHtml = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <polygon points="12,3 22,20 2,20" fill="#D99B08" stroke="#FFFFFF" stroke-width="2.5" stroke-linejoin="round" />
        </svg>`;
    } else {
        svgHtml = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="12" y="2" width="14" height="14" rx="2" transform="rotate(45 12 2)" fill="#D94330" stroke="#FFFFFF" stroke-width="2.5" />
        </svg>`;
    }

    return L.divIcon({
        className: 'sota-map-pin',
        html: svgHtml,
        iconSize: [24, 24],
        iconAnchor: [12, 12],
        popupAnchor: [0, -10]
    });
}

document.addEventListener('DOMContentLoaded', () => {
    // ------------------------------------------------------------------------
    // 1. CHART.JS: PIE CHART PERSENTASE TINGKAT KERENTANAN (PRD 12.1 & 12.2)
    // ------------------------------------------------------------------------
    const pieCanvas = document.getElementById('chart-svi-pie');
    if (pieCanvas) {
        new Chart(pieCanvas, {
            type: 'pie',
            data: {
                labels: ['Kerentanan Rendah', 'Kerentanan Sedang', 'Kerentanan Tinggi'],
                datasets: [{
                    data: [7, 60, 24],
                    backgroundColor: [
                        '#1B8C5E', // Rendah
                        '#D99B08', // Sedang
                        '#D94330', // Tinggi
                    ],
                    borderColor: '#FFFFFF',
                    borderWidth: 2,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false // Legenda kustom ada di bawah sesuai PRD
                    },
                    tooltip: {
                        backgroundColor: '#1E232A',
                        padding: 10,
                        cornerRadius: 8,
                        titleFont: { family: 'Plus Jakarta Sans', size: 12, weight: 'bold' },
                        bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                        callbacks: {
                            label: function(context) {
                                const total = 91;
                                const val = context.raw;
                                const pct = ((val / total) * 100).toFixed(1).replace('.', ',');
                                return ` ${val} KK (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // ------------------------------------------------------------------------
    // 2. CHART.JS: BAR CHART BERTUMPUK PER WILAYAH (PRD 12.1 & 12.2)
    // ------------------------------------------------------------------------
    const barCanvas = document.getElementById('chart-wilayah-bar');
    if (barCanvas) {
        new Chart(barCanvas, {
            type: 'bar',
            data: {
                labels: ['Jalur A (15 KK)', 'Jalur B (33 KK)', 'Poros Trans (43 KK)'],
                datasets: [
                    {
                        label: 'Rendah',
                        data: [0, 4, 3],
                        backgroundColor: '#1B8C5E',
                        borderRadius: 4
                    },
                    {
                        label: 'Sedang',
                        data: [7, 21, 32],
                        backgroundColor: '#D99B08',
                        borderRadius: 4
                    },
                    {
                        label: 'Tinggi',
                        data: [8, 8, 8],
                        backgroundColor: '#D94330',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        stacked: true,
                        grid: { display: false },
                        ticks: {
                            font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' },
                            color: '#475569'
                        }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        max: 50,
                        ticks: {
                            stepSize: 10,
                            font: { family: 'Plus Jakarta Sans', size: 11 },
                            color: '#64748B'
                        },
                        grid: {
                            color: '#E2E8F0'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false // Legenda terpisah di bawah
                    },
                    tooltip: {
                        backgroundColor: '#1E232A',
                        padding: 10,
                        cornerRadius: 8,
                        titleFont: { family: 'Plus Jakarta Sans', size: 12, weight: 'bold' },
                        bodyFont: { family: 'Plus Jakarta Sans', size: 12 }
                    }
                }
            }
        });
    }

    // ------------------------------------------------------------------------
    // 3. LEAFLET: PETA RINGKAS HANYA-BACA (PRD 12.1)
    // ------------------------------------------------------------------------
    const mapContainer = document.getElementById('peta-ringkas-pimpinan');
    if (mapContainer) {
        const map = L.map('peta-ringkas-pimpinan', {
            center: [-8.4485, 140.8712],
            zoom: 14,
            scrollWheelZoom: false, // Jangan menjebak scroll halaman
            attributionControl: false
        });

        // Basemap OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
        }).addTo(map);

        // Tambah Watermark Sederhana
        L.control.attribution({ position: 'bottomright' })
            .addAttribution('&copy; OpenStreetMap')
            .addTo(map);

        // Render 15 Marker Terverifikasi
        samplePoints.forEach(p => {
            const marker = L.marker([p.lat, p.lng], {
                icon: createIcon(p.klas)
            }).addTo(map);

            const popupContent = `
                <div style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 12px; line-height: 1.4; padding: 2px;">
                    <div style="font-weight: 700; color: #1E232A; font-size: 13px;">${p.nama}</div>
                    <div style="color: #64748B; font-size: 11px;">${p.jalur} · Skor SVI: <strong>${p.skor}</strong></div>
                    <div style="margin-top: 4px; display: inline-block; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; ${
                        p.klas === 'tinggi' ? 'background: #FEE2E2; color: #991B1B;' :
                        p.klas === 'sedang' ? 'background: #FEF3C7; color: #92400E;' :
                        'background: #DCFCE7; color: #166534;'
                    }">
                        ${p.klasLabel}
                    </div>
                </div>
            `;
            marker.bindPopup(popupContent);
        });
    }
});
