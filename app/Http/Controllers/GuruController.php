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

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
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
            'nama'                      => 'required|string|max:100',
            'nip'                       => 'required|numeric|digits:18|unique:gurus,nip',
            'assignments'               => 'nullable|array',
            'assignments.*.mapel_id'    => 'nullable|exists:mapels,id',
            'assignments.*.kelas_id'    => 'nullable|exists:kelases,id',
            'assignments.*.kelas_ids'   => 'nullable|array',
            'assignments.*.kelas_ids.*' => 'exists:kelases,id',
        ], [
            'nip.numeric' => 'NIP guru harus berupa angka.',
            'nip.digits'  => 'NIP guru harus tepat 18 digit angka.',
            'nip.unique'  => 'NIP ini sudah terdaftar untuk guru lain.',
        ]);

        $guru = Guru::create([
            'nama'   => $validated['nama'],
            'nip'    => $validated['nip'],
            'status' => 'aktif',
        ]);

        // Auto Create Akun User Guru (Poin 5)
        $autoEmail = $validated['nip'] . '@guru.learnpoint.sch.id';
        User::firstOrCreate(
            ['username' => $validated['nip']],
            [
                'name'     => $validated['nama'],
                'email'    => $autoEmail,
                'password' => Hash::make($validated['nip']),
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
        $guru->load(['user', 'kelas.siswas', 'pengampuKelases.guruMapel.mapel', 'pengampuKelases.kelas']);

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
            'nama'            => 'required|string|max:100',
            'nip'             => ['required', 'numeric', 'digits:18', Rule::unique('gurus')->ignore($guru->id)],
            'new_mapel_id'    => 'nullable|exists:mapels,id',
            'new_kelas_ids'   => 'nullable|array',
            'new_kelas_ids.*' => 'exists:kelases,id',
            'new_kelas_id'    => 'nullable|exists:kelases,id',
        ], [
            'nip.numeric' => 'NIP guru harus berupa angka.',
            'nip.digits'  => 'NIP guru harus tepat 18 digit angka.',
            'nip.unique'  => 'NIP ini sudah terdaftar untuk guru lain.',
        ]);

        $guru->update([
            'nama' => $validated['nama'],
            'nip'  => $validated['nip'],
        ]);

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
        $nipIdx = array_search('nip', $header);

        if ($nameIdx === false || $nipIdx === false) {
            fclose($handle);
            return back()->with('error', 'Format header CSV harus memuat: nama, nip');
        }

        $validationErrors = [];
        $rowsToImport = [];
        $seenNip = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $rowNumber++;

            // Skip baris kosong
            if (empty(array_filter($row))) {
                continue;
            }

            $nama = trim($row[$nameIdx] ?? '');
            $rawNip = trim($row[$nipIdx] ?? '');
            $rowErrors = [];

            if (empty($nama)) {
                $rowErrors[] = 'Nama guru wajib diisi.';
            }

            if (empty($rawNip)) {
                $rowErrors[] = 'NIP wajib diisi.';
            } else {
                $nip = preg_replace('/[^0-9]/', '', $rawNip);
                if (empty($nip)) {
                    $rowErrors[] = "NIP '{$rawNip}' tidak valid (harus berupa angka).";
                } elseif (strlen($nip) !== 18) {
                    $rowErrors[] = "NIP '{$nip}' harus tepat 18 digit angka.";
                } elseif (in_array($nip, $seenNip)) {
                    $rowErrors[] = "NIP '{$nip}' duplikat dalam file CSV ini.";
                } elseif (Guru::where('nip', $nip)->exists()) {
                    $rowErrors[] = "NIP '{$nip}' sudah terdaftar dalam sistem.";
                } else {
                    $seenNip[] = $nip;
                }
            }

            if (!empty($rowErrors)) {
                $validationErrors[] = "Baris {$rowNumber}: " . implode(' ', $rowErrors);
            } else {
                $rowsToImport[] = [
                    'nama' => $nama,
                    'nip'  => $nip,
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
                    'nama' => $data['nama'],
                    'nip'  => $data['nip'],
                ]);

                User::firstOrCreate(
                    ['username' => $data['nip']],
                    [
                        'name'     => $data['nama'],
                        'email'    => $data['nip'] . '@guru.learnpoint.sch.id',
                        'password' => Hash::make($data['nip']),
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

        $columns = ['nama', 'nip'];
        $sampleData = [
            ['Dra. Siti Nurhaliza, M.Pd.', '198501012010012001'],
            ['Ahmad Fauzi, S.Pd.', '198803152014021002'],
            ['Bambang Pamungkas, M.Kom.', '199005202018031003'],
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
