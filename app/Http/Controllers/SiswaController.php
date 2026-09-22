<?php

namespace App\Http\Controllers;

use App\Models\AnggotaKelas;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Siswa::with(['kelas.guru', 'user', 'anggotaKelases']);

        // Filter kelas
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_siswa', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('wali_murid', 'like', "%{$search}%");
            });
        }

        $siswa = $query->latest()->paginate(10)->withQueryString();
        $kelases = Kelas::all();

        return view('siswa.index', compact('siswa', 'kelases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kelases = Kelas::all();

        return view('siswa.create', compact('kelases'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_siswa'    => ['required', 'string', 'max:100'],
            'nis'           => 'required|numeric|digits_between:3,30|unique:siswas,nis',
            'alamat'        => 'required|string',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'wali_murid'    => ['required', 'string', 'max:100'],
            'nohp_wali'     => 'required|numeric|digits_between:8,20',
            'kelas_id'      => 'nullable|exists:kelases,id',
        ], [
            'nis.numeric'              => 'NIS siswa harus berupa angka.',
            'nis.digits_between'       => 'NIS siswa harus terdiri dari 3 hingga 30 digit angka.',
            'nis.unique'               => 'NIS ini sudah terdaftar untuk siswa lain.',
            'nohp_wali.numeric'        => 'Nomor telepon / WhatsApp wali murid harus berupa angka.',
            'nohp_wali.digits_between' => 'Nomor telepon wali murid harus terdiri dari 8 hingga 20 digit angka.',
        ]);

        $siswa = Siswa::create([
            'nama_siswa'    => $validated['nama_siswa'],
            'nis'           => $validated['nis'],
            'alamat'        => $validated['alamat'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'wali_murid'    => $validated['wali_murid'],
            'nohp_wali'     => $validated['nohp_wali'],
            'kelas_id'      => $validated['kelas_id'] ?? null,
        ]);

        // Jika kelas dipilih, daftarkan otomatis ke anggota_kelases
        if (!empty($validated['kelas_id'])) {
            $jadwalFoto = AnggotaKelas::where('kelas_id', $validated['kelas_id'])
                ->whereNotNull('jadwal')
                ->latest()
                ->value('jadwal');

            AnggotaKelas::firstOrCreate(
                [
                    'kelas_id'     => $validated['kelas_id'],
                    'siswa_id'     => $siswa->id,
                    'tahun_ajaran' => '2026/2027',
                    'semester'     => 'ganjil',
                ],
                [
                    'jadwal'       => $jadwalFoto,
                ]
            );
        }

        // Auto Create Akun User Siswa (Poin 5)
        $autoEmail = $validated['nis'] . '@siswa.learnpoint.sch.id';
        User::firstOrCreate(
            ['username' => $validated['nis']],
            [
                'name'     => $validated['nama_siswa'],
                'email'    => $autoEmail,
                'password' => Hash::make($validated['nis']),
                'role'     => 'siswa',
                'status'   => 'aktif',
                'siswa_id' => $siswa->id,
            ]
        );

        return redirect()->route('siswa.index')->with('success', 'Data siswa dan akun pengguna berhasil ditambahkan secara otomatis!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Siswa $siswa)
    {
        $siswa->load(['kelas.guru', 'user', 'anggotaKelases.kelas']);

        return view('siswa.show', compact('siswa'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Siswa $siswa)
    {
        $kelases = Kelas::all();

        return view('siswa.edit', compact('siswa', 'kelases'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'nama_siswa'    => ['required', 'string', 'max:100'],
            'nis'           => ['required', 'numeric', 'digits_between:3,30', Rule::unique('siswas')->ignore($siswa->id)],
            'alamat'        => 'required|string',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'wali_murid'    => ['required', 'string', 'max:100'],
            'nohp_wali'     => 'required|numeric|digits_between:8,20',
            'kelas_id'      => 'nullable|exists:kelases,id',
        ], [
            'nis.numeric'              => 'NIS siswa harus berupa angka.',
            'nis.digits_between'       => 'NIS siswa harus terdiri dari 3 hingga 30 digit angka.',
            'nis.unique'               => 'NIS ini sudah terdaftar untuk siswa lain.',
            'nohp_wali.numeric'        => 'Nomor telepon / WhatsApp wali murid harus berupa angka.',
            'nohp_wali.digits_between' => 'Nomor telepon wali murid harus terdiri dari 8 hingga 20 digit angka.',
        ]);

        $siswa->update($validated);

        // Sinkronisasi ke anggota_kelases
        if (!empty($validated['kelas_id'])) {
            $jadwalFoto = AnggotaKelas::where('kelas_id', $validated['kelas_id'])
                ->whereNotNull('jadwal')
                ->latest()
                ->value('jadwal');

            AnggotaKelas::updateOrCreate(
                [
                    'siswa_id'     => $siswa->id,
                    'tahun_ajaran' => '2026/2027',
                    'semester'     => 'ganjil',
                ],
                [
                    'kelas_id'     => $validated['kelas_id'],
                    'jadwal'       => $jadwalFoto,
                ]
            );
        }

        if ($siswa->user) {
            $siswa->user->update(['name' => $validated['nama_siswa']]);
        }

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Siswa $siswa)
    {
        $siswa->delete();

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil dihapus!');
    }

    /**
     * Import data siswa dari file CSV dan otomatis buat akun login siswa.
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return back()->with('error', 'Gagal membaca berkas CSV.');
        }

        $header = fgetcsv($handle, 1000, ',');
        if (!$header) {
            fclose($handle);
            return back()->with('error', 'Berkas CSV kosong.');
        }

        $header = array_map(function ($col) {
            return strtolower(trim($col, "\xEF\xBB\xBF \t\n\r\0\x0B"));
        }, $header);

        $nameIdx = array_search('nama_siswa', $header) !== false ? array_search('nama_siswa', $header) : array_search('nama', $header);
        $nisIdx = array_search('nis', $header);
        $alamatIdx = array_search('alamat', $header);
        $tglIdx = array_search('tanggal_lahir', $header);
        $jkIdx = array_search('jenis_kelamin', $header) !== false ? array_search('jenis_kelamin', $header) : array_search('jk', $header);
        $waliIdx = array_search('wali_murid', $header) !== false ? array_search('wali_murid', $header) : array_search('wali', $header);
        $nohpIdx = array_search('nohp_wali', $header) !== false ? array_search('nohp_wali', $header) : array_search('telepon', $header);
        $kelasIdx = array_search('kelas', $header) !== false ? array_search('kelas', $header) : array_search('kelas_id', $header);

        if ($nameIdx === false || $nisIdx === false) {
            fclose($handle);
            return back()->with('error', 'Format header CSV harus minimal memiliki kolom: nama_siswa, nis');
        }

        $successCount = 0;
        $skipCount = 0;
        $rowNumber = 1;

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $rowNumber++;
            $nama = trim($row[$nameIdx] ?? '');
            $rawNis = trim($row[$nisIdx] ?? '');

            if (empty($nama) || empty($rawNis)) {
                continue;
            }

            $nis = preg_replace('/[^0-9]/', '', $rawNis);
            if (empty($nis)) {
                $skipCount++;
                continue;
            }

            if (Siswa::where('nis', $nis)->exists()) {
                $skipCount++;
                continue;
            }

            $alamat = $alamatIdx !== false && !empty(trim($row[$alamatIdx] ?? '')) ? trim($row[$alamatIdx]) : 'Alamat belum diatur';
            $tgl = $tglIdx !== false && !empty(trim($row[$tglIdx] ?? '')) ? trim($row[$tglIdx]) : '2010-01-01';
            
            // Format tanggal jika d/m/Y
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $tgl)) {
                $parts = explode('/', $tgl);
                $tgl = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            }

            $rawJk = $jkIdx !== false ? strtoupper(trim($row[$jkIdx] ?? 'L')) : 'L';
            $jk = in_array($rawJk, ['L', 'LAKI-LAKI', 'PRIA']) ? 'L' : 'P';

            $wali = $waliIdx !== false && !empty(trim($row[$waliIdx] ?? '')) ? trim($row[$waliIdx]) : 'Wali Murid ' . $nama;
            $rawNohp = $nohpIdx !== false ? trim($row[$nohpIdx] ?? '') : '';
            $nohp = preg_replace('/[^0-9]/', '', $rawNohp);
            if (empty($nohp)) {
                $nohp = '081234567890';
            }

            // Cari Kelas
            $kelasId = null;
            if ($kelasIdx !== false && !empty(trim($row[$kelasIdx] ?? ''))) {
                $kelasVal = trim($row[$kelasIdx]);
                if (is_numeric($kelasVal)) {
                    $kelasId = Kelas::where('id', $kelasVal)->value('id');
                }
                if (!$kelasId) {
                    $kelasId = Kelas::where('nama_kelas', 'like', "%{$kelasVal}%")->value('id');
                }
            }

            $siswa = Siswa::create([
                'nama_siswa'    => $nama,
                'nis'           => $nis,
                'alamat'        => $alamat,
                'tanggal_lahir' => $tgl,
                'jenis_kelamin' => $jk,
                'wali_murid'    => $wali,
                'nohp_wali'     => $nohp,
                'kelas_id'      => $kelasId,
            ]);

            if ($kelasId) {
                AnggotaKelas::firstOrCreate(
                    [
                        'kelas_id'     => $kelasId,
                        'siswa_id'     => $siswa->id,
                        'tahun_ajaran' => '2026/2027',
                        'semester'     => 'ganjil',
                    ]
                );
            }

            // Auto create akun User siswa (Poin 5)
            User::firstOrCreate(
                ['username' => $nis],
                [
                    'name'     => $nama,
                    'email'    => $nis . '@siswa.learnpoint.sch.id',
                    'password' => Hash::make($nis),
                    'role'     => 'siswa',
                    'status'   => 'aktif',
                    'siswa_id' => $siswa->id,
                ]
            );

            $successCount++;
        }

        fclose($handle);

        $msg = "Import berhasil: {$successCount} data siswa dan akun pengguna berhasil ditambahkan.";
        if ($skipCount > 0) {
            $msg .= " ({$skipCount} baris dilewati karena NIS duplikat atau tidak valid).";
        }

        return redirect()->route('siswa.index')->with('success', $msg);
    }

    /**
     * Unduh contoh template CSV untuk import siswa.
     */
    public function downloadTemplateCsv()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_siswa_learnpoint.csv"',
        ];

        $columns = ['nama_siswa', 'nis', 'alamat', 'tanggal_lahir', 'jenis_kelamin', 'wali_murid', 'nohp_wali', 'kelas'];
        $sampleData = [
            ['Muhammad Rizky', '20241001', 'Jl. Merpati Putih No. 12', '2012-05-14', 'L', 'Hendra Kusuma', '081234567891', '7-A'],
            ['Siti Aisyah', '20241002', 'Jl. Kenanga Indah No. 5', '2012-08-20', 'P', 'Rahmat Hidayat', '081234567892', '7-A'],
            ['Budi Santoso', '20241003', 'Jl. Dahlia No. 44', '2012-03-10', 'L', 'Sutrisno', '081234567893', '7-B'],
        ];

        $callback = function () use ($columns, $sampleData) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($sampleData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
