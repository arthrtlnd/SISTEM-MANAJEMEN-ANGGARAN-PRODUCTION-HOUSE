<?php

namespace Database\Seeders;

// ← PENTING: import semua model yang dipakai
use App\Models\BudgetNotification;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── USERS ──────────────────────────────────────────────
        $admin = User::create([
            'name'     => 'Admin PH',
            'email'    => 'admin@ph.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        $dewi = User::create([
            'name'     => 'Dewi Puspita',
            'email'    => 'dewi@ph.com',
            'password' => Hash::make('password'),
            'role'     => 'produser',
        ]);

        $reza = User::create([
            'name'     => 'Reza Firmansyah',
            'email'    => 'reza@ph.com',
            'password' => Hash::make('password'),
            'role'     => 'crew',
        ]);

        // ── CLIENTS ────────────────────────────────────────────
        $clientA = Client::create([
            'nama_klien'    => 'PT Segar Nusantara',
            'industri'      => 'FMCG',
            'kontak_person' => 'Budi Santoso',
            'email'         => 'budi@segar.co.id',
            'telepon'       => '0811-1234-5678',
            'tipe'          => 'retainer',
        ]);

        $clientB = Client::create([
            'nama_klien'    => 'PT Cantik Alami',
            'industri'      => 'Beauty & Skincare',
            'kontak_person' => 'Rina Hartati',
            'email'         => 'rina@cantikalami.id',
            'telepon'       => '0812-9876-5432',
            'tipe'          => 'per_project',
        ]);

        $clientC = Client::create([
            'nama_klien'    => 'Bank Maju Sejahtera',
            'industri'      => 'Perbankan',
            'kontak_person' => 'Hendra Wijaya',
            'email'         => 'hendra@bankmaju.co.id',
            'telepon'       => '0813-5555-7777',
            'tipe'          => 'per_project',
        ]);

        // ── PROJECTS ───────────────────────────────────────────
        $proj1 = Project::create([
            'kode_project'     => 'PRJ-2026-041',
            'nama_project'     => 'TVC Minuman X',
            'client_id'        => $clientA->id,
            'pic_id'           => $dewi->id,
            'status'           => 'production',
            'tipe_iklan'       => 'TVC',
            'tanggal_mulai'    => '2026-04-01',
            'tanggal_deadline' => '2026-04-30',
            'budget_total'     => 400000000,
            'deskripsi'        => 'Iklan TVC 30 detik untuk produk minuman baru.',
        ]);

        $proj2 = Project::create([
            'kode_project'     => 'PRJ-2026-042',
            'nama_project'     => 'Campaign Skincare Y',
            'client_id'        => $clientB->id,
            'pic_id'           => $reza->id,
            'status'           => 'pre_prod',
            'tipe_iklan'       => 'digital',
            'tanggal_mulai'    => '2026-04-05',
            'tanggal_deadline' => '2026-05-10',
            'budget_total'     => 250000000,
            'deskripsi'        => 'Campaign digital untuk lini produk skincare terbaru.',
        ]);

        $proj3 = Project::create([
            'kode_project'     => 'PRJ-2026-043',
            'nama_project'     => 'Billboard Bank Z',
            'client_id'        => $clientC->id,
            'pic_id'           => $dewi->id,
            'status'           => 'post_prod',
            'tipe_iklan'       => 'OOH',
            'tanggal_mulai'    => '2026-03-15',
            'tanggal_deadline' => '2026-04-20',
            'budget_total'     => 150000000,
            'deskripsi'        => 'Materi billboard OOH untuk kampanye akhir kuartal.',
        ]);

        // ── EXPENSES ───────────────────────────────────────────
        // Project 1
        Expense::create(['project_id'=>$proj1->id,'submitted_by'=>$dewi->id,'approved_by'=>$admin->id,'nama_pengeluaran'=>'Sewa Studio A 2 hari','kategori'=>'sewa_lokasi','jumlah'=>15000000,'status'=>'approved','tanggal_pengeluaran'=>'2026-04-16','approved_at'=>now()]);
        Expense::create(['project_id'=>$proj1->id,'submitted_by'=>$reza->id,'approved_by'=>$admin->id,'nama_pengeluaran'=>'Sewa kamera & lighting kit','kategori'=>'peralatan','jumlah'=>8000000,'status'=>'approved','tanggal_pengeluaran'=>'2026-04-10','approved_at'=>now()]);
        Expense::create(['project_id'=>$proj1->id,'submitted_by'=>$dewi->id,'approved_by'=>$admin->id,'nama_pengeluaran'=>'Honorarium sutradara','kategori'=>'honorarium','jumlah'=>25000000,'status'=>'approved','tanggal_pengeluaran'=>'2026-04-08','approved_at'=>now()]);
        Expense::create(['project_id'=>$proj1->id,'submitted_by'=>$dewi->id,'approved_by'=>$admin->id,'nama_pengeluaran'=>'Katering tim syuting 2 hari','kategori'=>'katering','jumlah'=>4500000,'status'=>'approved','tanggal_pengeluaran'=>'2026-04-16','approved_at'=>now()]);
        Expense::create(['project_id'=>$proj1->id,'submitted_by'=>$reza->id,'nama_pengeluaran'=>'Makeup artist tambahan','kategori'=>'honorarium','jumlah'=>5000000,'status'=>'pending','tanggal_pengeluaran'=>'2026-04-17']);

        // Project 2
        Expense::create(['project_id'=>$proj2->id,'submitted_by'=>$reza->id,'approved_by'=>$admin->id,'nama_pengeluaran'=>'Honorarium talent utama','kategori'=>'honorarium','jumlah'=>22000000,'status'=>'approved','tanggal_pengeluaran'=>'2026-04-14','approved_at'=>now()]);
        Expense::create(['project_id'=>$proj2->id,'submitted_by'=>$dewi->id,'approved_by'=>$admin->id,'nama_pengeluaran'=>'Sewa studio foto setengah hari','kategori'=>'sewa_lokasi','jumlah'=>6000000,'status'=>'approved','tanggal_pengeluaran'=>'2026-04-09','approved_at'=>now()]);
        Expense::create(['project_id'=>$proj2->id,'submitted_by'=>$reza->id,'nama_pengeluaran'=>'Transportasi & akomodasi','kategori'=>'transportasi','jumlah'=>3000000,'status'=>'pending','tanggal_pengeluaran'=>'2026-04-08']);

        // Project 3 — sengaja over budget
        Expense::create(['project_id'=>$proj3->id,'submitted_by'=>$dewi->id,'approved_by'=>$admin->id,'nama_pengeluaran'=>'Cetak materi billboard','kategori'=>'peralatan','jumlah'=>80000000,'status'=>'approved','tanggal_pengeluaran'=>'2026-03-20','approved_at'=>now()]);
        Expense::create(['project_id'=>$proj3->id,'submitted_by'=>$dewi->id,'approved_by'=>$admin->id,'nama_pengeluaran'=>'Jasa pasang billboard 3 titik','kategori'=>'sewa_lokasi','jumlah'=>45000000,'status'=>'approved','tanggal_pengeluaran'=>'2026-03-25','approved_at'=>now()]);
        Expense::create(['project_id'=>$proj3->id,'submitted_by'=>$reza->id,'approved_by'=>$admin->id,'nama_pengeluaran'=>'Biaya tambahan perizinan lokasi','kategori'=>'lain_lain','jumlah'=>18000000,'status'=>'approved','tanggal_pengeluaran'=>'2026-04-12','approved_at'=>now()]);
        Expense::create(['project_id'=>$proj3->id,'submitted_by'=>$dewi->id,'approved_by'=>$admin->id,'nama_pengeluaran'=>'Transportasi tim survey lokasi','kategori'=>'transportasi','jumlah'=>7000000,'status'=>'approved','tanggal_pengeluaran'=>'2026-03-18','approved_at'=>now()]);

        // ── INVOICES ───────────────────────────────────────────
        Invoice::create(['nomor_invoice'=>'INV-2026-031','project_id'=>$proj1->id,'nama_vendor'=>'Studio A Jakarta','jumlah'=>15000000,'tanggal_invoice'=>'2026-04-16','jatuh_tempo'=>'2026-04-20','status'=>'belum_bayar']);
        Invoice::create(['nomor_invoice'=>'INV-2026-028','project_id'=>$proj2->id,'nama_vendor'=>'Talent Agency Nusantara','jumlah'=>22000000,'tanggal_invoice'=>'2026-04-01','jatuh_tempo'=>'2026-04-10','status'=>'lunas','tanggal_bayar'=>'2026-04-10']);
        Invoice::create(['nomor_invoice'=>'INV-2026-025','project_id'=>$proj1->id,'nama_vendor'=>'Rental Kamera XYZ','jumlah'=>8000000,'tanggal_invoice'=>'2026-04-10','jatuh_tempo'=>'2026-04-25','status'=>'belum_bayar']);

        // ── NOTIFIKASI ─────────────────────────────────────────
        BudgetNotification::create(['project_id'=>$proj3->id,'tipe'=>'over_budget','pesan'=>'Billboard Bank Z melebihi budget Rp 15.000.000 (pemakaian 110%). Segera koordinasi dengan klien.']);
        BudgetNotification::create(['project_id'=>$proj1->id,'tipe'=>'warning_80','pesan'=>'TVC Minuman X telah mencapai 80% anggaran. Sisa Rp 80.000.000 untuk sisa produksi.']);
        BudgetNotification::create(['project_id'=>$proj1->id,'tipe'=>'invoice_jatuh_tempo','pesan'=>'Invoice INV-2026-031 dari Studio A Jakarta jatuh tempo 20 April 2026.']);
    }
}
