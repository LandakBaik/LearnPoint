<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\DateHelper;
use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\AnggotaKelas;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // Filter kelas pada periode aktif
        $activePeriod = AcademicPeriod::current();
        if ($request->filled('kelas_id')) {
            $query->whereHas('anggotaKelases', function ($q) use ($request, $activePeriod) {
                $q->where('kelas_id', $request->kelas_id)
                  ->where('academic_period_id', $activePeriod->id);
            });
        }

        // Filter tingkatan (7, 8, 9)
        if ($request->filled('tingkatan')) {
            $query->whereHas('kelas', function ($q) use ($request) {
                $q->where('tingkatan', $request->tingkatan);
            });
        }

        // Filter angkatan
        if ($request->filled('angkatan')) {
            $query->where('angkatan', (int) $request->angkatan);
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter agama
        if ($request->filled('agama')) {
            $query->where('agama', $request->agama);
        }

        // Filter jenis kelamin (dari clickable stat card)
        if ($request->filled('jenis_kelamin')) {
            if ($request->jenis_kelamin === 'L') {
                $query->whereIn('jenis_kelamin', ['L', 'Laki-laki']);
            } elseif ($request->jenis_kelamin === 'P') {
                $query->whereIn('jenis_kelamin', ['P', 'Perempuan']);
            }
        }

        // Filter siswa yang sudah punya kelas (dari clickable stat card)
        if ($request->filled('has_class') && $request->has_class == '1') {
            $query->has('kelas');
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

        $totalSiswa = Siswa::count();
        $countLakiLaki = Siswa::whereIn('jenis_kelamin', ['L', 'Laki-laki'])->count();
        $countPerempuan = Siswa::whereIn('jenis_kelamin', ['P', 'Perempuan'])->count();
        $countHasClass = Siswa::has('kelas')->count();

        $siswa = $query->latest()->paginate(10)->withQueryString();
        $kelases = Kelas::all();
        $angkatanList = Siswa::whereNotNull('angkatan')->distinct()->orderByDesc('angkatan')->pluck('angkatan');

        return view('admin.kelola_siswa.index', compact('siswa', 'kelases', 'totalSiswa', 'countLakiLaki', 'countPerempuan', 'countHasClass', 'angkatanList', 'activePeriod'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kelases = Kelas::all();

        return view('admin.kelola_siswa.create', compact('kelases'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $minAngkatan = Siswa::MIN_ANGKATAN;
        $maxAngkatan = Siswa::maxAngkatan();
        $effectiveAngkatan = $request->filled('angkatan') && is_numeric($request->input('angkatan'))
            ? (int) $request->input('angkatan')
            : AcademicPeriod::defaultAngkatan();
        $minBirthYear = $effectiveAngkatan - Siswa::MAX_ENTRY_AGE;
        $maxBirthYear = $effectiveAngkatan - Siswa::MIN_ENTRY_AGE;

        $validated = $request->validate([
            'nama_siswa'    => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\.,]+$/u'],
            'nis'           => 'required|numeric|digits_between:4,10|unique:siswas,nis',
            'angkatan'      => ['nullable', 'date_format:Y', 'integer', "min:{$minAngkatan}", "max:{$maxAngkatan}"],
            'status'        => ['nullable', Rule::in(array_keys(Siswa::STATUS_OPTIONS))],
            'alamat'        => 'required|string',
            'tanggal_lahir' => [
                'required',
                'date',
                'before_or_equal:today',
                function (string $attribute, mixed $value, \Closure $fail) use ($effectiveAngkatan, $minBirthYear, $maxBirthYear) {
                    $birthYear = \Carbon\Carbon::parse($value)->year;
                    $entryAge = $effectiveAngkatan - $birthYear;
                    if ($entryAge < Siswa::MIN_ENTRY_AGE || $entryAge > Siswa::MAX_ENTRY_AGE) {
                        $fail("Tanggal lahir tidak sesuai dengan angkatan {$effectiveAngkatan}. Usia saat masuk harus antara " . Siswa::MIN_ENTRY_AGE . ' hingga ' . Siswa::MAX_ENTRY_AGE . " tahun (tahun lahir {$minBirthYear}–{$maxBirthYear}).");
                    }
                },
            ],
            'jenis_kelamin' => 'required|in:L,P',
            'agama'         => ['nullable', Rule::in(Siswa::AGAMA_OPTIONS)],
            'wali_murid'    => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\.,]+$/u'],
            'nohp_wali'     => 'required|numeric|digits_between:10,13',
            'kelas_id'      => 'nullable|exists:kelases,id',
        ], [
            'nama_siswa.required'           => 'Nama lengkap siswa wajib diisi.',
            'nama_siswa.max'                => 'Nama lengkap siswa maksimal 100 karakter.',
            'nama_siswa.regex'              => 'Nama hanya boleh berisi huruf, spasi, koma (,), dan titik (.).',
            'angkatan.date_format'          => 'Angkatan harus berupa format tahun 4 digit (contoh: 2026).',
            'angkatan.min'                  => "Angkatan tidak boleh kurang dari tahun {$minAngkatan}.",
            'angkatan.max'                  => "Angkatan tidak boleh lebih dari tahun {$maxAngkatan}.",
            'wali_murid.max'                => 'Nama wali murid maksimal 100 karakter.',
            'nis.required'                  => 'NIS siswa wajib diisi.',
            'nis.numeric'                   => 'NIS siswa harus berupa angka.',
            'nis.digits_between'            => 'NIS siswa harus terdiri dari 4 hingga 10 digit angka.',
            'nis.unique'                    => 'NIS ini sudah terdaftar untuk siswa lain.',
            'alamat.required'               => 'Alamat tempat tinggal wajib diisi.',
            'tanggal_lahir.required'        => 'Tanggal lahir siswa wajib diisi.',
            'tanggal_lahir.date'            => 'Format tanggal lahir tidak valid.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'jenis_kelamin.required'        => 'Jenis kelamin wajib dipilih.',
            'wali_murid.required'           => 'Nama wali murid wajib diisi.',
            'wali_murid.regex'              => 'Nama wali murid hanya boleh berisi huruf, spasi, koma (,), dan titik (.).',
            'nohp_wali.required'            => 'Nomor HP / WhatsApp wali murid wajib diisi.',
            'nohp_wali.numeric'             => 'Nomor HP / WhatsApp wali murid harus berupa angka.',
            'nohp_wali.digits_between'      => 'Nomor HP / WhatsApp wali murid harus terdiri dari 10 hingga 13 digit angka.',
        ]);

        $statusSiswa = $validated['status'] ?? 'aktif';
        $activePeriod = AcademicPeriod::current();
        $siswa = Siswa::create([
            'nama_siswa'    => $validated['nama_siswa'],
            'nis'           => $validated['nis'],
            'angkatan'      => $validated['angkatan'] ?? AcademicPeriod::defaultAngkatan(),
            'alamat'        => $validated['alamat'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'agama'         => $validated['agama'] ?? null,
            'wali_murid'    => $validated['wali_murid'],
            'nohp_wali'     => $validated['nohp_wali'],
            'status'        => $statusSiswa,
        ]);

        // Jika kelas dipilih, daftarkan otomatis ke anggota_kelases pada periode aktif
        if (!empty($validated['kelas_id'])) {
            AnggotaKelas::firstOrCreate([
                'kelas_id'           => $validated['kelas_id'],
                'siswa_id'           => $siswa->id,
                'academic_period_id' => $activePeriod->id,
            ]);
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
                'status'   => $statusSiswa === 'aktif' ? 'aktif' : 'nonaktif',
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

        return view('admin.kelola_siswa.show', compact('siswa'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Siswa $siswa)
    {
        $kelases = Kelas::all();

        return view('admin.kelola_siswa.edit', compact('siswa', 'kelases'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Siswa $siswa)
    {
        $minAngkatan = Siswa::MIN_ANGKATAN;
        $maxAngkatan = Siswa::maxAngkatan();
        $effectiveAngkatan = $request->filled('angkatan') && is_numeric($request->input('angkatan'))
            ? (int) $request->input('angkatan')
            : ($siswa->angkatan ?? AcademicPeriod::defaultAngkatan());
        $minBirthYear = $effectiveAngkatan - Siswa::MAX_ENTRY_AGE;
        $maxBirthYear = $effectiveAngkatan - Siswa::MIN_ENTRY_AGE;

        $validated = $request->validate([
            'nama_siswa'    => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\.,]+$/u'],
            'nis'           => ['required', 'numeric', 'digits_between:4,10', Rule::unique('siswas')->ignore($siswa->id)],
            'angkatan'      => ['nullable', 'date_format:Y', 'integer', "min:{$minAngkatan}", "max:{$maxAngkatan}"],
            'status'        => ['nullable', Rule::in(array_keys(Siswa::STATUS_OPTIONS))],
            'alamat'        => 'required|string',
            'tanggal_lahir' => [
                'required',
                'date',
                'before_or_equal:today',
                function (string $attribute, mixed $value, \Closure $fail) use ($effectiveAngkatan, $minBirthYear, $maxBirthYear) {
                    $birthYear = \Carbon\Carbon::parse($value)->year;
                    $entryAge = $effectiveAngkatan - $birthYear;
                    if ($entryAge < Siswa::MIN_ENTRY_AGE || $entryAge > Siswa::MAX_ENTRY_AGE) {
                        $fail("Tanggal lahir tidak sesuai dengan angkatan {$effectiveAngkatan}. Usia saat masuk harus antara " . Siswa::MIN_ENTRY_AGE . ' hingga ' . Siswa::MAX_ENTRY_AGE . " tahun (tahun lahir {$minBirthYear}–{$maxBirthYear}).");
                    }
                },
            ],
            'jenis_kelamin' => 'required|in:L,P',
            'agama'         => ['nullable', Rule::in(Siswa::AGAMA_OPTIONS)],
            'wali_murid'    => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\.,]+$/u'],
            'nohp_wali'     => 'required|numeric|digits_between:10,13',
            'kelas_id'      => 'nullable|exists:kelases,id',
        ], [
            'nama_siswa.required'           => 'Nama lengkap siswa wajib diisi.',
            'nama_siswa.max'                => 'Nama lengkap siswa maksimal 100 karakter.',
            'nama_siswa.regex'              => 'Nama hanya boleh berisi huruf, spasi, koma (,), dan titik (.).',
            'angkatan.date_format'          => 'Angkatan harus berupa format tahun 4 digit (contoh: 2026).',
            'angkatan.min'                  => "Angkatan tidak boleh kurang dari tahun {$minAngkatan}.",
            'angkatan.max'                  => "Angkatan tidak boleh lebih dari tahun {$maxAngkatan}.",
            'wali_murid.max'                => 'Nama wali murid maksimal 100 karakter.',
            'nis.required'                  => 'NIS siswa wajib diisi.',
            'nis.numeric'                   => 'NIS siswa harus berupa angka.',
            'nis.digits_between'            => 'NIS siswa harus terdiri dari 4 hingga 10 digit angka.',
            'nis.unique'                    => 'NIS ini sudah terdaftar untuk siswa lain.',
            'alamat.required'               => 'Alamat tempat tinggal wajib diisi.',
            'tanggal_lahir.required'        => 'Tanggal lahir siswa wajib diisi.',
            'tanggal_lahir.date'            => 'Format tanggal lahir tidak valid.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'jenis_kelamin.required'        => 'Jenis kelamin wajib dipilih.',
            'wali_murid.required'           => 'Nama wali murid wajib diisi.',
            'wali_murid.regex'              => 'Nama wali murid hanya boleh berisi huruf, spasi, koma (,), dan titik (.).',
            'nohp_wali.required'            => 'Nomor HP / WhatsApp wali murid wajib diisi.',
            'nohp_wali.numeric'             => 'Nomor HP / WhatsApp wali murid harus berupa angka.',
            'nohp_wali.digits_between'      => 'Nomor HP / WhatsApp wali murid harus terdiri dari 10 hingga 13 digit angka.',
        ]);

        $updatePayload = array_diff_key($validated, ['kelas_id' => true]);
        if (empty($updatePayload['angkatan'])) {
            unset($updatePayload['angkatan']);
        }
        if (empty($updatePayload['status'])) {
            unset($updatePayload['status']);
        }
        $siswa->update($updatePayload);

        // Sinkronisasi ke anggota_kelases pada periode aktif
        $activePeriod = AcademicPeriod::current();
        if (!empty($validated['kelas_id'])) {
            AnggotaKelas::updateOrCreate(
                [
                    'siswa_id'           => $siswa->id,
                    'academic_period_id' => $activePeriod->id,
                ],
                [
                    'kelas_id'           => $validated['kelas_id'],
                ]
            );
        } else {
            AnggotaKelas::where('siswa_id', $siswa->id)
                ->where('academic_period_id', $activePeriod->id)
                ->delete();
        }

        if ($siswa->user) {
            $userUpdate = ['name' => $validated['nama_siswa']];
            if (!empty($validated['status'])) {
                $userUpdate['status'] = $validated['status'] === 'aktif' ? 'aktif' : 'nonaktif';
            }
            $siswa->user->update($userUpdate);
        }

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil diperbarui!');
    }

    /**
     * Ubah status siswa menjadi aktif atau nonaktif (dan otomatis sinkronkan status akun user terikat).
     */
    public function toggleStatus(Siswa $siswa)
    {
        $siswa->status = ($siswa->status === 'aktif') ? 'nonaktif' : 'aktif';
        $siswa->save();

        if ($siswa->user) {
            $siswa->user->update(['status' => $siswa->status]);
        }

        $statusText = $siswa->status === 'aktif' ? 'diaktifkan kembali' : 'dinonaktifkan';
        return redirect()->route('siswa.index')->with('success', "Data siswa {$siswa->nama_siswa} dan akun terikat berhasil {$statusText}!");
    }

    /**
     * Menonaktifkan data siswa (menggantikan fungsi hapus permanen).
     */
    public function destroy(Siswa $siswa)
    {
        return $this->toggleStatus($siswa);
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
        $angkatanIdx = array_search('angkatan', $header);
        $agamaIdx = array_search('agama', $header);
        $statusIdx = array_search('status', $header);
        $minAngkatan = Siswa::MIN_ANGKATAN;
        $maxAngkatan = Siswa::maxAngkatan();

        if ($nameIdx === false || $nisIdx === false) {
            fclose($handle);
            return back()->with('error', 'Format header CSV harus minimal memiliki kolom: nama_siswa, nis');
        }

        $validationErrors = [];
        $rowsToImport = [];
        $seenNis = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $rowNumber++;

            // Skip baris jika benar-benar kosong
            if (empty(array_filter($row))) {
                continue;
            }

            $nama = trim($row[$nameIdx] ?? '');
            $rawNis = trim($row[$nisIdx] ?? '');
            $nis = null;
            $rowErrors = [];

            if (empty($nama)) {
                $rowErrors[] = 'Nama siswa wajib diisi.';
            }

            if (empty($rawNis)) {
                $rowErrors[] = 'NIS wajib diisi.';
            } else {
                $nis = preg_replace('/[^0-9]/', '', $rawNis);
                if (empty($nis)) {
                    $rowErrors[] = "NIS '{$rawNis}' tidak valid (harus berupa angka).";
                } elseif (strlen($nis) < 4 || strlen($nis) > 10) {
                    $rowErrors[] = "NIS '{$nis}' harus terdiri dari 4 hingga 10 digit angka.";
                } elseif (in_array($nis, $seenNis)) {
                    $rowErrors[] = "NIS '{$nis}' duplikat dalam file CSV ini.";
                } elseif (Siswa::where('nis', $nis)->exists()) {
                    $rowErrors[] = "NIS '{$nis}' sudah terdaftar dalam sistem.";
                } else {
                    $seenNis[] = $nis;
                }
            }

            // Validasi Angkatan terlebih dahulu agar bisa dipakai untuk validasi usia masuk
            $rawAngkatanInput = $angkatanIdx !== false ? trim($row[$angkatanIdx] ?? '') : '';
            $angkatan = AcademicPeriod::defaultAngkatan();
            if ($rawAngkatanInput !== '') {
                $cleanAngkatan = preg_replace('/[^0-9]/', '', $rawAngkatanInput);
                if (strlen($cleanAngkatan) !== 4 || (int) $cleanAngkatan < $minAngkatan || (int) $cleanAngkatan > $maxAngkatan) {
                    $rowErrors[] = "Angkatan '{$rawAngkatanInput}' tidak valid (harus tahun {$minAngkatan}–{$maxAngkatan}).";
                } else {
                    $angkatan = (int) $cleanAngkatan;
                }
            }

            // Normalisasi & validasi Tanggal Lahir relatif terhadap Angkatan (Usia masuk 10-18 tahun)
            $rawTgl = $tglIdx !== false ? trim($row[$tglIdx] ?? '') : '';
            $normalizedTgl = null;
            if (empty($rawTgl)) {
                $rowErrors[] = 'Tanggal lahir wajib diisi.';
            } else {
                $parsedTgl = DateHelper::normalize($rawTgl);
                if (!$parsedTgl) {
                    $rowErrors[] = "Format tanggal lahir '{$rawTgl}' tidak valid.";
                } else {
                    $birthDate = \Carbon\Carbon::parse($parsedTgl);
                    $entryAge = $angkatan - $birthDate->year;
                    if ($birthDate->isFuture()) {
                        $rowErrors[] = "Tanggal lahir '{$rawTgl}' tidak boleh melebihi hari ini.";
                    } elseif ($entryAge < Siswa::MIN_ENTRY_AGE || $entryAge > Siswa::MAX_ENTRY_AGE) {
                        $minBirthYear = $angkatan - Siswa::MAX_ENTRY_AGE;
                        $maxBirthYear = $angkatan - Siswa::MIN_ENTRY_AGE;
                        $rowErrors[] = "Tanggal lahir '{$rawTgl}' ({$parsedTgl}) tidak sesuai dengan angkatan {$angkatan}. Usia saat masuk harus " . Siswa::MIN_ENTRY_AGE . '–' . Siswa::MAX_ENTRY_AGE . " tahun (tahun lahir {$minBirthYear}–{$maxBirthYear}).";
                    } else {
                        $normalizedTgl = $parsedTgl;
                    }
                }
            }

            // Alamat
            $alamat = $alamatIdx !== false && !empty(trim($row[$alamatIdx] ?? '')) ? trim($row[$alamatIdx]) : 'Alamat belum diatur';

            // Jenis Kelamin
            $rawJk = $jkIdx !== false ? strtoupper(trim($row[$jkIdx] ?? 'L')) : 'L';
            $jk = in_array($rawJk, ['L', 'LAKI-LAKI', 'PRIA']) ? 'L' : 'P';

            // Wali & Nohp
            $wali = $waliIdx !== false && !empty(trim($row[$waliIdx] ?? '')) ? trim($row[$waliIdx]) : ($nama ? 'Wali Murid ' . $nama : 'Wali Murid');
            $rawNohp = $nohpIdx !== false ? trim($row[$nohpIdx] ?? '') : '';
            $nohp = preg_replace('/[^0-9]/', '', $rawNohp);
            if (empty($nohp)) {
                $nohp = '081234567890';
            }

            // Cari Kelas jika diisi
            $kelasId = null;
            if ($kelasIdx !== false && !empty(trim($row[$kelasIdx] ?? ''))) {
                $kelasVal = trim($row[$kelasIdx]);
                if (is_numeric($kelasVal)) {
                    $kelasId = Kelas::where('id', $kelasVal)->value('id');
                }
                if (!$kelasId) {
                    $kelasId = Kelas::where('nama_kelas', 'like', "%{$kelasVal}%")->value('id');
                }
                if (!$kelasId) {
                    $rowErrors[] = "Kelas '{$kelasVal}' tidak ditemukan di database.";
                }
            }

            // Status siswa (opsional, default aktif)
            $statusSiswa = 'aktif';
            if ($statusIdx !== false && !empty(trim($row[$statusIdx] ?? ''))) {
                $rawStatus = strtolower(trim($row[$statusIdx]));
                if (!array_key_exists($rawStatus, Siswa::STATUS_OPTIONS)) {
                    $rowErrors[] = "Status '{$row[$statusIdx]}' tidak valid (pilihan: " . implode(', ', array_keys(Siswa::STATUS_OPTIONS)) . ').';
                } else {
                    $statusSiswa = $rawStatus;
                }
            }

            // Agama (opsional, harus sesuai daftar Siswa::AGAMA_OPTIONS jika diisi)
            $agama = null;
            if ($agamaIdx !== false && !empty(trim($row[$agamaIdx] ?? ''))) {
                $rawAgama = trim($row[$agamaIdx]);
                foreach (Siswa::AGAMA_OPTIONS as $opt) {
                    if (strcasecmp($opt, $rawAgama) === 0) {
                        $agama = $opt;
                        break;
                    }
                }
                if (!$agama) {
                    $rowErrors[] = "Agama '{$rawAgama}' tidak valid (pilihan: " . implode(', ', Siswa::AGAMA_OPTIONS) . ').';
                }
            }

            if (!empty($rowErrors)) {
                $validationErrors[] = "Baris {$rowNumber}: " . implode(' ', $rowErrors);
            } else {
                $rowsToImport[] = [
                    'nama_siswa'    => $nama,
                    'nis'           => $nis,
                    'angkatan'      => $angkatan,
                    'alamat'        => $alamat,
                    'tanggal_lahir' => $normalizedTgl,
                    'jenis_kelamin' => $jk,
                    'agama'         => $agama,
                    'wali_murid'    => $wali,
                    'nohp_wali'     => $nohp,
                    'status'        => $statusSiswa,
                    'kelas_id'      => $kelasId,
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
        $activePeriod = AcademicPeriod::current();
        DB::transaction(function () use ($rowsToImport, $activePeriod) {
            foreach ($rowsToImport as $data) {
                $siswa = Siswa::create(array_diff_key($data, ['kelas_id' => true]));

                if ($data['kelas_id']) {
                    AnggotaKelas::firstOrCreate(
                        [
                            'kelas_id'           => $data['kelas_id'],
                            'siswa_id'           => $siswa->id,
                            'academic_period_id' => $activePeriod->id,
                        ]
                    );
                }

                User::firstOrCreate(
                    ['username' => $data['nis']],
                    [
                        'name'     => $data['nama_siswa'],
                        'email'    => $data['nis'] . '@siswa.learnpoint.sch.id',
                        'password' => Hash::make($data['nis']),
                        'role'     => 'siswa',
                        'status'   => ($data['status'] ?? 'aktif') === 'aktif' ? 'aktif' : 'nonaktif',
                        'siswa_id' => $siswa->id,
                    ]
                );
            }
        });

        $count = count($rowsToImport);
        return redirect()->route('siswa.index')->with('success', "Import berhasil: {$count} data siswa dan akun pengguna berhasil ditambahkan.");
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

        $defaultAngkatan = (string) AcademicPeriod::defaultAngkatan();
        $columns = ['nama_siswa', 'nis', 'angkatan', 'alamat', 'tanggal_lahir', 'jenis_kelamin', 'agama', 'wali_murid', 'nohp_wali', 'kelas'];
        $sampleData = [
            ['Muhammad Rizky', '20241001', $defaultAngkatan, 'Jl. Merpati Putih No. 12', '2012-05-14', 'L', 'Islam', 'Hendra Kusuma', '081234567891', ''],
            ['Siti Aisyah', '20241002', $defaultAngkatan, 'Jl. Kenanga Indah No. 5', '2012-08-20', 'P', 'Islam', 'Rahmat Hidayat', '081234567892', ''],
            ['Budi Santoso', '20241003', $defaultAngkatan, 'Jl. Dahlia No. 44', '2012-03-10', 'L', 'Kristen', 'Sutrisno', '081234567893', ''],
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

