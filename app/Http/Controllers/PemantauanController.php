<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bahan;
use App\Models\BahanKeluar;
use App\Models\MenuTerjual;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class PemantauanController extends Controller
{
    public function index(Request $request)
    {
        [$laporan, $ringkasan] = $this->hitungLaporan($request);

        return view('laporan.pemantauan', [
            'laporan' => $laporan,
            'ringkasan' => $ringkasan,
        ]);
    }

    public function downloadPDF(Request $request)
    {
        [$laporan, $ringkasan] = $this->hitungLaporan($request);

        if ($laporan->isEmpty()) {
            return redirect()->route('laporan.pemantauan', $request->only(['dari_tanggal', 'sampai_tanggal']))
                ->with('error', 'Tidak ada data untuk diunduh pada rentang tanggal tersebut.');
        }

        $pdf = Pdf::loadView('laporan.pemantauan_pdf', [
            'laporan' => $laporan,
            'ringkasan' => $ringkasan,
            'dariTanggal' => $request->input('dari_tanggal'),
            'sampaiTanggal' => $request->input('sampai_tanggal'),
        ]);

        return $pdf->download(
            'Laporan_Pemantauan_Bahan_' . $request->input('dari_tanggal') . '_sd_' . $request->input('sampai_tanggal') . '.pdf'
        );
    }

    /**
     * Membandingkan pemakaian bahan yang SEHARUSNYA (resep menu x jumlah terjual)
     * dengan pemakaian AKTUAL (tabel bahan_keluar) pada rentang tanggal tertentu.
     *
     * Sebelumnya logic ini disalin identik di index() dan downloadPDF(); disatukan
     * agar keduanya dijamin menghasilkan angka yang sama.
     *
     * @return array{0: Collection, 1: array}
     */
    private function hitungLaporan(Request $request): array
    {
        $dari = $request->input('dari_tanggal');
        $sampai = $request->input('sampai_tanggal');

        if (!$dari || !$sampai) {
            return [collect(), $this->ringkasanKosong()];
        }

        // Bandingkan sebagai DATE murni. Memakai '... 00:00:00' membuat baris hari pertama
        // ikut terbuang, karena kolom tanggal_keluar bertipe DATE dan perbandingan string
        // '2026-07-27' < '2026-07-27 00:00:00'.
        $dariTanggal = date('Y-m-d', strtotime($dari));
        $sampaiTanggal = date('Y-m-d', strtotime($sampai));

        // Pemakaian seharusnya, dari menu yang terjual pada rentang tanggal ini.
        $menuTerjual = MenuTerjual::with(['menu.bahans', 'menu.bahanProcesses'])
            ->whereBetween('tanggal', [$dari, $sampai])
            ->get();

        $bahanData = [];

        foreach ($menuTerjual as $item) {
            if (!$item->menu) {
                continue;
            }

            foreach ($item->menu->bahans as $bahan) {
                $this->akumulasi($bahanData, $bahan, $item->jumlah_terjual, 'Non-Proses');
            }

            foreach ($item->menu->bahanProcesses as $bahanProcess) {
                $this->akumulasi($bahanData, $bahanProcess, $item->jumlah_terjual, 'Proses');
            }
        }

        if (empty($bahanData)) {
            return [collect(), $this->ringkasanKosong()];
        }

        // Ambil total keluar aktual sekali jalan (hindari query di dalam loop).
        $totalKeluar = BahanKeluar::whereIn('kode_bahan', array_keys($bahanData))
            ->whereDate('tanggal_keluar', '>=', $dariTanggal)
            ->whereDate('tanggal_keluar', '<=', $sampaiTanggal)
            ->selectRaw('kode_bahan, SUM(jumlah_keluar) as total')
            ->groupBy('kode_bahan')
            ->pluck('total', 'kode_bahan');

        $totalMasuk = Bahan::whereIn('kode_bahan', array_keys($bahanData))
            ->pluck('sisa_stok', 'kode_bahan');

        foreach ($bahanData as $kode => &$row) {
            $row['total_keluar'] = (float) ($totalKeluar[$kode] ?? 0);
            $row['sisa_stok'] = (float) ($totalMasuk[$kode] ?? 0);

            $selisih = $row['total_keluar'] - $row['pemakaian_seharusnya'];
            $row['selisih'] = $selisih;

            if (abs($selisih) < 0.0001) {
                $row['status'] = 'Balance';
                $row['status_pemantauan'] = 'Balance';
            } elseif ($selisih > 0) {
                $row['status'] = 'Waste';
                $row['status_pemantauan'] = "Waste ({$selisih} {$row['satuan']})";
            } else {
                $row['status'] = 'Plus';
                $row['status_pemantauan'] = 'Plus (+' . abs($selisih) . " {$row['satuan']})";
            }

            // Rasio aktual terhadap seharusnya, dibatasi 150% agar bar tidak meledak.
            $row['rasio'] = $row['pemakaian_seharusnya'] > 0
                ? min(150, round($row['total_keluar'] / $row['pemakaian_seharusnya'] * 100))
                : ($row['total_keluar'] > 0 ? 150 : 0);

            // Kompatibilitas dengan view PDF lama.
            $row['total_masuk'] = $row['sisa_stok'];
        }
        unset($row);

        // Keterangan manual dari form (dipakai saat unduh PDF). Dikunci berdasarkan
        // kode_bahan, bukan indeks numerik, supaya tetap cocok walau urutan baris berubah.
        $keterangan = (array) $request->input('keterangan', []);
        foreach ($bahanData as $kode => &$baris) {
            $baris['keterangan'] = $keterangan[$kode] ?? '';
        }
        unset($baris);

        $laporan = collect($bahanData);

        // Filter tampilan (tidak memengaruhi perhitungan).
        if ($request->filled('status')) {
            $laporan = $laporan->where('status', $request->status);
        }

        if ($request->filled('kategori_bahan')) {
            $prefix = $request->kategori_bahan;
            $laporan = $laporan->filter(fn ($r) => str_starts_with($r['kode_bahan'], $prefix));
        }

        if ($request->filled('search')) {
            $cari = strtolower($request->search);
            $laporan = $laporan->filter(fn ($r) => str_contains(strtolower($r['nama_bahan']), $cari));
        }

        $laporan = $laporan->sortByDesc(fn ($r) => abs($r['selisih']))->values();

        return [$laporan, $this->ringkasan($laporan)];
    }

    private function akumulasi(array &$bahanData, $bahan, int $jumlahTerjual, string $tipe): void
    {
        $kode = $bahan->kode_bahan;
        $gramasi = $bahan->pivot->gramasi ?? 0;

        if (!isset($bahanData[$kode])) {
            $bahanData[$kode] = [
                'kode_bahan' => $kode,
                'nama_bahan' => $bahan->nama_bahan,
                'tipe' => $tipe,
                'satuan' => $bahan->satuan ?? 'g',
                'pemakaian_seharusnya' => 0,
                'total_keluar' => 0,
                'sisa_stok' => 0,
                'selisih' => 0,
                'status' => 'Balance',
                'status_pemantauan' => '',
                'rasio' => 0,
                'keterangan' => '',
            ];
        }

        $bahanData[$kode]['pemakaian_seharusnya'] += $jumlahTerjual * $gramasi;
    }

    private function ringkasan(Collection $laporan): array
    {
        return [
            'total' => $laporan->count(),
            'balance' => $laporan->where('status', 'Balance')->count(),
            'waste' => $laporan->where('status', 'Waste')->count(),
            'plus' => $laporan->where('status', 'Plus')->count(),
            'total_seharusnya' => $laporan->sum('pemakaian_seharusnya'),
            'total_aktual' => $laporan->sum('total_keluar'),
        ];
    }

    private function ringkasanKosong(): array
    {
        return [
            'total' => 0, 'balance' => 0, 'waste' => 0, 'plus' => 0,
            'total_seharusnya' => 0, 'total_aktual' => 0,
        ];
    }
}
