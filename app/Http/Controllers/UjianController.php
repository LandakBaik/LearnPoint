<?php

namespace App\Http\Controllers;

use App\Models\Ujian;
use App\Models\Soal;
use App\Models\PengampuKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UjianController extends Controller
{
    /**
     * Menampilkan semua ujian milik guru yang sedang login.
     */
    public function index(Request $request)
    {
        $guruId = auth()->user()->guru_id;

        $query = Ujian::with([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'soals',
        ])
        ->whereHas('pengampuKelas.guruMapel', function ($query) use ($guruId) {
            $query->where('guru_id', $guruId);
        });

        // Pencarian berdasarkan judul
        if ($request->filled('search')) {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Urutan terbaru
        $ujians = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('guru.ujian.index', compact('ujians'));
    }


    /**
     * Menampilkan halaman tambah ujian.
     */
    public function create()
    {
        $guruId = auth()->user()->guru_id;

        $pengampuKelases = PengampuKelas::with([
            'guruMapel.mapel',
            'kelas',
        ])
        ->whereHas('guruMapel', function ($query) use ($guruId) {
            $query->where('guru_id', $guruId);
        })
        ->get();

        return view('guru.ujian.create', compact('pengampuKelases'));
    }


    /**
     * Menyimpan ujian baru beserta soal-soalnya.
     */
    public function store(Request $request)
{
    $guruId = auth()->user()->guru_id;

    $validated = $request->validate([
        'judul' => ['required', 'string', 'max:100'],
        'deskripsi' => ['nullable', 'string'],
        'kisi_kisi' => ['nullable', 'string','max:500'],

        'pengampu_kelas_id' => [
            'required',
            'integer',
        ],

        'durasi_menit' => [
            'required',
            'integer',
            'min:1',
            'max:999',
        ],

        'kkm' => [
            'required',
            'numeric',
            'min:0',
            'max:99',
        ],

        'waktu_mulai' => [
            'nullable',
            'date',
        ],

        'deadline' => [
            'nullable',
            'date',
            'after_or_equal:waktu_mulai',
        ],

        'soals' => [
            'required',
            'array',
            'min:1',
            'max:100',
        ],

        'soals.*.pertanyaan' => [
            'required',
            'string',
            'max:500',

        ],

        'soals.*.tipe_soal' => [
            'required',
            'in:single_choice,multiple_choice,essay,matching',
        ],

        'soals.*.pilihan' => [
            'nullable',
            'array',
            'max:6',

        ],

        'soals.*.kunci_jawaban' => [
            'nullable',
        ],

        'soals.*.bobot' => [
            'nullable',
            'numeric',
            'min:0',
            'max:99',
        ],

        'soals.*.urutan' => [
            'nullable',
            'integer',
            'min:1',
        ],

        'soals.*.gambar' => [
            'nullable',
            'image',
            'mimes:jpg,jpeg,png,webp,gif',
            'max:2048',
        ],
    ]);

    /*
    |--------------------------------------------------------------------------
    | Pastikan pengampu kelas memang milik guru yang sedang login
    |--------------------------------------------------------------------------
    */

    $pengampuKelas = PengampuKelas::whereHas(
        'guruMapel',
        function ($query) use ($guruId) {
            $query->where('guru_id', $guruId);
        }
    )->findOrFail($validated['pengampu_kelas_id']);


    /*
    |--------------------------------------------------------------------------
    | Tentukan status ujian
    |--------------------------------------------------------------------------
    */

    $action = $request->input('action', 'draft');

    if ($action === 'schedule') {

    $request->validate([
        'waktu_mulai' => ['required', 'date'],
        'deadline' => ['required', 'date', 'after:waktu_mulai'],
    ]);
}

    $status = match ($action) {
        'schedule' => 'scheduled',
        'publish' => 'published',
        default => 'draft',
    };


    /*
    |--------------------------------------------------------------------------
    | Simpan ujian + soal dalam satu transaksi
    |--------------------------------------------------------------------------
    */

    DB::transaction(function () use (
        $request,
        $validated,
        $pengampuKelas,
        $status,
        $action
    ) {

        $ujian = Ujian::create([
    'judul' => $validated['judul'],
    'deskripsi' => $validated['deskripsi'] ?? null,
    'kisi_kisi' => $validated['kisi_kisi'] ?? null,

    'pengampu_kelas_id' => $pengampuKelas->id,
    'guru_mapel_id' => $pengampuKelas->guru_mapel_id,

    'durasi_menit' => $validated['durasi_menit'],
    'kkm' => $validated['kkm'],
    'waktu_mulai' => $validated['waktu_mulai'] ?? null,
    'deadline' => $validated['deadline'] ?? null,
    'status' => $status,
    'dipublikasi_at' => $action === 'publish' ? now() : null,
]);
        /*
        |--------------------------------------------------------------------------
        | Simpan setiap soal
        |--------------------------------------------------------------------------
        */

        foreach ($request->input('soals', []) as $index => $data) {

            $gambar = null;

            /*
            |--------------------------------------------------------------------------
            | Upload gambar soal
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile("soals.$index.gambar")) {

                $gambar = $request
                    ->file("soals.$index.gambar")
                    ->store('soal-images', 'public');
            }


            /*
            |--------------------------------------------------------------------------
            | Pastikan pilihan berupa array
            |--------------------------------------------------------------------------
            */

            $pilihan = $data['pilihan'] ?? null;

            if (!is_array($pilihan)) {
                $pilihan = null;
            }


            /*
            |--------------------------------------------------------------------------
            | Kunci jawaban
            |--------------------------------------------------------------------------
            */

            // ============================================================
// SIAPKAN KUNCI JAWABAN
// Database tidak mengizinkan NULL.
// Jadi minimal kita simpan [].
// ============================================================

$kunciJawaban = $data['kunci_jawaban'] ?? [];

// Kalau bukan array, ubah menjadi array.
if (!is_array($kunciJawaban)) {
    $kunciJawaban = [$kunciJawaban];
}

// Buang nilai kosong.
$kunciJawaban = array_values(
    array_filter(
        $kunciJawaban,
        fn ($value) => $value !== null && trim((string) $value) !== ''
    )
);

// Khusus essay.
if ($data['tipe_soal'] === 'essay') {

    $jawabanEssay = trim(
        (string) ($data['kunci_jawaban_text'] ?? '')
    );

    if ($jawabanEssay !== '') {
        $kunciJawaban = [$jawabanEssay];
    } else {
        $kunciJawaban = [];
    }
}

// Pastikan TIDAK PERNAH NULL.
// Simpan sebagai JSON secara eksplisit.
$kunciJawabanJson = json_encode(
    $kunciJawaban,
    JSON_UNESCAPED_UNICODE
);

if ($kunciJawabanJson === false || $kunciJawabanJson === 'null') {
    $kunciJawabanJson = '[]';
}

            /*
            |--------------------------------------------------------------------------
            | Simpan soal
            |--------------------------------------------------------------------------
            */

            Soal::create([

                'pertanyaan' => $data['pertanyaan'],

                'tipe_soal' => $data['tipe_soal'],

                'pilihan' => $pilihan,

                'kunci_jawaban' => $kunciJawaban,

                'gambar' => $gambar,

                'bobot' => $data['bobot'] ?? 1,

                'urutan' => $data['urutan'] ?? ($index + 1),

                'ujian_id' => $ujian->id,

                'quiz_id' => null,

                'tugas_id' => null,
            ]);
        }

    });


    /*
    |--------------------------------------------------------------------------
    | Pesan berdasarkan aksi
    |--------------------------------------------------------------------------
    */

    $message = match ($action) {

        'schedule' =>
            'Ujian berhasil dijadwalkan.',

        'publish' =>
            'Ujian berhasil dipublikasi.',

        default =>
            'Ujian berhasil disimpan sebagai draft.',
    };


    return redirect()
        ->route('guru.ujian.index')
        ->with('success', $message);
}
    public function show(Ujian $ujian)
    {
        $this->authorizeGuruUjian($ujian);

        $ujian->load([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'soals',
            'seleksiUjians',
        ]);

        return view('guru.ujian.show', compact('ujian'));
    }


    /**
     * Menampilkan halaman edit.
     */
    public function edit(Ujian $ujian)
{
    $this->authorizeGuruUjian($ujian);

    $guruId = auth()->user()->guru_id;

    $pengampuKelases = PengampuKelas::with([
        'guruMapel.mapel',
        'kelas',
    ])
    ->whereHas('guruMapel', function ($query) use ($guruId) {
        $query->where('guru_id', $guruId);
    })
    ->get();

    $ujian->load('soals');

    // Siapkan data soal untuk dikirim ke JavaScript.
    // Jangan melakukan map(function...) langsung di Blade.
    $soalData = $ujian->soals->map(function ($soal) {
        return [
            'id' => $soal->id,
            'localId' => 'existing-' . $soal->id,

            'pertanyaan' => $soal->pertanyaan,

            'tipe_soal' => $soal->tipe_soal,

            'pilihan' => is_array($soal->pilihan)
                ? array_values($soal->pilihan)
                : [],

            'kunci_jawaban' => is_array($soal->kunci_jawaban)
                ? array_values($soal->kunci_jawaban)
                : [],

            'kunci_jawaban_text' => is_array($soal->kunci_jawaban)
                ? implode("\n", $soal->kunci_jawaban)
                : ($soal->kunci_jawaban ?? ''),

            'gambar' => $soal->gambar
                ? asset('storage/' . $soal->gambar)
                : null,

            'bobot' => (float) $soal->bobot,
        ];
    })->values()->all();

    return view('guru.ujian.edit', compact(
        'ujian',
        'pengampuKelases',
        'soalData'
    ));
}


    /**
     * Update ujian beserta soal.
     */
    public function update(Request $request, Ujian $ujian)
{




    $validated = $request->validate([
        'judul' => ['required', 'string', 'max:200'],
        'deskripsi' => ['nullable', 'string'],
        'kisi_kisi' => ['nullable', 'string'],

        'pengampu_kelas_id' => ['required', 'integer'],
        'durasi_menit' => ['required', 'integer', 'min:1'],
        'kkm' => ['required', 'numeric', 'min:0', 'max:100'],

        'waktu_mulai' => ['nullable', 'date'],
        'deadline' => ['nullable', 'date', 'after_or_equal:waktu_mulai'],

        'soals' => ['required', 'array', 'min:1'],

        'soals.*.id' => ['nullable', 'integer'],
        'soals.*.pertanyaan' => ['required', 'string'],
        'soals.*.tipe_soal' => [
            'required',
            'in:single_choice,multiple_choice,essay,matching'
        ],

        'soals.*.pilihan' => ['nullable', 'array'],
        'soals.*.kunci_jawaban' => ['nullable'],
        'soals.*.kunci_jawaban_text' => ['nullable', 'string'],

        'soals.*.bobot' => ['nullable', 'numeric', 'min:0'],
        'soals.*.urutan' => ['nullable', 'integer', 'min:1'],

        'soals.*.gambar' => [
            'nullable',
            'image',
            'mimes:jpg,jpeg,png,webp,gif',
            'max:2048'
        ],
    ]);

    $guruId = auth()->user()->guru_id;

    $pengampuKelas = PengampuKelas::whereHas(
        'guruMapel',
        function ($query) use ($guruId) {
            $query->where('guru_id', $guruId);
        }
    )->findOrFail($validated['pengampu_kelas_id']);

    DB::transaction(function () use (
        $request,
        $validated,
        $ujian,
        $pengampuKelas
    ) {

        // =====================================================
        // UPDATE DATA UJIAN
        // =====================================================

        $ujian->update([
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'kisi_kisi' => $validated['kisi_kisi'] ?? null,

            'pengampu_kelas_id' => $pengampuKelas->id,
            'guru_mapel_id' => $pengampuKelas->guru_mapel_id,

            'durasi_menit' => $validated['durasi_menit'],
            'kkm' => $validated['kkm'],

            'waktu_mulai' => $validated['waktu_mulai'] ?? null,
            'deadline' => $validated['deadline'] ?? null,
        ]);

        // =====================================================
        // ID SOAL YANG MASIH ADA DI FORM
        // =====================================================

        $submittedSoalIds = collect($request->input('soals', []))
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        // =====================================================
        // HAPUS SOAL YANG DIHAPUS DARI FORM
        // =====================================================

        $soalsYangDihapus = $ujian->soals()
            ->whereNotIn('id', $submittedSoalIds)
            ->get();

        foreach ($soalsYangDihapus as $soal) {

            if ($soal->gambar) {
                Storage::disk('public')->delete($soal->gambar);
            }

            $soal->delete();
        }

        // =====================================================
        // SIMPAN / UPDATE SOAL
        // =====================================================

        foreach ($request->input('soals', []) as $index => $data) {

            $soal = null;

            // -------------------------------------------------
            // Kalau soal lama
            // -------------------------------------------------

            if (!empty($data['id'])) {

                $soal = $ujian->soals()
                    ->where('id', $data['id'])
                    ->first();

                if (!$soal) {
                    continue;
                }
            }

            // -------------------------------------------------
            // GAMBAR
            // -------------------------------------------------

            $gambar = $soal?->gambar;

            if ($request->hasFile("soals.$index.gambar")) {

                if ($gambar) {
                    Storage::disk('public')->delete($gambar);
                }

                $gambar = $request
                    ->file("soals.$index.gambar")
                    ->store('soal-images', 'public');
            }

            // =================================================
            // PILIHAN JAWABAN
            // =================================================

            $pilihan = $data['pilihan'] ?? [];

            if (!is_array($pilihan)) {
                $pilihan = [];
            }

            $pilihan = array_values(
                array_filter(
                    $pilihan,
                    fn ($value) =>
                        $value !== null &&
                        trim((string) $value) !== ''
                )
            );

            // =================================================
            // KUNCI JAWABAN
            // =================================================

            $kunciJawaban = $data['kunci_jawaban'] ?? [];

            // Kalau bukan array, jadikan array
            if (!is_array($kunciJawaban)) {
                $kunciJawaban = [$kunciJawaban];
            }

            // Bersihkan nilai kosong
            $kunciJawaban = array_values(
                array_filter(
                    $kunciJawaban,
                    fn ($value) =>
                        $value !== null &&
                        trim((string) $value) !== ''
                )
            );

            // -------------------------------------------------
            // KHUSUS ESSAY
            // -------------------------------------------------

            if ($data['tipe_soal'] === 'essay') {

                $jawabanEssay = trim(
                    (string) ($data['kunci_jawaban_text'] ?? '')
                );

                if ($jawabanEssay !== '') {
                    $kunciJawaban = [$jawabanEssay];
                } else {
                    $kunciJawaban = [];
                }
            }

            // =================================================
            // PASTIKAN TIDAK PERNAH NULL
            // =================================================

            if (!is_array($kunciJawaban)) {
                $kunciJawaban = [];
            }

            if (!is_array($pilihan)) {
                $pilihan = [];
            }

            // =================================================
            // DATA SOAL
            // =================================================

            $soalData = [
                'pertanyaan' => $data['pertanyaan'],

                'tipe_soal' => $data['tipe_soal'],

                // SELALU ARRAY, TIDAK BOLEH NULL
                'pilihan' => $pilihan,

                // SELALU ARRAY, TIDAK BOLEH NULL
                'kunci_jawaban' => $kunciJawaban,

                'gambar' => $gambar,

                'bobot' => $data['bobot'] ?? 1,

                'urutan' => $data['urutan'] ?? ($index + 1),

                'ujian_id' => $ujian->id,

                'quiz_id' => null,

                'tugas_id' => null,
            ];

            // =================================================
            // UPDATE SOAL LAMA
            // =================================================

            if ($soal) {

                $soal->fill($soalData);
                $soal->save();

            }

            // =================================================
            // BUAT SOAL BARU
            // =================================================

            else {

                Soal::create($soalData);
            }
        }
    });

    return redirect()
        ->route('guru.ujian.show', $ujian)
        ->with('success', 'Ujian berhasil diperbarui.');
}    public function publish(Ujian $ujian)
    {
        $this->authorizeGuruUjian($ujian);

        $ujian->update([
            'status' => 'published',
            'dipublikasi_at' => now(),
        ]);

        return back()->with(
            'success',
            'Ujian berhasil dipublikasi.'
        );
    }


    /**
     * Menjadwalkan ujian.
     */
    public function schedule(Request $request, Ujian $ujian)
    {
        $this->authorizeGuruUjian($ujian);

        $validated = $request->validate([
            'waktu_mulai' => ['required', 'date'],
            'deadline' => ['required', 'date', 'after:waktu_mulai'],
        ]);

        $ujian->update([
            'waktu_mulai' => $validated['waktu_mulai'],
            'deadline' => $validated['deadline'],
            'status' => 'scheduled',
        ]);

        return back()->with(
            'success',
            'Ujian berhasil dijadwalkan.'
        );
    }


    /**
     * Memastikan ujian adalah milik guru yang sedang login.
     */
    private function authorizeGuruUjian(Ujian $ujian): void
    {
        $guruId = auth()->user()->guru_id;

        $belongsToGuru = $ujian->pengampuKelas()
            ->whereHas('guruMapel', function ($query) use ($guruId) {
                $query->where('guru_id', $guruId);
            })
            ->exists();

        abort_unless($belongsToGuru, 403);
    }


    /**
     * Menyiapkan pilihan jawaban agar bisa disimpan sebagai JSON.
     */
    private function preparePilihan($pilihan): ?array
    {
        if (empty($pilihan)) {
            return null;
        }

        if (is_string($pilihan)) {
            $decoded = json_decode($pilihan, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }

            return array_values(
                array_filter(
                    preg_split('/\r\n|\r|\n/', $pilihan)
                )
            );
        }

        return $pilihan;
    }


    /**
     * Menyiapkan kunci jawaban.
     */
    private function prepareKunciJawaban($kunci): ?array
    {
        if (empty($kunci)) {
            return null;
        }

        if (is_string($kunci)) {
            $decoded = json_decode($kunci, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }

            return array_values(
                array_filter(
                    preg_split('/\r\n|\r|\n/', $kunci)
                )
            );
        }

        return $kunci;
    }
}
