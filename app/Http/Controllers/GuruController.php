<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class GuruController extends Controller
{
    /**
     * Tampilkan data seluruh guru.
     */
    public function index(Request $request)
    {
        $query = Guru::query()->with(['user', 'kelas', 'pengampuKelases.guruMapel.mapel', 'pengampuKelases.kelas']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nuptk', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan tingkatan (7, 8, 9)
        if ($request->filled('tingkatan')) {
            $tingkatan = $request->tingkatan;
            $query->where(function ($q) use ($tingkatan) {
                $q->whereHas('pengampuKelases.kelas', function ($q2) use ($tingkatan) {
                    $q2->where('tingkatan', $tingkatan);
                })->orWhereHas('kelas', function ($q2) use ($tingkatan) {
                    $q2->where('tingkatan', $tingkatan);
                });
            });
        }

        $totalGuru = Guru::count();
        $gurus = $query->latest()->paginate(10)->withQueryString();

        return view('admin.kelolaGuru.index', compact('gurus', 'totalGuru'));
    }

    /**
     * Form tambah guru baru.
     */
    public function create()
    {
        $mapels = Mapel::orderBy('nama_mapel', 'asc')->get();
        $kelases = Kelas::orderBy('tingkatan', 'asc')->orderBy('nama_kelas', 'asc')->get();

        return view('admin.kelolaGuru.create', compact('mapels', 'kelases'));
    }

    /**
     * Simpan data guru baru beserta akun dan penugasan mapel otomatis.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'                      => 'required|string|max:100|regex:/^[a-zA-Z\s\.,]+$/u',
            'nuptk'                     => 'required|numeric|digits:16|unique:gurus,nuptk',
            'jenis_kelamin'             => 'nullable|in:Laki-laki,Perempuan,L,P',
            'nomor_telepon'             => 'nullable|string|max:20|regex:/^[0-9+\-\s]+$/',
            'assignments'               => 'nullable|array',
            'assignments.*.mapel_id'    => 'nullable|exists:mapels,id',
            'assignments.*.kelas_id'    => 'nullable|exists:kelases,id',
            'assignments.*.kelas_ids'   => 'nullable|array',
            'assignments.*.kelas_ids.*' => 'exists:kelases,id',
        ], [
            'nama.regex'          => 'Nama hanya boleh berisi huruf, spasi, koma (,), dan titik (.).',
            'nuptk.numeric'       => 'NUPTK guru harus berupa angka.',
            'nuptk.digits'        => 'NUPTK guru harus tepat 16 digit angka.',
            'nuptk.unique'        => 'NUPTK ini sudah terdaftar untuk guru lain.',
            'nomor_telepon.regex' => 'Nomor telepon hanya boleh berisi angka, tanda plus (+), minus (-), atau spasi.',
        ]);

        $guru = Guru::create([
            'nama'          => $validated['nama'],
            'nuptk'         => $validated['nuptk'],
            'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
            'nomor_telepon' => $validated['nomor_telepon'] ?? null,
            'status'        => 'aktif',
        ]);

        // Auto Create Akun User Guru (Poin 5)
        $autoEmail = $validated['nuptk'] . '@guru.learnpoint.sch.id';
        User::firstOrCreate(
            ['username' => $validated['nuptk']],
            [
                'name'     => $validated['nama'],
                'email'    => $autoEmail,
                'password' => Hash::make($validated['nuptk']),
                'role'     => 'guru',
                'status'   => 'aktif',
                'guru_id'  => $guru->id,
            ]
        );

        // Tambah penugasan mapel yang diampu sekaligus jika ada
        if (!empty($validated['assignments'])) {
            foreach ($validated['assignments'] as $assign) {
                if (!empty($assign['mapel_id'])) {
                    $kelasIds = $assign['kelas_ids'] ?? [];
                    if (!empty($assign['kelas_id']) && !in_array($assign['kelas_id'], $kelasIds)) {
                        $kelasIds[] = $assign['kelas_id'];
                    }

                    if (!empty($kelasIds)) {
                        $gm = \App\Models\GuruMapel::firstOrCreate([
                            'guru_id'  => $guru->id,
                            'mapel_id' => $assign['mapel_id'],
                        ]);
                        foreach ($kelasIds as $kId) {
                            \App\Models\PengampuKelas::firstOrCreate([
                                'guru_mapel_id' => $gm->id,
                                'kelas_id'      => $kId,
                            ]);
                        }
                    }
                }
            }
        }

        return redirect()->route('guru.index')->with('success', 'Data guru dan akun pengguna berhasil ditambahkan secara otomatis!');
    }

    /**
     * Detail profil guru dan pengampu mapel.
     */
    public function show(Guru $guru)
    {
        $guru->load([
            'user', 
            'kelas.siswas', 
            'guruMapels.mapel', 
            'guruMapels.pengampuKelases.kelas', 
            'pengampuKelases.guruMapel.mapel', 
            'pengampuKelases.kelas'
        ]);

        $allMapels = Mapel::orderBy('nama_mapel', 'asc')->get();
        $allKelases = Kelas::orderBy('tingkatan', 'asc')->orderBy('nama_kelas', 'asc')->get();

        return view('admin.kelolaGuru.show', compact('guru', 'allMapels', 'allKelases'));
    }

    /**
     * Form edit guru beserta penugasan mapelnya.
     */
    public function edit(Guru $guru)
    {
        $guru->load(['user', 'pengampuKelases.guruMapel.mapel', 'pengampuKelases.kelas']);
        $mapels = Mapel::orderBy('nama_mapel', 'asc')->get();
        $kelases = Kelas::orderBy('tingkatan', 'asc')->orderBy('nama_kelas', 'asc')->get();

        return view('admin.kelolaGuru.edit', compact('guru', 'mapels', 'kelases'));
    }

    /**
     * Update data guru dan penugasan mapel.
     */
    public function update(Request $request, Guru $guru)
    {
        $validated = $request->validate([
            'nama'            => 'required|string|max:100|regex:/^[a-zA-Z\s\.,]+$/u',
            'nuptk'           => ['required', 'numeric', 'digits:16', Rule::unique('gurus')->ignore($guru->id)],
            'jenis_kelamin'   => 'nullable|in:Laki-laki,Perempuan,L,P',
            'nomor_telepon'   => 'nullable|string|max:20|regex:/^[0-9+\-\s]+$/',
            'new_mapel_id'    => 'nullable|exists:mapels,id',
            'new_kelas_ids'   => 'nullable|array',
            'new_kelas_ids.*' => 'exists:kelases,id',
            'new_kelas_id'    => 'nullable|exists:kelases,id',
        ], [
            'nama.regex'          => 'Nama hanya boleh berisi huruf, spasi, koma (,), dan titik (.).',
            'nuptk.numeric'       => 'NUPTK guru harus berupa angka.',
            'nuptk.digits'        => 'NUPTK guru harus tepat 16 digit angka.',
            'nuptk.unique'        => 'NUPTK ini sudah terdaftar untuk guru lain.',
            'nomor_telepon.regex' => 'Nomor telepon hanya boleh berisi angka, tanda plus (+), minus (-), atau spasi.',
        ]);

        $updateGuru = [
            'nama'  => $validated['nama'],
            'nuptk' => $validated['nuptk'],
        ];
        if ($request->has('jenis_kelamin')) {
            $updateGuru['jenis_kelamin'] = $validated['jenis_kelamin'] ?? null;
        }
        if ($request->has('nomor_telepon')) {
            $updateGuru['nomor_telepon'] = $validated['nomor_telepon'] ?? null;
        }
        $guru->update($updateGuru);

        // Update user name jika terhubung
        if ($guru->user) {
            $guru->user->update(['name' => $validated['nama']]);
        }

        // Tambah penugasan baru jika dipilih
        $newKelasIds = $request->input('new_kelas_ids', []);
        if ($request->filled('new_kelas_id') && !in_array($request->new_kelas_id, $newKelasIds)) {
            $newKelasIds[] = $request->new_kelas_id;
        }

        if (!empty($validated['new_mapel_id']) && !empty($newKelasIds)) {
            $gm = \App\Models\GuruMapel::firstOrCreate([
                'guru_id'  => $guru->id,
                'mapel_id' => $validated['new_mapel_id'],
            ]);
            foreach ($newKelasIds as $kId) {
                \App\Models\PengampuKelas::firstOrCreate([
                    'guru_mapel_id' => $gm->id,
                    'kelas_id'      => $kId,
                ]);
            }
        }

        return redirect()->route('guru.edit', $guru->id)->with('success', 'Data guru dan penugasan mengajar berhasil diperbarui!');
    }

    /**
     * Ubah status guru menjadi aktif atau nonaktif (dan otomatis sinkronkan status akun user terikat).
     */
    public function toggleStatus(Guru $guru)
    {
        $guru->status = ($guru->status === 'aktif') ? 'nonaktif' : 'aktif';
        $guru->save();

        if ($guru->user) {
            $guru->user->update(['status' => $guru->status]);
        }

        $statusText = $guru->status === 'aktif' ? 'diaktifkan kembali' : 'dinonaktifkan';
        return redirect()->route('guru.index')->with('success', "Data guru {$guru->nama} dan akun terikat berhasil {$statusText}!");
    }

    /**
     * Menonaktifkan data guru (menggantikan fungsi hapus permanen).
     */
    public function destroy(Guru $guru)
    {
        return $this->toggleStatus($guru);
    }

    /**
     * Import data guru via file CSV dan otomatis buat akun login.
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return back()->with('error', 'Gagal membaca file CSV.');
        }

        $header = fgetcsv($handle, 1000, ',');
        if (!$header) {
            fclose($handle);
            return back()->with('error', 'File CSV kosong.');
        }

        $header = array_map(function ($col) {
            return strtolower(trim($col, "\xEF\xBB\xBF \t\n\r\0\x0B"));
        }, $header);

        $nameIdx = array_search('nama', $header) !== false ? array_search('nama', $header) : array_search('name', $header);
        $nuptkIdx = array_search('nuptk', $header) !== false ? array_search('nuptk', $header) : array_search('nip', $header);

        if ($nameIdx === false || $nuptkIdx === false) {
            fclose($handle);
            return back()->with('error', 'Format header CSV harus memuat: nama, nuptk');
        }

        $validationErrors = [];
        $rowsToImport = [];
        $seenNuptk = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $rowNumber++;

            // Skip baris kosong
            if (empty(array_filter($row))) {
                continue;
            }

            $nama = trim($row[$nameIdx] ?? '');
            $rawNuptk = trim($row[$nuptkIdx] ?? '');
            $rowErrors = [];

            if (empty($nama)) {
                $rowErrors[] = 'Nama guru wajib diisi.';
            }

            if (empty($rawNuptk)) {
                $rowErrors[] = 'NUPTK wajib diisi.';
            } else {
                $nuptk = preg_replace('/[^0-9]/', '', $rawNuptk);
                if (empty($nuptk)) {
                    $rowErrors[] = "NUPTK '{$rawNuptk}' tidak valid (harus berupa angka).";
                } elseif (strlen($nuptk) !== 16) {
                    $rowErrors[] = "NUPTK '{$nuptk}' harus tepat 16 digit angka.";
                } elseif (in_array($nuptk, $seenNuptk)) {
                    $rowErrors[] = "NUPTK '{$nuptk}' duplikat dalam file CSV ini.";
                } elseif (Guru::where('nuptk', $nuptk)->exists()) {
                    $rowErrors[] = "NUPTK '{$nuptk}' sudah terdaftar dalam sistem.";
                } else {
                    $seenNuptk[] = $nuptk;
                }
            }

            if (!empty($rowErrors)) {
                $validationErrors[] = "Baris {$rowNumber}: " . implode(' ', $rowErrors);
            } else {
                $rowsToImport[] = [
                    'nama'  => $nama,
                    'nuptk' => $nuptk,
                ];
            }
        }

        fclose($handle);

        if (!empty($validationErrors)) {
            return back()
                ->with('csv_errors', $validationErrors)
                ->with('error', 'Import CSV dibatalkan karena terdapat ' . count($validationErrors) . ' kesalahan data.');
        }

        if (empty($rowsToImport)) {
            return back()->with('error', 'Tidak ada data valid yang dapat di-import.');
        }

        // Eksekusi penyimpan data dalam transaksi
        DB::transaction(function () use ($rowsToImport) {
            foreach ($rowsToImport as $data) {
                $guru = Guru::create([
                    'nama'  => $data['nama'],
                    'nuptk' => $data['nuptk'],
                ]);

                User::firstOrCreate(
                    ['username' => $data['nuptk']],
                    [
                        'name'     => $data['nama'],
                        'email'    => $data['nuptk'] . '@guru.learnpoint.sch.id',
                        'password' => Hash::make($data['nuptk']),
                        'role'     => 'guru',
                        'status'   => 'aktif',
                        'guru_id'  => $guru->id,
                    ]
                );
            }
        });

        $count = count($rowsToImport);
        return redirect()->route('guru.index')->with('success', "Import berhasil: {$count} data guru dan akun pengguna berhasil ditambahkan.");
    }

    /**
     * Unduh contoh template CSV untuk import guru.
     */
    public function downloadTemplateCsv()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_guru_learnpoint.csv"',
        ];

        $columns = ['nama', 'nuptk'];
        $sampleData = [
            ['Dra. Siti Nurhaliza, M.Pd.', '1234567890123456'],
            ['Ahmad Fauzi, S.Pd.', '2345678901234567'],
            ['Bambang Pamungkas, M.Kom.', '3456789012345678'],
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
