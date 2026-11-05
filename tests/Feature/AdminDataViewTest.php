<?php

namespace Tests\Feature;

use App\Models\DataPenduduk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDataViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_data_page_renders_cleanly_without_js_leak(): void
    {
        $admin = User::create([
            'name' => 'Jhoni Arafat',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        DataPenduduk::create([
            'nama_kk' => 'JEFRI KORWA',
            'alamat' => 'Kampung Sota RT 01 / RW 01',
            'jalur' => 'Jalur A',
            'latitude' => -8.498,
            'longitude' => 140.781,
            'pendapatan' => 'Rp 1.000.000 - Rp 2.000.000',
            'pekerjaan' => 'Petani',
            'pendidikan' => 'SMA/SMK',
            'jenis_dinding' => 'Papan',
            'jenis_lantai' => 'Semen',
            'fasilitas_sanitasi' => 'Jamban Pribadi',
            'sumber_air' => 'Sumur',
            'pendapatan_tambahan' => 'Ada',
            'kepemilikan_lahan' => 'Milik Sendiri',
            'total_skor' => 19,
            'klasifikasi' => 'sedang',
            'status_verifikasi' => 'terverifikasi',
        ]);

        $response = $this->actingAs($admin)->get('/admin/data');

        $response->assertStatus(200);
        $response->assertSee('<div x-data="adminDataWarga()" class="space-y-6">', false);
        $response->assertDontSee('get filteredData()()', false);
        // Ensure no leaked JS expression into HTML body
        $response->assertDontSee('{ const matchStatus = this.statusFilter', false);
        // Ensure seeded respondent exists in HTML
        $response->assertSee('JEFRI KORWA');
        $response->assertSee('paginatedData', false);
        $response->assertSee('goToPage', false);
    }

    public function test_admin_verifikasi_page_renders_cleanly(): void
    {
        $admin = User::create([
            'name' => 'Jhoni Arafat',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/admin/verifikasi');

        $response->assertStatus(200);
        $response->assertSee('<div x-data="adminVerifikasi()" class="space-y-6">', false);
        $response->assertSee('paginatedAntrean', false);
        $response->assertSee('goToPage', false);
        $response->assertDontSee('get filteredAntrean()()', false);
    }

    public function test_operator_data_page_renders_cleanly(): void
    {
        $operator = User::create([
            'name' => 'Harpiyansa',
            'username' => 'operator',
            'password' => bcrypt('password'),
            'role' => 'operator',
        ]);

        $response = $this->actingAs($operator)->get('/operator/data');

        $response->assertStatus(200);
        $response->assertSee('<div x-data="operatorDataList()" class="space-y-6">', false);
    }

    public function test_input_data_warga_page_renders_cleanly_with_map_preview(): void
    {
        $admin = User::create([
            'name' => 'Jhoni Arafat',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/admin/data/create');

        $response->assertStatus(200);
        $response->assertSee('id="map-preview"', false);
        $response->assertSee('id="gps-accuracy-warning"', false);
        $response->assertSee('Poros Sota PLBN');
        $response->assertDontSee('Pusat Sota', false);
        $response->assertDontSee('Bebas geser peta atau klik langsung', false);
    }

    public function test_admin_edit_page_renders_cleanly_without_js_leak(): void
    {
        $admin = User::create([
            'name' => 'Jhoni Arafat',
            'username' => 'admin_edit',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $penduduk = DataPenduduk::create([
            'nama_kk' => 'Arnol Basik-Basik',
            'status_penduduk' => 'Tetap',
            'alamat' => 'Jln poros perbatasan',
            'jalur' => 'Poros Trans',
            'latitude' => -8.428645,
            'longitude' => 141.010799,
            'pendapatan' => 'Kurang dari Rp1.000.000 / bulan',
            'pekerjaan' => 'Petani',
            'pendidikan' => 'SMA / SMK Sederajat',
            'jumlah_anggota_keluarga' => 2,
            'jenis_dinding' => 'Dinding Kayu dan Tembok (Campuran)',
            'jenis_lantai' => 'Lantai Semen Plester',
            'fasilitas_sanitasi' => 'Tidak Memiliki MCK Pribadi / Menumpang',
            'sumber_air' => 'Sumur Gali Terlindung',
            'pendapatan_tambahan' => 'Tidak ada pendapatan tambahan',
            'kepemilikan_lahan' => 'Pinjam Pakai / Numpang Lahan Adat',
            'total_skor' => 21,
            'klasifikasi' => 'tinggi',
            'status_verifikasi' => 'terverifikasi',
        ]);

        $response = $this->actingAs($admin)->get('/admin/data/' . $penduduk->id . '/edit');

        $response->assertStatus(200);
        $response->assertSee('function sotaFormWarga()', false);
        $response->assertSee('<div x-data="sotaFormWarga()"', false);
        $response->assertSee('Arnol Basik-Basik');
        // Ensure no JS leakage
        $response->assertDontSee('{! (res.ok)', false);
        $response->assertDontSee('[object HTMLInputElement]', false);
    }

    public function test_peta_sebaran_renders_cleanly_with_svi_filter_and_no_score_labels(): void
    {
        $admin = User::create([
            'name' => 'Jhoni Arafat',
            'username' => 'admin_peta',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/admin/peta-sebaran');

        $response->assertStatus(200);
        $response->assertSee('sharedPetaSebaran()', false);
        $response->assertSee('#175c6b', false);
        $response->assertSee('Poros Sota PLBN');
        $response->assertSee('Warga Tetap');
        $response->assertSee('Warga Tidak Tetap');
        $response->assertDontSee('Domisili Resmi Sota', false);
        $response->assertDontSee('(Skor 1)', false);
        $response->assertDontSee('(Skor 2)', false);
        $response->assertDontSee('(Skor 3)', false);
    }

    public function test_admin_log_page_renders_cleanly_with_log_pembaruan(): void
    {
        $admin = User::create([
            'name' => 'Jhoni Arafat',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $penduduk = DataPenduduk::create([
            'nama_kk' => 'YOHANES KABUJAY',
            'alamat' => 'Jalur B Sota',
            'jalur' => 'Jalur B',
            'latitude' => -8.4285,
            'longitude' => 141.0042,
            'pendapatan' => 'Kurang dari Rp1.000.000 / bulan',
            'pekerjaan' => 'Petani',
            'pendidikan' => 'SD',
            'jenis_dinding' => 'Papan',
            'jenis_lantai' => 'Semen',
            'fasilitas_sanitasi' => 'Jamban',
            'sumber_air' => 'Sumur',
            'pendapatan_tambahan' => 'Tidak Ada',
            'kepemilikan_lahan' => 'Adat',
            'total_skor' => 22,
            'klasifikasi' => 'tinggi',
            'status_verifikasi' => 'terverifikasi',
        ]);

        \App\Models\LogPembaruan::catat(
            $penduduk->id,
            $admin->id,
            'verifikasi',
            'Pemeriksaan kesesuaian fisik bangunan dengan foto rumah.',
            ['status_verifikasi' => 'menunggu_verifikasi'],
            ['status_verifikasi' => 'terverifikasi']
        );

        $response = $this->actingAs($admin)->get('/admin/log');

        $response->assertStatus(200);
        $response->assertSee('Catatan Aktivitas &amp; Log Pembaruan', false);
        $response->assertSee('YOHANES KABUJAY');
        $response->assertSee('Jhoni Arafat');
        $response->assertSee('<div x-data="adminLogPage()" class="space-y-6">', false);
        $response->assertSee('paginatedLogs', false);
        $response->assertSee('goToPage', false);
        $response->assertDontSee('class="space-y-6">&gt;', false);
    }

    public function test_admin_can_update_resident_and_reflects_on_peta_sebaran(): void
    {
        $admin = User::create([
            'name' => 'Jhoni Arafat',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $penduduk = DataPenduduk::create([
            'nama_kk' => 'Sudarwanto',
            'alamat' => 'Jln Tugu perbatasan',
            'jalur' => 'Poros Trans',
            'latitude' => -8.427431,
            'longitude' => 141.013259,
            'pendapatan' => 'Rp1.000.000 – Rp2.499.999 / bulan',
            'pekerjaan' => 'Petani',
            'pendidikan' => 'SMA / SMK Sederajat',
            'jumlah_anggota_keluarga' => 2,
            'jenis_dinding' => 'Dinding Tembok Permanen',
            'jenis_lantai' => 'Lantai Semen Plester',
            'fasilitas_sanitasi' => 'Tidak Memiliki MCK Pribadi / Menumpang',
            'sumber_air' => 'Sumur Gali Terlindung',
            'pendapatan_tambahan' => 'Tidak ada pendapatan tambahan',
            'kepemilikan_lahan' => 'Pinjam Pakai / Numpang Lahan Adat',
            'total_skor' => 20,
            'klasifikasi' => 'sedang',
            'status_verifikasi' => 'terverifikasi',
        ]);

        $updatePayload = [
            'nama_kk' => 'Sudarwanto',
            'status_penduduk' => 'Tetap',
            'jalur' => 'Poros Sota PLBN',
            'rt' => 'RT 03',
            'rw' => 'RW 01',
            'latitude' => -8.427431,
            'longitude' => 141.013259,
            'pendapatan' => 'Rp1.000.000 – Rp2.499.999 / bulan',
            'pekerjaan' => 'Petani',
            'pendidikan' => 'SMA / SMK Sederajat',
            'jumlah_anggota_keluarga' => 2,
            'jenis_dinding' => 'Dinding Tembok Permanen',
            'jenis_lantai' => 'Lantai Semen Plester',
            'fasilitas_sanitasi' => 'Tidak Memiliki MCK Pribadi / Menumpang',
            'sumber_air' => 'Sumur Gali Terlindung',
            'pendapatan_tambahan' => 'Tidak ada pendapatan tambahan',
            'kepemilikan_lahan' => 'Pinjam Pakai / Numpang Lahan Adat',
            'total_skor' => 20,
            'klasifikasi' => 'sedang',
        ];

        $response = $this->actingAs($admin)->postJson('/admin/data/' . $penduduk->id, $updatePayload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('data_penduduk', [
            'id' => $penduduk->id,
            'jalur' => 'Poros Sota PLBN',
        ]);

        $this->assertDatabaseHas('log_pembaruan', [
            'data_penduduk_id' => $penduduk->id,
            'aksi' => 'update',
        ]);

        $petaResponse = $this->actingAs($admin)->get('/admin/peta-sebaran');
        $petaResponse->assertStatus(200);
        $petaResponse->assertSee('Poros Sota PLBN');
    }

    public function test_operator_survey_submission_with_base64_photo_saves_and_enters_queue(): void
    {
        $operator = User::create([
            'name' => 'Maria K.',
            'username' => 'operator',
            'password' => bcrypt('password'),
            'role' => 'operator',
        ]);

        $base64Image = 'data:image/jpeg;base64,' . base64_encode('fake image binary content from mobile phone camera');

        $payload = [
            'nama_kk' => 'DEMO WARGA HP',
            'status_penduduk' => 'Tetap',
            'jalur' => 'Poros Sota PLBN',
            'rt' => 'RT 01',
            'rw' => 'RW 02',
            'latitude' => -8.428,
            'longitude' => 141.003,
            'foto_rumah' => $base64Image,
            'pendapatan' => 'Rp1.000.000 – Rp2.499.999 per bulan',
            'pekerjaan' => 'Petani',
            'pendidikan' => 'Tamat SMA / Sederajat',
            'jumlah_anggota_keluarga' => 4,
            'jenis_dinding' => 'Kayu',
            'jenis_lantai' => 'Semen',
            'fasilitas_sanitasi' => 'MCK Umum / Jamban berbagi dengan tetangga',
            'sumber_air' => 'Sumur Gali',
            'pendapatan_tambahan' => 'Tidak ada pendapatan tambahan',
            'kepemilikan_lahan' => 'Milik sendiri / Warisan keluarga',
            'total_skor' => 20,
            'klasifikasi' => 'sedang',
        ];

        $response = $this->actingAs($operator)->postJson('/operator/data', $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('data_penduduk', [
            'nama_kk' => 'DEMO WARGA HP',
            'status_verifikasi' => 'menunggu_verifikasi',
            'created_by' => $operator->id,
        ]);

        $savedRecord = DataPenduduk::where('nama_kk', 'DEMO WARGA HP')->first();
        $this->assertNotNull($savedRecord);
        $this->assertStringStartsWith('/uploads/foto_rumah/', $savedRecord->foto_rumah);

        // Check operator data page includes this data
        $opIndexResponse = $this->actingAs($operator)->get('/operator/data');
        $opIndexResponse->assertStatus(200);
        $opIndexResponse->assertSee('DEMO WARGA HP');
        $opIndexResponse->assertSee('countMenunggu');

        // Check admin dashboard has it in queue
        $admin = User::create([
            'name' => 'Admin Test',
            'username' => 'admin_test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $adminDashResponse = $this->actingAs($admin)->get('/admin');
        $adminDashResponse->assertStatus(200);
        $adminDashResponse->assertSee('DEMO WARGA HP');
    }

    public function test_admin_can_verify_pending_resident_and_it_appears_on_map(): void
    {
        $admin = User::create([
            'name' => 'Admin Verifikator',
            'username' => 'admin_v',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $penduduk = DataPenduduk::create([
            'nama_kk' => 'WARGA MERAUKE TEST',
            'status_penduduk' => 'Tetap',
            'jalur' => 'Poros Sota PLBN',
            'alamat' => 'Merauke Titik Uji',
            'latitude' => -8.51621,
            'longitude' => 140.42215,
            'pendapatan' => 'Rp1.000.000 – Rp2.499.999 / bulan',
            'pekerjaan' => 'Petani',
            'pendidikan' => 'SMA / SMK Sederajat',
            'jumlah_anggota_keluarga' => 4,
            'jenis_dinding' => 'Kayu',
            'jenis_lantai' => 'Semen',
            'fasilitas_sanitasi' => 'MCK Umum',
            'sumber_air' => 'Sumur Gali',
            'pendapatan_tambahan' => 'Tidak ada',
            'kepemilikan_lahan' => 'Milik Sendiri',
            'total_skor' => 17,
            'klasifikasi' => 'sedang',
            'status_verifikasi' => 'menunggu_verifikasi',
        ]);

        // Verify action via POST /admin/data/{id}/verifikasi
        $verifyResponse = $this->actingAs($admin)->postJson('/admin/data/' . $penduduk->id . '/verifikasi');
        $verifyResponse->assertStatus(200);
        $verifyResponse->assertJson(['success' => true]);

        $this->assertDatabaseHas('data_penduduk', [
            'id' => $penduduk->id,
            'status_verifikasi' => 'terverifikasi',
        ]);

        $this->assertDatabaseHas('log_pembaruan', [
            'data_penduduk_id' => $penduduk->id,
            'aksi' => 'verifikasi',
        ]);

        // Check map page includes verified resident
        $mapResponse = $this->actingAs($admin)->get('/admin/peta-sebaran');
        $mapResponse->assertStatus(200);
        $mapResponse->assertSee('WARGA MERAUKE TEST');
    }

    public function test_admin_can_delete_resident_and_audit_log_is_recorded(): void
    {
        $admin = User::create([
            'name' => 'Admin Penghapus',
            'username' => 'admin_del',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $penduduk = DataPenduduk::create([
            'nama_kk' => 'WARGA AKAN DIHAPUS',
            'status_penduduk' => 'Tetap',
            'jalur' => 'Jalur A',
            'alamat' => 'Kampung Sota',
            'latitude' => -8.428,
            'longitude' => 141.003,
            'pendapatan' => 'Rp1.000.000 – Rp2.499.999 / bulan',
            'pekerjaan' => 'Petani',
            'pendidikan' => 'SMA / SMK Sederajat',
            'jumlah_anggota_keluarga' => 4,
            'jenis_dinding' => 'Kayu',
            'jenis_lantai' => 'Semen',
            'fasilitas_sanitasi' => 'MCK Pribadi',
            'sumber_air' => 'Sumur Gali',
            'pendapatan_tambahan' => 'Tidak ada',
            'kepemilikan_lahan' => 'Milik Sendiri',
            'total_skor' => 18,
            'klasifikasi' => 'sedang',
            'status_verifikasi' => 'terverifikasi',
        ]);

        $id = $penduduk->id;

        $deleteResponse = $this->actingAs($admin)->deleteJson('/admin/data/' . $id);
        $deleteResponse->assertStatus(200);
        $deleteResponse->assertJson(['success' => true]);

        $this->assertDatabaseMissing('data_penduduk', [
            'id' => $id,
        ]);

        $this->assertDatabaseHas('log_pembaruan', [
            'data_penduduk_id' => $id,
            'aksi' => 'delete',
        ]);
    }
}
