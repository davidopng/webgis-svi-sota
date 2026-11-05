import Chart from 'chart.js/auto';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Titik Rumah Tangga Terverifikasi di Kampung Sota (dari Database)
const samplePoints = window.SOTA_REAL_WARGA || [];
const sotaStats = window.SOTA_STATS || { total: 0, rendah: 0, sedang: 0, tinggi: 0 };

// Helper Icon Generator Sesuai PRD 7.1
function createIcon(klas) {
    let fillColor = '#1B8C5E'; // Rendah: Hijau
    if (klas === 'sedang') {
        fillColor = '#D99B08'; // Sedang: Kuning
    } else if (klas === 'tinggi') {
        fillColor = '#D94330'; // Tinggi: Merah
    }

    const svgHtml = `<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg" class="drop-shadow-sm">
        <circle cx="9" cy="9" r="7" fill="${fillColor}" stroke="#FFFFFF" stroke-width="2" />
    </svg>`;

    return L.divIcon({
        className: 'sota-map-pin',
        html: svgHtml,
        iconSize: [18, 18],
        iconAnchor: [9, 9],
        popupAnchor: [0, -8]
    });
}

document.addEventListener('DOMContentLoaded', () => {
    // ------------------------------------------------------------------------
    // 1. CHART.JS: PIE CHART PERSENTASE TINGKAT KERENTANAN (PRD 12.1 & 12.2)
    // ------------------------------------------------------------------------
    const pieCanvas = document.getElementById('chart-svi-pie');
    if (pieCanvas) {
        const pieData = [sotaStats.rendah, sotaStats.sedang, sotaStats.tinggi];
        const hasData = sotaStats.total > 0;

        new Chart(pieCanvas, {
            type: 'pie',
            data: {
                labels: ['Kerentanan Rendah', 'Kerentanan Sedang', 'Kerentanan Tinggi'],
                datasets: [{
                    data: hasData ? pieData : [1],
                    backgroundColor: hasData ? [
                        '#1B8C5E', // Rendah
                        '#D99B08', // Sedang
                        '#D94330', // Tinggi
                    ] : ['#CBD5E1'],
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
                        enabled: hasData,
                        backgroundColor: '#1E232A',
                        padding: 10,
                        cornerRadius: 8,
                        titleFont: { family: 'Plus Jakarta Sans', size: 12, weight: 'bold' },
                        bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                        callbacks: {
                            label: function(context) {
                                const total = sotaStats.total || 1;
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
        // Agregasi dari data riil
        const jalurList = ['Jalur A', 'Jalur B', 'Poros Trans'];
        const checkJalur = (jalurStr, jName) => {
            const str = (jalurStr || '').toLowerCase();
            if (jName === 'Jalur A') return /jalur.*a|jalur\s*a/.test(str);
            if (jName === 'Jalur B') return /jalur.*b|jalur\s*b/.test(str);
            if (jName === 'Poros Trans') return /poros/.test(str);
            return false;
        };

        const getCount = (jName, klas) => {
            return samplePoints.filter(p => checkJalur(p.jalur, jName) && (p.klasifikasi || '').toLowerCase() === klas).length;
        };

        const barLabels = jalurList.map(j => {
            const tot = samplePoints.filter(p => checkJalur(p.jalur, j)).length;
            return `${j} (${tot} KK)`;
        });

        new Chart(barCanvas, {
            type: 'bar',
            data: {
                labels: barLabels,
                datasets: [
                    {
                        label: 'Rendah',
                        data: jalurList.map(j => getCount(j, 'rendah')),
                        backgroundColor: '#1B8C5E',
                        borderRadius: 4
                    },
                    {
                        label: 'Sedang',
                        data: jalurList.map(j => getCount(j, 'sedang')),
                        backgroundColor: '#D99B08',
                        borderRadius: 4
                    },
                    {
                        label: 'Tinggi',
                        data: jalurList.map(j => getCount(j, 'tinggi')),
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
                        ticks: {
                            stepSize: 5,
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
                        display: false
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
            center: [-8.4285, 141.0070],
            zoom: 14,
            scrollWheelZoom: false, // Jangan menjebak scroll halaman
            attributionControl: false
        });

        // Basemap OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
        }).addTo(map);

        L.control.attribution({ position: 'bottomright' })
            .addAttribution('&copy; OpenStreetMap')
            .addTo(map);

        // Render Marker Terverifikasi dari Database
        samplePoints.forEach(p => {
            const lat = parseFloat(p.latitude || p.lat);
            const lng = parseFloat(p.longitude || p.lng);
            if (!lat || !lng) return;

            const marker = L.marker([lat, lng], {
                icon: createIcon(p.klasifikasi || p.klas)
            }).addTo(map);

            const popupContent = `
                <div style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 12px; line-height: 1.4; padding: 2px;">
                    <div style="font-weight: 700; color: #1E232A; font-size: 13px;">${p.nama_kk || p.nama}</div>
                    <div style="color: #64748B; font-size: 11px;">${p.jalur || '-'} · Skor SVI: <strong>${p.skor_svi || p.skor || '-'}</strong></div>
                    <div style="margin-top: 4px; display: inline-block; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; ${
                        (p.klasifikasi || p.klas) === 'tinggi' ? 'background: #FEE2E2; color: #991B1B;' :
                        (p.klasifikasi || p.klas) === 'sedang' ? 'background: #FEF3C7; color: #92400E;' :
                        'background: #DCFCE7; color: #166534;'
                    }">
                        Kerentanan ${((p.klasifikasi || p.klas) || '').toUpperCase()}
                    </div>
                </div>
            `;
            marker.bindPopup(popupContent);
        });
    }
});
