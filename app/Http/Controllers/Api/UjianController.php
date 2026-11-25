<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menjawab;
use App\Models\Nilai;
use App\Models\Notifikasi;
use App\Models\PengampuKelas;
use App\Models\SeleksiUjian;
use App\Models\Siswa;
use App\Models\Soal;
use App\Models\Ujian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UjianController extends Controller
{
    /**
     * Helper authorization for Guru.
     */
    protected function authorizeGuruUjian(Ujian $ujian, $guruId)
    {
        $owner = $ujian->pengampuKelas?->guruMapel?->guru_id;
        if (!$owner || (int) $owner !== (int) $guruId) {
            abort(403, 'Akses ditolak.');
        }
    }

    /**
     * GURU: Menampilkan semua ujian milik guru yang sedang login.
     */
    public function guruIndex(Request $request)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;

        if (!$guruId) {
            return $this->notFoundResponse('Profil guru tidak ditemukan.');
        }

        $query = Ujian::with([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
        ])
        ->withCount(['soals', 'seleksiUjians'])
        ->whereHas('pengampuKelas.guruMapel', function ($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        });

        if ($request->filled('search')) {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('kelas_id')) {
            $query->whereHas('pengampuKelas', fn ($q) => $q->where('kelas_id', $request->kelas_id));
        }

        if ($request->has('is_archived')) {
            $query->where('is_archived', $request->boolean('is_archived'));
        }

        $ujians = $query->latest()->paginate((int) $request->query('per_page', 15));

        foreach ($ujians as $u) {
            $u->sinkronisasiStatus();
        }

        return $this->successResponse($ujians);
    }

    /**
     * GURU: Status & statistik ujian real-time.
     */
    public function guruStatusRealtime(Request $request)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        if (!$guruId) {
            return $this->notFoundResponse('Profil guru tidak ditemukan.');
        }

        $ujians = Ujian::whereHas('pengampuKelas.guruMapel', function ($query) use ($guruId) {
            $query->where('guru_id', $guruId);
        })->get();

        $ujians->each(fn ($ujian) => $ujian->sinkronisasiStatus());

        $grupUjians = $ujians->groupBy(function ($ujian) {
            return $ujian->id_grup_ujian ? 'grup-' . $ujian->id_grup_ujian : 'ujian-' . $ujian->id;
        });

        $dataUjian = $grupUjians->map(function ($anggotaGrup) {
            $ujian = $anggotaGrup->sortByDesc('created_at')->first();
            return [
                'id'            => $ujian->id,
                'id_grup_ujian' => $ujian->id_grup_ujian,
                'status'        => $ujian->status,
                'status_label'  => $ujian->status_label,
                'jumlah_kelas'  => $anggotaGrup->count(),
            ];
        })->values();

        $stats = [
            'total'       => $grupUjians->count(),
            'draft'       => $grupUjians->filter(fn ($g) => $g->first()->status === 'draft')->count(),
            'scheduled'   => $grupUjians->filter(fn ($g) => $g->first()->status === 'scheduled')->count(),
            'published'   => $grupUjians->filter(fn ($g) => $g->first()->status === 'published')->count(),
            'completed'   => $grupUjians->filter(fn ($g) => $g->first()->status === 'completed')->count(),
            'unpublished' => $grupUjians->filter(fn ($g) => $g->first()->status === 'unpublished')->count(),
            'archived'    => $grupUjians->filter(fn ($g) => (bool) $g->first()->is_archived)->count(),
        ];

        return $this->successResponse([
            'stats'  => $stats,
            'ujians' => $dataUjian,
        ]);
    }

    /**
     * GURU: Opsi kelas pengampu untuk form ujian.
     */
    public function guruOptions(Request $request)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;

        $pengampuKelases = PengampuKelas::with(['guruMapel.mapel', 'kelas'])
            ->whereHas('guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })
            ->get()
            ->map(function ($pk) {
                return [
                    'pengampu_kelas_id' => $pk->id,
                    'kelas_id'          => $pk->kelas?->id,
                    'nama_kelas'        => $pk->kelas?->nama_kelas,
                    'tingkatan'         => $pk->kelas?->tingkatan,
                    'mapel_id'          => $pk->guruMapel?->mapel?->id,
                    'nama_mapel'        => $pk->guruMapel?->mapel?->nama_mapel,
                ];
            });

        return $this->successResponse($pengampuKelases);
    }

    /**
     * GURU: Detail ujian dan butir soal.
     */
    public function guruShow(Request $request, Ujian $ujian)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuruUjian($ujian, $guruId);

        $ujian->sinkronisasiStatus();

        $ujian->load([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'soals',
            'seleksiUjians',
        ]);

        $soals = $ujian->soals->map(function ($s) {
            return [
                'id'            => $s->id,
                'urutan'        => $s->urutan,
                'pertanyaan'    => $s->pertanyaan,
                'tipe_soal'     => $s->tipe_soal,
                'kesulitan'     => $s->kesulitan,
                'pilihan'       => $s->pilihan,
                'kunci_jawaban' => $s->kunci_jawaban,
                'bobot'         => $s->bobot,
                'gambar'        => $s->gambar,
                'gambar_url'    => $s->gambar ? asset('storage/' . $s->gambar) : null,
            ];
        });

        $grupUjians = $ujian->id_grup_ujian
            ? Ujian::with(['pengampuKelas.kelas'])
                ->where('id_grup_ujian', $ujian->id_grup_ujian)
                ->get()
                ->map(fn ($gu) => [
                    'id'                => $gu->id,
                    'pengampu_kelas_id' => $gu->pengampu_kelas_id,
                    'kelas'             => $gu->pengampuKelas?->kelas?->nama_kelas,
                    'sesi'              => $gu->sesi ?? 1,
                    'token'             => $gu->token,
                    'waktu_mulai_sesi'  => $gu->waktu_mulai_sesi,
                    'status'            => $gu->status,
                ])
            : collect();

        return $this->successResponse([
            'id'                => $ujian->id,
            'id_grup_ujian'     => $ujian->id_grup_ujian,
            'judul'             => $ujian->judul,
            'deskripsi'         => $ujian->deskripsi,
            'kisi_kisi'         => $ujian->kisi_kisi,
            'durasi_menit'      => $ujian->durasi_menit,
            'kkm'               => $ujian->kkm,
            'tampilkan_nilai'   => (bool) $ujian->tampilkan_nilai,
            'is_archived'       => (bool) $ujian->is_archived,
            'jenis_ujian'       => $ujian->jenis_ujian ?? 'reguler',
            'parent_ujian_id'   => $ujian->parent_ujian_id,
            'kebijakan_remidi'  => $ujian->kebijakan_remidi,
            'peserta_khusus'    => $ujian->peserta_khusus,
            'sesi'              => $ujian->sesi ?? 1,
            'token'             => $ujian->token,
            'unlock_code'       => $ujian->unlock_code,
            'status'            => $ujian->status,
            'status_label'      => $ujian->status_label,
            'waktu_mulai'       => $ujian->waktu_mulai,
            'waktu_mulai_sesi'  => $ujian->waktu_mulai_sesi,
            'deadline'          => $ujian->deadline,
            'dipublikasi_at'    => $ujian->dipublikasi_at,
            'mapel'             => $ujian->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
            'kelas'             => $ujian->pengampuKelas?->kelas?->nama_kelas,
            'pengampu_kelas_id' => $ujian->pengampu_kelas_id,
            'soals'             => $soals,
            'grup_ujians'       => $grupUjians,
        ]);
    }

    /**
     * GURU: Buat ujian baru + soal (mendukung multi-kelas, sesi, dan token).
     */
    public function guruStore(Request $request)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;

        $validator = Validator::make($request->all(), [
            'judul'               => ['required', 'string', 'max:200'],
            'deskripsi'           => ['nullable', 'string', 'max:1000'],
            'kisi_kisi'           => ['nullable', 'string', 'max:2000'],
            'pengampu_kelas_id'   => ['nullable'],
            'pengampu_kelas_ids'  => ['nullable', 'array', 'min:1'],
            'durasi_menit'        => ['required', 'integer', 'min:1', 'max:999'],
            'kkm'                 => ['required', 'numeric', 'min:0', 'max:100'],
            'tampilkan_nilai'     => ['nullable', 'boolean'],
            'waktu_mulai'         => ['nullable', 'date'],
            'deadline'            => ['nullable', 'date', 'after_or_equal:waktu_mulai'],
            'action'              => ['nullable', 'in:draft,publish,schedule'],
            'soals'               => ['required', 'array', 'min:1'],
            'soals.*.pertanyaan'  => ['required', 'string'],
            'soals.*.tipe_soal'   => ['required', 'in:single_choice,multiple_choice,essay,matching'],
            'soals.*.pilihan'     => ['nullable', 'array'],
            'soals.*.kunci_jawaban' => ['nullable'],
            'soals.*.kesulitan'   => ['nullable', 'in:0,1,2,3'],
            'soals.*.bobot'       => ['nullable', 'numeric', 'min:0'],
            'soals.*.urutan'      => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $validated = $validator->validated();

        $rawPengampu = $request->input('pengampu_kelas_ids') ?? $request->input('pengampu_kelas_id');
        if (empty($rawPengampu)) {
            return $this->validationErrorResponse([
                'pengampu_kelas_id' => ['Kelas pengampu wajib dipilih.'],
            ]);
        }

        $pengampuIds = array_values(array_unique(array_map('intval', (array) $rawPengampu)));
        $pengampuKelases = PengampuKelas::with('guruMapel.mapel')
            ->whereIn('id', $pengampuIds)
            ->whereHas('guruMapel', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })
            ->get();

        if ($pengampuKelases->count() !== count($pengampuIds)) {
            return $this->forbiddenResponse('Kelas pengampu tidak valid.');
        }

        $action = $request->input('action', 'draft');
        $status = 'draft';
        if ($action === 'publish') {
            $status = 'published';
        } elseif ($action === 'schedule') {
            if (empty($validated['waktu_mulai']) || empty($validated['deadline'])) {
                return $this->errorResponse('Waktu mulai dan deadline wajib diisi jika menjadwalkan ujian.', 422);
            }
            $status = 'scheduled';
        }

        $grupUjianId = (string) Str::uuid();
        $createdUjians = [];

        DB::transaction(function () use ($request, $validated, $pengampuKelases, $status, $action, $grupUjianId, &$createdUjians) {
            $dataSoals = [];

            foreach ($request->input('soals', []) as $index => $data) {
                $gambar = null;
                if ($request->hasFile("soals.$index.gambar")) {
                    $gambar = $request->file("soals.$index.gambar")->store('soal-images', 'public');
                }

                $pilihan = $data['pilihan'] ?? null;
                if (!is_array($pilihan)) {
                    $pilihan = null;
                }

                $kunci = $data['kunci_jawaban'] ?? [];
                if (!is_array($kunci)) {
                    $kunci = $kunci !== null && $kunci !== '' ? [$kunci] : [];
                }
                if (($data['tipe_soal'] ?? '') !== 'essay') {
                    $kunci = $this->normalizeKunciPilihan($kunci);
                }

                $dataSoals[] = [
                    'pertanyaan'    => $data['pertanyaan'],
                    'tipe_soal'     => $data['tipe_soal'],
                    'pilihan'       => $pilihan,
                    'kunci_jawaban' => $kunci,
                    'gambar'        => $gambar,
                    'kesulitan'     => isset($data['kesulitan']) ? (int) $data['kesulitan'] : 1,
                    'bobot'         => (float) ($data['bobot'] ?? 10),
                    'urutan'        => $data['urutan'] ?? ($index + 1),
                ];
            }

            $sesiInput = $request->input('sesi_kelas', []);
            $tokenPerSesi = [];
            $unlockCodeGrup = strtoupper(Str::random(6));
            $waktuMulaiBase = !empty($validated['waktu_mulai'])
                ? Carbon::parse($validated['waktu_mulai'])
                : ($action === 'publish' ? now() : null);
            $deadlineUjian = !empty($validated['deadline'])
                ? Carbon::parse($validated['deadline'])
                : ($action === 'publish' && $waktuMulaiBase ? $waktuMulaiBase->copy()->addDay() : null);

            foreach ($pengampuKelases as $pengampuKelas) {
                $nomorSesi = max(1, (int) ($sesiInput[$pengampuKelas->id] ?? 1));
                if (!isset($tokenPerSesi[$nomorSesi])) {
                    $tokenPerSesi[$nomorSesi] = 'S' . $nomorSesi . '-' . strtoupper(Str::random(4));
                }

                $waktuMulaiSesi = $waktuMulaiBase
                    ? $waktuMulaiBase->copy()->addMinutes(($nomorSesi - 1) * (int) $validated['durasi_menit'])
                    : null;

                $ujian = Ujian::create([
                    'id_grup_ujian'     => $grupUjianId,
                    'judul'             => $validated['judul'],
                    'deskripsi'         => $validated['deskripsi'] ?? null,
                    'kisi_kisi'         => $validated['kisi_kisi'] ?? null,
                    'pengampu_kelas_id' => $pengampuKelas->id,
                    'sesi'              => $nomorSesi,
                    'durasi_menit'      => $validated['durasi_menit'],
                    'kkm'               => $validated['kkm'],
                    'tampilkan_nilai'   => $request->boolean('tampilkan_nilai'),
                    'waktu_mulai'       => $waktuMulaiBase,
                    'waktu_mulai_sesi'  => $waktuMulaiSesi,
                    'deadline'          => $deadlineUjian,
                    'status'            => $status,
                    'token'             => $tokenPerSesi[$nomorSesi],
                    'unlock_code'       => $unlockCodeGrup,
                    'dipublikasi_at'    => $action === 'publish' ? now() : null,
                ]);

                foreach ($dataSoals as $dataSoal) {
                    Soal::create([
                        'pertanyaan'    => $dataSoal['pertanyaan'],
                        'tipe_soal'     => $dataSoal['tipe_soal'],
                        'pilihan'       => $dataSoal['pilihan'],
                        'kunci_jawaban' => $dataSoal['kunci_jawaban'],
                        'gambar'        => $dataSoal['gambar'],
                        'kesulitan'     => $dataSoal['kesulitan'],
                        'bobot'         => $dataSoal['bobot'],
                        'urutan'        => $dataSoal['urutan'],
                        'ujian_id'      => $ujian->id,
                        'quiz_id'       => null,
                        'tugas_id'      => null,
                    ]);
                }

                $createdUjians[] = $ujian;
            }
        });

        if ($action === 'publish') {
            foreach ($createdUjians as $u) {
                $pk = $pengampuKelases->firstWhere('id', $u->pengampu_kelas_id);
                if ($pk) {
                    $mapelNama = $pk->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran';
                    Notifikasi::kirimKeKelas(
                        $pk->kelas_id,
                        "Ujian Baru Dipublikasikan: {$u->judul}",
                        "Ujian evaluasi {$mapelNama} telah dipublikasikan dan dapat diakses sesuai jadwal.",
                        'ujian_baru',
                        route('siswa.dashboard'),
                        $u->id
                    );
                }
            }
        }

        $firstUjian = $createdUjians[0] ?? null;

        return $this->createdResponse(
            count($createdUjians) === 1 ? $firstUjian : $createdUjians,
            'Ujian berhasil dibuat.'
        );
    }

    /**
     * GURU: Perbarui ujian (beserta pembaruan soal jika dikirim).
     */
    public function guruUpdate(Request $request, Ujian $ujian)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuruUjian($ujian, $guruId);

        $validator = Validator::make($request->all(), [
            'judul'                 => ['required', 'string', 'max:200'],
            'deskripsi'             => ['nullable', 'string', 'max:1000'],
            'kisi_kisi'             => ['nullable', 'string', 'max:2000'],
            'pengampu_kelas_id'     => ['nullable', 'exists:pengampu_kelas,id'],
            'durasi_menit'          => ['required', 'integer', 'min:1', 'max:999'],
            'kkm'                   => ['required', 'numeric', 'min:0', 'max:100'],
            'tampilkan_nilai'       => ['nullable', 'boolean'],
            'waktu_mulai'           => ['nullable', 'date'],
            'deadline'              => ['nullable', 'date', 'after_or_equal:waktu_mulai'],
            'soals'                 => ['nullable', 'array', 'min:1'],
            'soals.*.pertanyaan'    => ['required_with:soals', 'string'],
            'soals.*.tipe_soal'     => ['required_with:soals', 'in:single_choice,multiple_choice,essay,matching'],
            'soals.*.pilihan'       => ['nullable', 'array'],
            'soals.*.kunci_jawaban' => ['nullable'],
            'soals.*.kesulitan'     => ['nullable', 'in:0,1,2,3'],
            'soals.*.bobot'         => ['nullable', 'numeric', 'min:0'],
            'soals.*.urutan'        => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $validated = $validator->validated();

        DB::transaction(function () use ($request, $validated, $ujian) {
            $updatePayload = [
                'judul'        => $validated['judul'],
                'deskripsi'    => $validated['deskripsi'] ?? null,
                'kisi_kisi'    => $validated['kisi_kisi'] ?? null,
                'durasi_menit' => $validated['durasi_menit'],
                'kkm'          => $validated['kkm'],
                'waktu_mulai'  => $validated['waktu_mulai'] ?? $ujian->waktu_mulai,
                'deadline'     => $validated['deadline'] ?? $ujian->deadline,
            ];

            if ($request->has('tampilkan_nilai')) {
                $updatePayload['tampilkan_nilai'] = $request->boolean('tampilkan_nilai');
            }

            if (!empty($validated['pengampu_kelas_id'])) {
                $updatePayload['pengampu_kelas_id'] = $validated['pengampu_kelas_id'];
            }

            $ujian->update($updatePayload);

            // Jika payload menyertakan array soals, perbarui soal in-place
            if ($request->filled('soals') && is_array($request->input('soals'))) {
                $existingSoals = $ujian->soals()->orderBy('urutan')->orderBy('id')->get()->values();
                $usedSoalIds = [];

                foreach ($request->input('soals', []) as $idx => $data) {
                    $existing = $existingSoals->get($idx);
                    $gambar = $existing?->gambar;

                    if ($request->hasFile("soals.$idx.gambar")) {
                        $gambar = $request->file("soals.$idx.gambar")->store('soal-images', 'public');
                    }

                    $pilihan = is_array($data['pilihan'] ?? null) ? $data['pilihan'] : ($existing?->pilihan ?? []);
                    $kunci = $data['kunci_jawaban'] ?? ($existing?->kunci_jawaban ?? []);
                    if (!is_array($kunci)) {
                        $kunci = $kunci !== null && $kunci !== '' ? [$kunci] : [];
                    }
                    if (($data['tipe_soal'] ?? '') !== 'essay') {
                        $kunci = $this->normalizeKunciPilihan($kunci);
                    }

                    $kesulitan = isset($data['kesulitan']) ? (int) $data['kesulitan'] : ($existing?->kesulitan ?? 1);
                    $bobot = $kesulitan === 0 ? 0 : (float) ($data['bobot'] ?? $existing?->bobot ?? 10);

                    $soalItem = [
                        'pertanyaan'    => $data['pertanyaan'],
                        'tipe_soal'     => $data['tipe_soal'],
                        'pilihan'       => $pilihan,
                        'kunci_jawaban' => $kunci,
                        'gambar'        => $gambar,
                        'kesulitan'     => $kesulitan,
                        'bobot'         => $bobot,
                        'urutan'        => (int) ($data['urutan'] ?? ($idx + 1)),
                        'quiz_id'       => null,
                        'tugas_id'      => null,
                    ];

                    if ($existing) {
                        $existing->update($soalItem);
                        $usedSoalIds[] = $existing->id;
                    } else {
                        $created = $ujian->soals()->create($soalItem);
                        $usedSoalIds[] = $created->id;
                    }
                }

                if (!$ujian->hanyaBisaEditBobot()) {
                    $ujian->soals()->whereNotIn('id', $usedSoalIds)->delete();
                } else {
                    // Rekalkulasi nilai siswa yang sudah selesai jika mengedit bobot
                    $sesiSelesaiList = SeleksiUjian::where('ujian_id', $ujian->id)
                        ->where(fn ($q) => $q->where('status', 'selesai')->orWhereNotNull('waktu_selesai'))
                        ->get();
                    foreach ($sesiSelesaiList as $sesiSiswa) {
                        $this->rekalkulasiNilaiSiswa($ujian->id, $sesiSiswa->siswa_id);
                    }
                }
            }
        });

        return $this->successResponse($ujian->fresh('soals'), 'Ujian berhasil diperbarui.');
    }

    /**
     * GURU: Hapus ujian (dilengkapi proteksi jika sudah ada pengerjaan siswa).
     */
    public function guruDestroy(Request $request, Ujian $ujian)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuruUjian($ujian, $guruId);

        $grupUjians = $ujian->id_grup_ujian
            ? Ujian::where('id_grup_ujian', $ujian->id_grup_ujian)->get()
            : collect([$ujian]);

        $ujianIds = $grupUjians->pluck('id');
        $soals = Soal::whereIn('ujian_id', $ujianIds)->get();
        $soalIds = $soals->pluck('id');

        $adaPengerjaan = SeleksiUjian::whereIn('ujian_id', $ujianIds)->exists();
        if (!$adaPengerjaan && $soalIds->isNotEmpty()) {
            $adaPengerjaan = Menjawab::whereIn('soal_id', $soalIds)->exists();
        }

        if ($adaPengerjaan && !$request->boolean('force')) {
            return $this->errorResponse('Ujian tidak dapat dihapus karena sudah memiliki data pengerjaan siswa.', 422);
        }

        $gambarSoal = $soals->pluck('gambar')->filter()->unique()->values();

        DB::transaction(function () use ($soals, $grupUjians) {
            foreach ($soals as $soal) {
                $soal->delete();
            }
            foreach ($grupUjians as $anggota) {
                $anggota->delete();
            }
        });

        foreach ($gambarSoal as $gambar) {
            if (!Soal::where('gambar', $gambar)->exists() && Storage::disk('public')->exists($gambar)) {
                Storage::disk('public')->delete($gambar);
            }
        }

        return $this->successResponse(null, 'Ujian berhasil dihapus.');
    }

    /**
     * GURU: Publikasi ujian (mendukung grup ujian & token sesi).
     */
    public function guruPublish(Request $request, Ujian $ujian)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuruUjian($ujian, $guruId);

        $grupUjians = $ujian->id_grup_ujian
            ? Ujian::with('pengampuKelas.guruMapel.mapel')->where('id_grup_ujian', $ujian->id_grup_ujian)->get()
            : collect([$ujian]);

        $waktuMulai = $request->filled('waktu_mulai')
            ? Carbon::parse($request->waktu_mulai)
            : ($ujian->waktu_mulai ? Carbon::parse($ujian->waktu_mulai) : now());
        $deadline = $request->filled('deadline')
            ? Carbon::parse($request->deadline)
            : ($ujian->deadline ?? $waktuMulai->copy()->addDay());

        $isScheduled = $request->input('tipe_publikasi') === 'jadwal' && $waktuMulai->isFuture();
        $targetStatus = $isScheduled ? 'scheduled' : 'published';

        DB::transaction(function () use ($grupUjians, $waktuMulai, $deadline, $targetStatus) {
            $tokenPerSesi = [];

            foreach ($grupUjians as $anggota) {
                $sesi = (int) ($anggota->sesi ?: 1);
                if (!isset($tokenPerSesi[$sesi])) {
                    $tokenPerSesi[$sesi] = $anggota->token ?: ('S' . $sesi . '-' . strtoupper(Str::random(4)));
                }

                $mulaiSesi = $waktuMulai->copy()->addMinutes(($sesi - 1) * (int) $anggota->durasi_menit);

                $anggota->update([
                    'status'           => $targetStatus,
                    'waktu_mulai'      => $waktuMulai,
                    'waktu_mulai_sesi' => $mulaiSesi,
                    'deadline'         => $deadline,
                    'token'            => $tokenPerSesi[$sesi],
                    'dipublikasi_at'   => $targetStatus === 'published' ? now() : null,
                ]);
            }
        });

        if ($targetStatus === 'published') {
            foreach ($grupUjians as $anggota) {
                $kelasId = $anggota->pengampuKelas?->kelas_id;
                if ($kelasId) {
                    $mapelNama = $anggota->pengampuKelas?->guruMapel?->mapel?->nama_mapel ?? 'Mata Pelajaran';
                    Notifikasi::kirimKeKelas(
                        $kelasId,
                        "Ujian Baru Dipublikasikan: {$anggota->judul}",
                        "Ujian evaluasi {$mapelNama} telah dipublikasikan dan dapat diakses sesuai jadwal.",
                        'ujian_baru',
                        route('siswa.dashboard'),
                        $anggota->id
                    );
                }
            }
        }

        return $this->successResponse($ujian->fresh(), 'Ujian berhasil dipublikasikan.');
    }

    /**
     * GURU: Jadwalkan ujian.
     */
    public function guruSchedule(Request $request, Ujian $ujian)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuruUjian($ujian, $guruId);

        $validator = Validator::make($request->all(), [
            'waktu_mulai' => ['required', 'date'],
            'deadline'    => ['required', 'date', 'after:waktu_mulai'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $mulai = Carbon::parse($request->waktu_mulai);
        $deadline = Carbon::parse($request->deadline);

        if (now()->greaterThan($deadline)) {
            $status = 'completed';
        } elseif (now()->lt($mulai)) {
            $status = 'scheduled';
        } else {
            $status = 'published';
        }

        $sesi = (int) ($ujian->sesi ?: 1);
        $mulaiSesi = $mulai->copy()->addMinutes(($sesi - 1) * (int) $ujian->durasi_menit);

        $ujian->update([
            'waktu_mulai'      => $mulai,
            'waktu_mulai_sesi' => $mulaiSesi,
            'deadline'         => $deadline,
            'status'           => $status,
            'token'            => $ujian->token ?: ('S' . $sesi . '-' . strtoupper(Str::random(4))),
            'dipublikasi_at'   => $status === 'published' ? ($ujian->dipublikasi_at ?? now()) : null,
        ]);

        return $this->successResponse($ujian, 'Ujian berhasil dijadwalkan.');
    }

    /**
     * GURU: Tarik publikasi ujian (Unpublish).
     */
    public function guruUnpublish(Request $request, Ujian $ujian)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuruUjian($ujian, $guruId);

        $grupUjians = $ujian->id_grup_ujian
            ? Ujian::where('id_grup_ujian', $ujian->id_grup_ujian)->get()
            : collect([$ujian]);

        DB::transaction(function () use ($grupUjians) {
            foreach ($grupUjians as $anggota) {
                $anggota->update(['status' => 'unpublished']);
            }
        });

        return $this->successResponse($ujian->fresh(), 'Ujian berhasil ditarik dari publikasi. Nilai sesi sebelumnya tetap tersimpan di rekap.');
    }

    /**
     * GURU: Duplikasi instrumen ujian ke draf baru.
     */
    public function guruDuplicate(Request $request, Ujian $ujian)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuruUjian($ujian, $guruId);

        $grupUjians = $ujian->id_grup_ujian
            ? Ujian::with('soals')->where('id_grup_ujian', $ujian->id_grup_ujian)->get()
            : collect([$ujian->load('soals')]);

        $duplicatedList = [];

        DB::transaction(function () use ($grupUjians, &$duplicatedList) {
            $grupBaruId = (string) Str::uuid();

            foreach ($grupUjians as $anggota) {
                $ujianBaru = $anggota->replicate([
                    'waktu_mulai',
                    'deadline',
                    'dipublikasi_at',
                    'created_at',
                    'updated_at',
                ]);

                $ujianBaru->judul = $anggota->judul . ' (Salinan)';
                $ujianBaru->status = 'draft';
                $ujianBaru->id_grup_ujian = $grupBaruId;
                $ujianBaru->waktu_mulai = null;
                $ujianBaru->deadline = null;
                $ujianBaru->dipublikasi_at = null;
                $ujianBaru->save();

                foreach ($anggota->soals as $soal) {
                    $soalBaru = $soal->replicate(['created_at', 'updated_at']);
                    $soalBaru->ujian_id = $ujianBaru->id;
                    $soalBaru->save();
                }

                $duplicatedList[] = $ujianBaru;
            }
        });

        return $this->createdResponse(
            count($duplicatedList) === 1 ? $duplicatedList[0] : $duplicatedList,
            'Instrumen ujian berhasil diduplikasi menjadi draf baru.'
        );
    }

    /**
     * GURU: Arsipkan / keluarkan ujian dari folder arsip.
     */
    public function guruToggleArchive(Request $request, Ujian $ujian)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuruUjian($ujian, $guruId);

        $grupUjians = $ujian->id_grup_ujian
            ? Ujian::where('id_grup_ujian', $ujian->id_grup_ujian)->get()
            : collect([$ujian]);

        $statusArsipBaru = !$ujian->is_archived;

        DB::transaction(function () use ($grupUjians, $statusArsipBaru) {
            foreach ($grupUjians as $anggota) {
                $anggota->update(['is_archived' => $statusArsipBaru]);
            }
        });

        $pesan = $statusArsipBaru
            ? 'Ujian berhasil dipindahkan ke folder Diarsipkan.'
            : 'Ujian berhasil dikeluarkan dari folder Diarsipkan.';

        return $this->successResponse([
            'is_archived' => $statusArsipBaru,
        ], $pesan);
    }

    /**
     * GURU: Data monitoring live ujian beserta daftar sesi, token, unlock code, dan peserta.
     */
    public function guruMonitoringData(Request $request, Ujian $ujian)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuruUjian($ujian, $guruId);

        $grupUjians = $ujian->id_grup_ujian
            ? Ujian::with('pengampuKelas.kelas')->where('id_grup_ujian', $ujian->id_grup_ujian)->get()->keyBy('id')
            : collect([$ujian->id => $ujian->load('pengampuKelas.kelas')]);

        $tokenPerSesi = [];
        foreach ($grupUjians as $item) {
            $s = (int) ($item->sesi ?: 1);
            if (!isset($tokenPerSesi[$s])) {
                $tokenPerSesi[$s] = $item->token ?: ('S' . $s . '-' . strtoupper(Str::random(4)));
            }
            if (!$item->token || $item->token !== $tokenPerSesi[$s]) {
                $item->update([
                    'sesi'             => $s,
                    'token'            => $tokenPerSesi[$s],
                    'waktu_mulai_sesi' => $item->waktu_mulai_sesi ?? $item->waktu_mulai ?? now(),
                ]);
            }
        }

        $daftarSesi = $grupUjians->groupBy('sesi')->sortKeys()->map(function ($items, $nomorSesi) {
            $first = $items->first();
            $mulai = $first->waktu_mulai_sesi ?? $first->waktu_mulai ?? now();
            $selesai = Carbon::parse($mulai)->addMinutes((int) $first->durasi_menit);

            return [
                'sesi'        => (int) $nomorSesi,
                'token'       => $first->token,
                'kelas_list'  => $items->map(fn ($u) => $u->pengampuKelas?->kelas?->nama_kelas ?? '-')->values()->all(),
                'jam_mulai'   => Carbon::parse($mulai)->format('H:i'),
                'jam_selesai' => $selesai->format('H:i'),
            ];
        })->values();

        $unlockCode = $ujian->unlock_code;
        if (empty($unlockCode)) {
            $unlockCode = strtoupper(Str::random(6));
            if ($ujian->id_grup_ujian) {
                Ujian::where('id_grup_ujian', $ujian->id_grup_ujian)->update(['unlock_code' => $unlockCode]);
            } else {
                $ujian->update(['unlock_code' => $unlockCode]);
            }
        }

        $ujianIds = $grupUjians->keys();
        $totalSoal = $ujian->soals()->count();
        $soalIds = Soal::whereIn('ujian_id', $ujianIds)->pluck('id');

        $sesiSiswa = SeleksiUjian::with(['siswa.user'])
            ->whereIn('ujian_id', $ujianIds)
            ->get();

        $siswaIds = $sesiSiswa->pluck('siswa_id')->filter()->unique();

        $jawabanCounts = Menjawab::whereIn('soal_id', $soalIds)
            ->whereIn('siswa_id', $siswaIds)
            ->select('siswa_id', DB::raw('count(distinct soal_id) as total'))
            ->groupBy('siswa_id')
            ->pluck('total', 'siswa_id');

        $pesertaList = $sesiSiswa->map(function ($sesi) use ($totalSoal, $jawabanCounts, $grupUjians) {
            $ujianSiswa = $grupUjians->get($sesi->ujian_id);
            $siswa = $sesi->siswa;
            $user = $siswa?->user;

            $nama = $user?->name ?? $siswa?->nama_siswa ?? ('Siswa #' . $sesi->siswa_id);
            $nisn = $siswa?->nis ?? $user?->username ?? '-';
            $namaKelas = $ujianSiswa?->pengampuKelas?->kelas?->nama_kelas ?? '-';
            $nomorSesi = $ujianSiswa?->sesi ?? 1;

            $totalDijawab = $jawabanCounts[$sesi->siswa_id] ?? 0;
            $progresPersen = $totalSoal > 0 ? round(($totalDijawab / $totalSoal) * 100) : 0;

            $sudahSelesai = ($sesi->status === 'selesai') || !is_null($sesi->waktu_selesai);

            $sisaDetik = 0;
            if (!$sudahSelesai) {
                $durasiMenit = (int) ($ujianSiswa?->durasi_menit ?? 60);
                $waktuAwalSesi = $ujianSiswa?->waktu_mulai_sesi ?? $ujianSiswa?->waktu_mulai ?? $sesi->waktu_mulai ?? now();
                $batasAkhirSesi = Carbon::parse($waktuAwalSesi)->addMinutes($durasiMenit);
                $sisaDetik = max(0, (int) now()->diffInSeconds($batasAkhirSesi, false));
            }

            if ($sudahSelesai) {
                $statusKey = 'selesai';
                $statusLabel = 'Selesai';
            } elseif ($sesi->status === 'terkunci') {
                $statusKey = 'terkunci';
                $statusLabel = 'Terkunci';
            } else {
                $statusKey = 'sedang_mengerjakan';
                $statusLabel = $sisaDetik <= 0 ? 'Waktu Habis' : 'Mengerjakan';
            }

            return [
                'seleksi_id'        => $sesi->id,
                'ujian_id'          => $sesi->ujian_id,
                'siswa_id'          => $sesi->siswa_id,
                'nama'              => $nama,
                'nisn'              => $nisn,
                'kelas'             => $namaKelas,
                'sesi'              => $nomorSesi,
                'status'            => $statusKey,
                'status_label'      => $statusLabel,
                'total_dijawab'     => $totalDijawab,
                'progres_persen'    => $progresPersen,
                'sisa_detik'        => (int) floor($sisaDetik),
                'total_pelanggaran' => (int) ($sesi->pelanggaran_count ?? 0),
            ];
        });

        $stats = [
            'total_peserta' => $pesertaList->count(),
            'aktif'         => $pesertaList->where('status', 'sedang_mengerjakan')->where('sisa_detik', '>', 0)->count(),
            'selesai'       => $pesertaList->where('status', 'selesai')->count(),
            'terkunci'      => $pesertaList->where('status', 'terkunci')->count(),
            'pelanggaran'   => $pesertaList->where('total_pelanggaran', '>', 0)->count(),
        ];

        return $this->successResponse([
            'total_soal'  => $totalSoal,
            'unlock_code' => $unlockCode,
            'daftar_sesi' => $daftarSesi,
            'stats'       => $stats,
            'pesertas'    => $pesertaList->values(),
        ]);
    }

    /**
     * GURU: Buka kunci sesi siswa yang terblokir anti-cheat.
     */
    public function guruUnlockPeserta(Request $request, Ujian $ujian, SeleksiUjian $seleksi)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuruUjian($ujian, $guruId);

        $seleksi->update([
            'status'            => 'sedang_mengerjakan',
            'pelanggaran_count' => 0,
        ]);

        return $this->successResponse($seleksi, 'Sesi siswa berhasil dibuka kembali.');
    }

    /**
     * GURU: Tarik paksa ujian siswa dari panel monitoring.
     */
    public function guruForceSubmit(Request $request, Ujian $ujian, $seleksi)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuruUjian($ujian, $guruId);

        $seleksi = $seleksi instanceof SeleksiUjian ? $seleksi : SeleksiUjian::findOrFail($seleksi);

        if ($seleksi->status === 'selesai') {
            return $this->successResponse($seleksi, 'Ujian siswa sudah selesai.');
        }

        DB::transaction(function () use ($ujian, $seleksi) {
            $siswaId = $seleksi->siswa_id;
            $ujianSiswa = Ujian::with('soals')->find($seleksi->ujian_id) ?? $ujian->load('soals');
            $soals = $ujianSiswa->soals;
            $soalIds = $soals->pluck('id');

            $jawabanSiswa = Menjawab::where('siswa_id', $siswaId)
                ->whereIn('soal_id', $soalIds)
                ->get()
                ->keyBy('soal_id');

            $totalBobot = $soals->sum('bobot') ?: 100;
            $skorDiperoleh = 0;

            foreach ($soals as $soal) {
                $skorDiperoleh += $this->hitungSkorSatuSoal($soal, $jawabanSiswa->get($soal->id))['skor'];
            }

            $nilaiAkhir = $totalBobot > 0 ? min(100, round(($skorDiperoleh / $totalBobot) * 100, 2)) : 0;
            $waktuAwal = $seleksi->waktu_mulai ?? $seleksi->created_at ?? now();
            $durasiDetik = (int) Carbon::parse($waktuAwal)->diffInSeconds(now());

            $seleksi->update([
                'status'        => 'selesai',
                'waktu_selesai' => now(),
                'nilai_awal'    => $nilaiAkhir,
                'nilai_akhir'   => $nilaiAkhir,
            ]);

            Nilai::updateOrCreate(
                [
                    'seleksi_ujian_id' => $seleksi->id,
                    'siswa_id'         => $siswaId,
                ],
                [
                    'nilai'        => $nilaiAkhir,
                    'status'       => 'selesai',
                    'durasi'       => $durasiDetik,
                    'tugas_id'     => null,
                    'quiz_id'      => null,
                    'file_jawaban' => null,
                ]
            );
        });

        return $this->successResponse($seleksi->fresh(), 'Ujian siswa berhasil ditarik paksa dan dinilai.');
    }

    /**
     * GURU: Buka kembali ujian siswa yang sudah selesai (Reopen Peserta).
     */
    public function guruReopenPeserta(Request $request, Ujian $ujian, SeleksiUjian $seleksi)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuruUjian($ujian, $guruId);

        $ujianMilikSiswa = Ujian::findOrFail($seleksi->ujian_id);
        if ($ujian->id_grup_ujian) {
            abort_unless($ujianMilikSiswa->id_grup_ujian === $ujian->id_grup_ujian, 403);
        } else {
            abort_unless($ujianMilikSiswa->id === $ujian->id, 403);
        }

        $waktuMulaiSesi = Carbon::parse($ujianMilikSiswa->waktu_mulai_sesi ?? $ujianMilikSiswa->waktu_mulai ?? now());
        $batasAkhirSesi = $waktuMulaiSesi->copy()->addMinutes((int) $ujianMilikSiswa->durasi_menit);
        $sisaDetik = (int) now()->diffInSeconds($batasAkhirSesi, false);

        if ($sisaDetik <= 0) {
            return $this->errorResponse('Waktu sesi untuk kelas siswa ini sudah habis. Silakan atur ulang jam sesi terlebih dahulu jika ingin memberi waktu tambahan.', 422);
        }

        DB::transaction(function () use ($seleksi) {
            Nilai::where('seleksi_ujian_id', $seleksi->id)->delete();

            $seleksi->update([
                'status'            => 'sedang_mengerjakan',
                'waktu_selesai'     => null,
                'nilai_awal'        => 0,
                'nilai_akhir'       => 0,
                'pelanggaran_count' => 0,
            ]);
        });

        return $this->successResponse($seleksi->fresh(), 'Ujian siswa berhasil dibuka kembali. Seluruh jawaban sebelumnya tetap tersimpan.');
    }

    /**
     * GURU: Refresh Kode Buka Blokir (Unlock Code).
     */
    public function guruRefreshUnlockCode(Request $request, Ujian $ujian)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuruUjian($ujian, $guruId);

        $kodeBaru = strtoupper(Str::random(6));

        if ($ujian->id_grup_ujian) {
            Ujian::where('id_grup_ujian', $ujian->id_grup_ujian)->update(['unlock_code' => $kodeBaru]);
        } else {
            $ujian->update(['unlock_code' => $kodeBaru]);
        }

        return $this->successResponse([
            'unlock_code' => $kodeBaru,
        ], "Kode buka blokir berhasil diperbarui menjadi {$kodeBaru}.");
    }

    /**
     * GURU: Reset token untuk sesi tertentu.
     */
    public function guruResetToken(Request $request, Ujian $ujian)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuruUjian($ujian, $guruId);

        $nomorSesi = (int) $request->input('sesi', $ujian->sesi ?? 1);
        $mulaiSekarang = (bool) $request->input('mulai_sekarang', false);

        $tokenBaru = 'S' . $nomorSesi . '-' . strtoupper(Str::random(4));

        $query = $ujian->id_grup_ujian
            ? Ujian::where('id_grup_ujian', $ujian->id_grup_ujian)->where('sesi', $nomorSesi)
            : Ujian::where('id', $ujian->id);

        $updateData = ['token' => $tokenBaru];
        if ($mulaiSekarang) {
            $updateData['waktu_mulai_sesi'] = now();
        }

        $query->update($updateData);

        $waktuMulaiSesi = $mulaiSekarang ? now() : ($query->first()?->waktu_mulai_sesi ?? now());
        $waktuSelesaiSesi = Carbon::parse($waktuMulaiSesi)->addMinutes((int) $ujian->durasi_menit);

        return $this->successResponse([
            'sesi'        => $nomorSesi,
            'token_baru'  => $tokenBaru,
            'jam_mulai'   => Carbon::parse($waktuMulaiSesi)->format('H:i'),
            'jam_selesai' => $waktuSelesaiSesi->format('H:i'),
        ], "Token Sesi {$nomorSesi} berhasil diperbarui menjadi {$tokenBaru}.");
    }

    /**
     * GURU: Mengatur pembagian Sesi untuk masing-masing kelas dalam satu grup ujian.
     */
    public function guruAturSesiKelas(Request $request, Ujian $ujian)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuruUjian($ujian, $guruId);

        $sesiMap = $request->input('sesi_ujian', []);
        $jamSesiMap = $request->input('jam_sesi', []);

        $grupUjians = $ujian->id_grup_ujian
            ? Ujian::where('id_grup_ujian', $ujian->id_grup_ujian)->get()
            : collect([$ujian]);

        DB::transaction(function () use ($grupUjians, $sesiMap, $jamSesiMap, $ujian) {
            foreach ($grupUjians as $item) {
                if (isset($sesiMap[$item->id])) {
                    $item->sesi = max(1, (int) $sesiMap[$item->id]);
                    $item->save();
                }
            }

            $groupedBySesi = $grupUjians->groupBy('sesi');
            $tanggalDasar = ($ujian->waktu_mulai ?? now())->format('Y-m-d');

            foreach ($groupedBySesi as $nomorSesi => $items) {
                $existingToken = $items->pluck('token')->filter(fn ($t) => str_starts_with((string) $t, "S{$nomorSesi}-"))->first();
                $tokenSesi = $existingToken ?: ('S' . $nomorSesi . '-' . strtoupper(Str::random(4)));

                if (!empty($jamSesiMap[$nomorSesi])) {
                    $waktuMulaiSesi = Carbon::parse($tanggalDasar . ' ' . $jamSesiMap[$nomorSesi]);
                } else {
                    $baseStart = $ujian->waktu_mulai ?? now();
                    $waktuMulaiSesi = Carbon::parse($baseStart)->copy()->addMinutes(((int) $nomorSesi - 1) * (int) $ujian->durasi_menit);
                }

                foreach ($items as $item) {
                    $item->update([
                        'token'            => $tokenSesi,
                        'waktu_mulai_sesi' => $waktuMulaiSesi,
                    ]);
                }
            }
        });

        return $this->successResponse(null, 'Pembagian sesi kelas dan jadwal token berhasil diperbarui.');
    }

    /**
     * GURU: Rekap Nilai Satu Pintu (Seluruh Siswa di Kelas Target).
     */
    public function guruRekap(Request $request, Ujian $ujian)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuruUjian($ujian, $guruId);

        $ujian->load([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.kelas',
            'soals',
        ]);

        $grupUjians = $ujian->id_grup_ujian
            ? Ujian::with(['pengampuKelas.kelas', 'soals'])->where('id_grup_ujian', $ujian->id_grup_ujian)->get()
            : collect([$ujian]);

        $ujianIds = $grupUjians->pluck('id');
        $kelasIds = $grupUjians->pluck('pengampuKelas.kelas_id')->filter()->unique();

        $mapelUjian = $ujian->pengampuKelas?->guruMapel?->mapel;
        $semuaSiswa = Siswa::with(['user', 'anggotaKelases.kelas'])
            ->forMapel($mapelUjian)
            ->whereHas('anggotaKelases', function ($sub) use ($kelasIds) {
                $sub->whereIn('kelas_id', $kelasIds);
            })
            ->get()
            ->unique('id');

        if (in_array($ujian->jenis_ujian, ['remidi', 'susulan']) && !empty($ujian->peserta_khusus)) {
            $pesertaKhususIds = array_map('intval', $ujian->peserta_khusus);
            $semuaSiswa = $semuaSiswa->whereIn('id', $pesertaKhususIds)->values();
        }

        $sesiMap = SeleksiUjian::whereIn('ujian_id', $ujianIds)->get()->keyBy('siswa_id');
        $soalIds = Soal::whereIn('ujian_id', $ujianIds)->pluck('id');
        $jawabanAll = Menjawab::whereIn('soal_id', $soalIds)->get()->groupBy('siswa_id');

        $kkm = (float) ($ujian->kkm ?? 75);

        $rekapSiswa = $semuaSiswa->map(function ($siswa) use ($grupUjians, $sesiMap, $jawabanAll, $kkm, $ujian) {
            $sesi = $sesiMap->get($siswa->id);
            $ujianSiswa = $sesi
                ? $grupUjians->firstWhere('id', $sesi->ujian_id)
                : $grupUjians->first();

            $soals = $ujianSiswa ? $ujianSiswa->soals : collect();
            $soalValid = $soals->filter(fn ($s) => (float) ($s->bobot ?? 0) > 0);
            $soalPG = $soalValid->whereIn('tipe_soal', ['single_choice', 'multiple_choice']);
            $soalEssay = $soalValid->where('tipe_soal', 'essay');

            $jawabanSiswa = ($jawabanAll->get($siswa->id) ?? collect())->keyBy('soal_id');

            $sudahSelesai = $sesi && ($sesi->status === 'selesai' || !is_null($sesi->waktu_selesai));
            $sedangMengerjakan = $sesi && !$sudahSelesai;

            $pgBenar = 0;
            $pgSalah = 0;
            $skorPG = 0;
            $bobotMaksPG = $soalPG->sum('bobot');

            foreach ($soalPG as $s) {
                $j = $jawabanSiswa->get($s->id);
                $hasil = $this->hitungSkorSatuSoal($s, $j);
                $skorPG += $hasil['skor'];
                if ($hasil['is_benar']) {
                    $pgBenar++;
                } else {
                    $pgSalah++;
                }
            }

            $totalSoalEssay = $soalEssay->count();
            $essaySudahDinilai = 0;
            $skorEssay = 0;
            $bobotMaksEssay = $soalEssay->sum('bobot');

            foreach ($soalEssay as $se) {
                $j = $jawabanSiswa->get($se->id);
                if ($j && !is_null($j->skor_essay)) {
                    $essaySudahDinilai++;
                    $skorEssay += (float) $j->skor_essay;
                }
            }

            $menungguKoreksiEssay = $sudahSelesai && ($totalSoalEssay > 0) && ($essaySudahDinilai < $totalSoalEssay);

            $totalBobot = ($bobotMaksPG + $bobotMaksEssay) ?: 100;
            $nilaiKalkulasi = min(100, round((($skorPG + $skorEssay) / $totalBobot) * 100, 2));

            $nilaiAkhir = ($sesi && $sesi->jumlah_remidi > 0)
                ? (float) $sesi->nilai_akhir
                : $nilaiKalkulasi;

            if (!$sudahSelesai) {
                $statusKategori = 'belum_ujian';
                $statusLabel = $sedangMengerjakan ? 'Sedang Ujian' : 'Belum Mengerjakan';
            } elseif ($menungguKoreksiEssay) {
                $statusKategori = 'menunggu_essay';
                $statusLabel = "Menunggu Koreksi Essay ({$essaySudahDinilai}/{$totalSoalEssay})";
            } elseif ($nilaiAkhir >= $kkm) {
                $statusKategori = 'tuntas';
                $statusLabel = 'Tuntas';
            } else {
                $statusKategori = 'remidi';
                $statusLabel = 'Remidi / Tidak Tuntas';
            }

            $namaKelasSiswa = $ujianSiswa?->pengampuKelas?->kelas?->nama_kelas
                ?? $siswa->anggotaKelases->last()?->kelas?->nama_kelas
                ?? '-';

            return [
                'siswa_id'               => $siswa->id,
                'ujian_id'               => $ujianSiswa?->id ?? $ujian->id,
                'seleksi_id'             => $sesi?->id,
                'nama'                   => $siswa->user?->name ?? $siswa->nama_siswa ?? ('Siswa #' . $siswa->id),
                'nisn'                   => $siswa->nis ?? '-',
                'kelas'                  => $namaKelasSiswa,
                'sudah_selesai'          => $sudahSelesai,
                'pg_benar'               => $pgBenar,
                'pg_salah'               => $pgSalah,
                'total_pg'               => $soalPG->count(),
                'nilai_pg'               => round($skorPG, 2),
                'bobot_pg'               => round($bobotMaksPG, 2),
                'total_essay'            => $totalSoalEssay,
                'essay_dinilai'          => $essaySudahDinilai,
                'nilai_essay'            => round($skorEssay, 2),
                'bobot_essay'            => round($bobotMaksEssay, 2),
                'menunggu_koreksi_essay' => $menungguKoreksiEssay,
                'nilai_awal'             => $sesi ? (float) $sesi->nilai_awal : 0,
                'nilai_akhir'            => $nilaiAkhir,
                'jumlah_remidi'          => $sesi ? (int) $sesi->jumlah_remidi : 0,
                'status_kategori'        => $statusKategori,
                'status_label'           => $statusLabel,
            ];
        })->sortBy('nama')->values();

        $ringkasan = [
            'total'          => $rekapSiswa->count(),
            'menunggu_essay' => $rekapSiswa->where('status_kategori', 'menunggu_essay')->count(),
            'remidi'         => $rekapSiswa->where('status_kategori', 'remidi')->count(),
            'tuntas'         => $rekapSiswa->where('status_kategori', 'tuntas')->count(),
            'belum_ujian'    => $rekapSiswa->where('status_kategori', 'belum_ujian')->count(),
            'ada_soal_essay' => $ujian->soals->where('tipe_soal', 'essay')->count() > 0,
        ];

        return $this->successResponse([
            'kkm'         => $kkm,
            'ringkasan'   => $ringkasan,
            'rekap_siswa' => $rekapSiswa,
        ]);
    }

    /**
     * GURU: Data Koreksi Essay Massal (Per Nomor Soal untuk Semua Siswa).
     */
    public function guruKoreksiEssay(Request $request, Ujian $ujian)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuruUjian($ujian, $guruId);

        $grupUjians = $ujian->id_grup_ujian
            ? Ujian::with(['pengampuKelas.kelas', 'soals'])->where('id_grup_ujian', $ujian->id_grup_ujian)->get()
            : collect([$ujian]);

        $ujianIds = $grupUjians->pluck('id');

        $soalEssayList = $ujian->soals()
            ->where('tipe_soal', 'essay')
            ->orderBy('urutan', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $sesiSelesai = SeleksiUjian::with(['siswa.user', 'siswa.anggotaKelases.kelas'])
            ->whereIn('ujian_id', $ujianIds)
            ->where(function ($q) {
                $q->where('status', 'selesai')->orWhereNotNull('waktu_selesai');
            })
            ->get();

        $siswaIds = $sesiSelesai->pluck('siswa_id')->unique();

        $soalsSemuaKelas = Soal::whereIn('ujian_id', $ujianIds)
            ->where('tipe_soal', 'essay')
            ->get();

        $jawabanEssayAll = Menjawab::whereIn('soal_id', $soalsSemuaKelas->pluck('id'))
            ->whereIn('siswa_id', $siswaIds)
            ->get();

        $dataKoreksi = $soalEssayList->values()->map(function ($soalUtama, $idx) use ($sesiSelesai, $soalsSemuaKelas, $jawabanEssayAll) {
            $urutan = $soalUtama->urutan ?? ($idx + 1);

            $pesertaItems = $sesiSelesai->map(function ($sesi) use ($soalUtama, $urutan, $soalsSemuaKelas, $jawabanEssayAll) {
                $siswa = $sesi->siswa;
                $soalKelasIni = $soalsSemuaKelas
                    ->where('ujian_id', $sesi->ujian_id)
                    ->where('urutan', $urutan)
                    ->first() ?? $soalUtama;

                $jawab = $jawabanEssayAll
                    ->where('soal_id', $soalKelasIni->id)
                    ->where('siswa_id', $sesi->siswa_id)
                    ->first();

                return [
                    'siswa_id'      => $sesi->siswa_id,
                    'ujian_id'      => $sesi->ujian_id,
                    'soal_id'       => $soalKelasIni->id,
                    'nama'          => $siswa?->user?->name ?? $siswa?->nama_siswa ?? ('Siswa #' . $sesi->siswa_id),
                    'kelas'         => $siswa?->anggotaKelases?->last()?->kelas?->nama_kelas ?? '-',
                    'jawaban_teks'  => $jawab?->jawaban_dipilih ?? '',
                    'skor'          => $jawab && !is_null($jawab->skor_essay) ? (float) $jawab->skor_essay : null,
                    'sudah_dinilai' => $jawab && !is_null($jawab->skor_essay),
                    'catatan_guru'  => $jawab?->catatan_guru ?? '',
                ];
            })->sortBy('nama')->values();

            $kunciTeks = is_array($soalUtama->kunci_jawaban)
                ? implode("\n", $soalUtama->kunci_jawaban)
                : ($soalUtama->kunci_jawaban ?? '');

            return [
                'nomor_essay'   => $idx + 1,
                'nomor_asli'    => $urutan,
                'pertanyaan'    => $soalUtama->pertanyaan,
                'gambar'        => $soalUtama->gambar ? asset('storage/' . $soalUtama->gambar) : null,
                'bobot_maks'    => (float) ($soalUtama->bobot ?? 10),
                'kunci_jawaban' => $kunciTeks,
                'pesertas'      => $pesertaItems,
            ];
        });

        return $this->successResponse($dataKoreksi);
    }

    /**
     * GURU: Simpan nilai essay siswa.
     */
    public function guruSimpanNilaiEssay(Request $request, Ujian $ujian)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuruUjian($ujian, $guruId);

        $validator = Validator::make($request->all(), [
            'ujian_id'     => ['nullable', 'integer', 'exists:ujians,id'],
            'siswa_id'     => ['required', 'integer', 'exists:siswas,id'],
            'soal_id'      => ['required', 'integer', 'exists:soals,id'],
            'skor'         => ['required', 'numeric', 'min:0'],
            'catatan_guru' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $validated = $validator->validated();
        $targetUjianId = (int) ($validated['ujian_id'] ?? $ujian->id);

        $soal = Soal::where('ujian_id', $targetUjianId)->findOrFail($validated['soal_id']);
        $bobotMaks = (float) ($soal->bobot ?? 100);
        $skorValid = min($bobotMaks, max(0, (float) $validated['skor']));

        Menjawab::updateOrCreate(
            [
                'soal_id'  => $soal->id,
                'siswa_id' => $validated['siswa_id'],
            ],
            [
                'skor_essay'   => $skorValid,
                'catatan_guru' => $validated['catatan_guru'] ?? null,
                'benar'        => $skorValid >= ($bobotMaks * 0.5),
            ]
        );

        $hasilRekalkulasi = $this->rekalkulasiNilaiSiswa($targetUjianId, $validated['siswa_id']);

        return $this->successResponse([
            'skor_disimpan' => $skorValid,
            'nilai_akhir'   => $hasilRekalkulasi['nilai_akhir'],
            'status_kkm'    => $hasilRekalkulasi['status_kkm'],
        ], 'Nilai essay berhasil disimpan.');
    }

    /**
     * GURU: Detail lembar jawaban individual siswa pada ujian.
     */
    public function guruDetailJawabanSiswa(Request $request, Ujian $ujian, Siswa $siswa)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuruUjian($ujian, $guruId);

        $ujianIds = $ujian->id_grup_ujian
            ? Ujian::where('id_grup_ujian', $ujian->id_grup_ujian)->pluck('id')
            : collect([$ujian->id]);

        $sesi = SeleksiUjian::whereIn('ujian_id', $ujianIds)
            ->where('siswa_id', $siswa->id)
            ->first();

        if (!$sesi) {
            return $this->notFoundResponse('Data pengerjaan ujian siswa tidak ditemukan.');
        }

        $ujianSiswa = Ujian::with(['soals' => fn ($q) => $q->orderBy('urutan')->orderBy('id'), 'pengampuKelas.kelas'])
            ->findOrFail($sesi->ujian_id);

        $jawabanMap = Menjawab::where('siswa_id', $siswa->id)
            ->whereIn('soal_id', $ujianSiswa->soals->pluck('id'))
            ->get()
            ->keyBy('soal_id');

        $daftarJawaban = $ujianSiswa->soals->map(function ($soal, $idx) use ($jawabanMap) {
            $jawab = $jawabanMap->get($soal->id);
            $hasil = $this->hitungSkorSatuSoal($soal, $jawab);

            return [
                'soal_id'        => $soal->id,
                'nomor'          => $idx + 1,
                'pertanyaan'     => $soal->pertanyaan,
                'tipe_soal'      => $soal->tipe_soal,
                'pilihan'        => $soal->pilihan,
                'kunci_jawaban'  => $soal->kunci_jawaban,
                'bobot'          => $soal->bobot,
                'gambar_url'     => $soal->gambar ? asset('storage/' . $soal->gambar) : null,
                'jawaban_teks'   => $jawab?->jawaban_dipilih ?? '',
                'skor_diperoleh' => $hasil['skor'],
                'is_benar'       => $hasil['is_benar'],
                'sudah_dinilai'  => $soal->tipe_soal !== 'essay' || ($jawab && !is_null($jawab->skor_essay)),
                'catatan_guru'   => $jawab?->catatan_guru ?? '',
            ];
        });

        return $this->successResponse([
            'siswa' => [
                'id'   => $siswa->id,
                'nama' => $siswa->nama_siswa,
                'nis'  => $siswa->nis,
            ],
            'sesi'           => $sesi,
            'daftar_jawaban' => $daftarJawaban,
        ]);
    }

    /**
     * GURU: Buat Ujian Remidi atau Susulan.
     */
    public function guruBuatRemidiSusulan(Request $request, Ujian $ujian)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        $this->authorizeGuruUjian($ujian, $guruId);

        $validator = Validator::make($request->all(), [
            'jenis'            => ['required', 'in:remidi,susulan'],
            'judul'            => ['required', 'string', 'max:120'],
            'siswa_ids'        => ['required', 'array', 'min:1'],
            'siswa_ids.*'      => ['integer', 'exists:siswas,id'],
            'durasi_menit'     => ['required', 'integer', 'min:1', 'max:999'],
            'waktu_mulai'      => ['required', 'date'],
            'deadline'         => ['required', 'date', 'after:waktu_mulai'],
            'kebijakan_remidi' => ['nullable', 'required_if:jenis,remidi', 'in:kkm,terbaik,rata_rata'],
            'sumber_soal'      => ['required', 'in:salin_induk,buat_baru'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $validated = $validator->validated();

        $grupUjians = $ujian->id_grup_ujian
            ? Ujian::with(['pengampuKelas.kelas', 'soals'])->where('id_grup_ujian', $ujian->id_grup_ujian)->get()
            : collect([$ujian->load(['pengampuKelas.kelas', 'soals'])]);

        $siswaIdsTerpilih = array_map('intval', $validated['siswa_ids']);
        $sesiIndukMap = SeleksiUjian::whereIn('ujian_id', $grupUjians->pluck('id'))
            ->whereIn('siswa_id', $siswaIdsTerpilih)
            ->get()
            ->keyBy('siswa_id');

        $siswaList = Siswa::with('anggotaKelases')->whereIn('id', $siswaIdsTerpilih)->get();

        $pesertaPerUjianInduk = [];
        foreach ($siswaList as $siswa) {
            $sesiInduk = $sesiIndukMap->get($siswa->id);
            if ($sesiInduk) {
                $targetUjianInduk = $grupUjians->firstWhere('id', $sesiInduk->ujian_id);
            } else {
                $kelasSiswaId = $siswa->anggotaKelases->last()?->kelas_id;
                $targetUjianInduk = $grupUjians->firstWhere('pengampuKelas.kelas_id', $kelasSiswaId) ?? $grupUjians->first();
            }

            if ($targetUjianInduk) {
                $pesertaPerUjianInduk[$targetUjianInduk->id]['ujian'] = $targetUjianInduk;
                $pesertaPerUjianInduk[$targetUjianInduk->id]['siswa_ids'][] = $siswa->id;
            }
        }

        $createdUjians = [];

        DB::transaction(function () use ($pesertaPerUjianInduk, $validated, &$createdUjians) {
            $grupBaruId = (string) Str::uuid();
            $tokenSesi1 = 'S1-' . strtoupper(Str::random(4));
            $unlockCode = strtoupper(Str::random(6));
            $waktuMulai = Carbon::parse($validated['waktu_mulai']);
            $deadline = Carbon::parse($validated['deadline']);

            if ($validated['sumber_soal'] === 'buat_baru') {
                $statusBaru = 'draft';
            } else {
                $statusBaru = now()->lt($waktuMulai) ? 'scheduled' : 'published';
            }

            foreach ($pesertaPerUjianInduk as $dataKelompok) {
                /** @var Ujian $induk */
                $induk = $dataKelompok['ujian'];
                $daftarSiswaKelas = array_values(array_unique($dataKelompok['siswa_ids']));

                $ujianBaru = Ujian::create([
                    'id_grup_ujian'     => $grupBaruId,
                    'judul'             => $validated['judul'],
                    'kisi_kisi'         => $induk->kisi_kisi,
                    'pengampu_kelas_id' => $induk->pengampu_kelas_id,
                    'sesi'              => 1,
                    'durasi_menit'      => (int) $validated['durasi_menit'],
                    'kkm'               => $induk->kkm,
                    'waktu_mulai'       => $waktuMulai,
                    'waktu_mulai_sesi'  => $waktuMulai,
                    'deadline'          => $deadline,
                    'status'            => $statusBaru,
                    'jenis_ujian'       => $validated['jenis'],
                    'parent_ujian_id'   => $induk->id,
                    'kebijakan_remidi'  => $validated['jenis'] === 'remidi' ? $validated['kebijakan_remidi'] : null,
                    'peserta_khusus'    => $daftarSiswaKelas,
                    'token'             => $tokenSesi1,
                    'unlock_code'       => $unlockCode,
                    'dipublikasi_at'    => $statusBaru === 'published' ? now() : null,
                ]);

                if ($validated['sumber_soal'] === 'salin_induk') {
                    foreach ($induk->soals as $soalLama) {
                        $soalBaru = $soalLama->replicate(['created_at', 'updated_at']);
                        $soalBaru->ujian_id = $ujianBaru->id;
                        $soalBaru->save();
                    }
                }

                $createdUjians[] = $ujianBaru;
            }
        });

        return $this->createdResponse(
            count($createdUjians) === 1 ? $createdUjians[0] : $createdUjians,
            'Ujian ' . ucfirst($validated['jenis']) . ' berhasil dibuat untuk ' . count($siswaIdsTerpilih) . ' siswa terpilih!'
        );
    }

    /**
     * GURU: Laporan Leger Semester & Berita Acara Ujian.
     */
    public function guruLaporan(Request $request)
    {
        $guruId = $request->user()?->guru?->id ?? $request->user()?->guru_id;
        if (!$guruId) {
            return $this->notFoundResponse('Profil guru tidak ditemukan.');
        }

        $pengampuList = PengampuKelas::with(['guruMapel.mapel', 'guruMapel.guru.user', 'kelas'])
            ->whereHas('guruMapel', fn ($q) => $q->where('guru_id', $guruId))
            ->get()
            ->filter(fn ($item) => $item->guruMapel?->mapel && $item->kelas)
            ->values();

        $kelasIdsGuru = $pengampuList->pluck('kelas_id')->unique();

        $daftarTahunAjaran = DB::table('anggota_kelases')
            ->join('academic_periods', 'anggota_kelases.academic_period_id', '=', 'academic_periods.id')
            ->whereIn('anggota_kelases.kelas_id', $kelasIdsGuru)
            ->distinct()
            ->orderByDesc('academic_periods.tahun_ajaran')
            ->pluck('academic_periods.tahun_ajaran')
            ->values();

        if ($daftarTahunAjaran->isEmpty()) {
            $bulanSekarang = (int) now()->format('n');
            $thnSekarang = (int) now()->format('Y');
            $defaultTa = $bulanSekarang >= 7
                ? "{$thnSekarang}/" . ($thnSekarang + 1)
                : ($thnSekarang - 1) . "/{$thnSekarang}";
            $daftarTahunAjaran = collect([$defaultTa]);
        }

        $selectedTahunAjaran = $request->input('tahun_ajaran', $daftarTahunAjaran->first());
        $selectedSemester = strtolower($request->input('semester', (int) now()->format('n') >= 7 ? 'ganjil' : 'genap'));
        if (!in_array($selectedSemester, ['ganjil', 'genap'])) {
            $selectedSemester = 'ganjil';
        }

        $selectedPengampuId = $request->input('pengampu_kelas_id', $pengampuList->first()?->id);
        $selectedPengampu = $pengampuList->firstWhere('id', (int) $selectedPengampuId) ?? $pengampuList->first();

        $mode = $request->input('mode', 'leger');
        if (!in_array($mode, ['leger', 'detail'])) {
            $mode = 'leger';
        }

        $partsTa = preg_split('/[\/\-]/', (string) $selectedTahunAjaran);
        $tahunAwal = isset($partsTa[0]) && is_numeric($partsTa[0]) ? (int) $partsTa[0] : (int) now()->format('Y');
        $tahunAkhir = isset($partsTa[1]) && is_numeric($partsTa[1]) ? (int) $partsTa[1] : ($tahunAwal + 1);

        if ($selectedSemester === 'ganjil') {
            $rentangMulai = Carbon::create($tahunAwal, 7, 1, 0, 0, 0);
            $rentangSelesai = Carbon::create($tahunAwal, 12, 31, 23, 59, 59);
        } else {
            $rentangMulai = Carbon::create($tahunAkhir, 1, 1, 0, 0, 0);
            $rentangSelesai = Carbon::create($tahunAkhir, 6, 30, 23, 59, 59);
        }

        $ujianRegulers = collect();
        $selectedUjian = null;
        $barisLeger = collect();
        $barisDetail = collect();
        $qualityGate = [
            'menunggu_essay' => 0,
            'belum_ujian'    => 0,
        ];

        if ($selectedPengampu) {
            $ujianRegulers = Ujian::with('soals')
                ->where('pengampu_kelas_id', $selectedPengampu->id)
                ->where(fn ($q) => $q->where('jenis_ujian', 'reguler')->orWhereNull('jenis_ujian'))
                ->whereIn('status', ['published', 'completed', 'unpublished'])
                ->where(function ($q) use ($rentangMulai, $rentangSelesai) {
                    $q->whereBetween('waktu_mulai', [$rentangMulai, $rentangSelesai])
                        ->orWhere(function ($sub) use ($rentangMulai, $rentangSelesai) {
                            $sub->whereNull('waktu_mulai')
                                ->whereBetween('created_at', [$rentangMulai, $rentangSelesai]);
                        });
                })
                ->orderBy('waktu_mulai', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $selectedUjianId = $request->input('ujian_id', $ujianRegulers->first()?->id);
            $selectedUjian = $ujianRegulers->firstWhere('id', (int) $selectedUjianId) ?? $ujianRegulers->first();

            $mapelPengampu = $selectedPengampu->guruMapel?->mapel;
            $daftarSiswa = Siswa::with(['user'])
                ->forMapel($mapelPengampu)
                ->whereHas('anggotaKelases', function ($q) use ($selectedPengampu, $selectedTahunAjaran, $selectedSemester) {
                    $q->where('kelas_id', $selectedPengampu->kelas_id)
                        ->whereHas('academicPeriod', function ($ap) use ($selectedTahunAjaran, $selectedSemester) {
                            $ap->where('tahun_ajaran', $selectedTahunAjaran)
                                ->whereRaw('LOWER(semester) = ?', [$selectedSemester]);
                        });
                })
                ->get()
                ->unique('id')
                ->sortBy(fn ($s) => strtolower($s->user?->name ?? $s->nama_siswa ?? ''))
                ->values();

            if ($daftarSiswa->isEmpty()) {
                $daftarSiswa = Siswa::with(['user'])
                    ->forMapel($mapelPengampu)
                    ->whereHas('anggotaKelases', fn ($q) => $q->where('kelas_id', $selectedPengampu->kelas_id))
                    ->get()
                    ->unique('id')
                    ->sortBy(fn ($s) => strtolower($s->user?->name ?? $s->nama_siswa ?? ''))
                    ->values();
            }

            $regulerIds = $ujianRegulers->pluck('id');
            $sesiRegulerAll = SeleksiUjian::whereIn('ujian_id', $regulerIds)->get();
            $ujianAnakList = Ujian::whereIn('parent_ujian_id', $regulerIds)
                ->whereIn('jenis_ujian', ['remidi', 'susulan'])
                ->get();

            $sesiAnakSelesai = SeleksiUjian::whereIn('ujian_id', $ujianAnakList->pluck('id'))
                ->where(fn ($q) => $q->where('status', 'selesai')->orWhereNotNull('waktu_selesai'))
                ->get();

            $semuaSoalIds = Soal::whereIn('ujian_id', $regulerIds)->pluck('id');
            $jawabanAll = Menjawab::whereIn('soal_id', $semuaSoalIds)->get()->groupBy('siswa_id');

            $barisLeger = $daftarSiswa->map(function ($siswa) use ($ujianRegulers, $sesiRegulerAll, $ujianAnakList, $sesiAnakSelesai, $jawabanAll, &$qualityGate) {
                $jawabanSiswa = ($jawabanAll->get($siswa->id) ?? collect())->keyBy('soal_id');
                $nilaiPerUjian = [];
                $kumpulanNilaiValid = [];

                foreach ($ujianRegulers as $ujianItem) {
                    $sesi = $sesiRegulerAll->where('ujian_id', $ujianItem->id)->where('siswa_id', $siswa->id)->first();
                    $sudahSelesai = $sesi && ($sesi->status === 'selesai' || !is_null($sesi->waktu_selesai));

                    $soalEssays = $ujianItem->soals->where('tipe_soal', 'essay');
                    $essayBelumDinilai = false;
                    if ($sudahSelesai && $soalEssays->isNotEmpty()) {
                        foreach ($soalEssays as $se) {
                            $j = $jawabanSiswa->get($se->id);
                            if (!$j || is_null($j->skor_essay)) {
                                $essayBelumDinilai = true;
                                break;
                            }
                        }
                    }

                    $anakIdsRemidi = $ujianAnakList->where('parent_ujian_id', $ujianItem->id)->where('jenis_ujian', 'remidi')->pluck('id');
                    $anakIdsSusulan = $ujianAnakList->where('parent_ujian_id', $ujianItem->id)->where('jenis_ujian', 'susulan')->pluck('id');

                    $ikutRemidi = ($sesi && (int) $sesi->jumlah_remidi > 0)
                        || $sesiAnakSelesai->whereIn('ujian_id', $anakIdsRemidi)->where('siswa_id', $siswa->id)->isNotEmpty();
                    $ikutSusulan = $sesiAnakSelesai->whereIn('ujian_id', $anakIdsSusulan)->where('siswa_id', $siswa->id)->isNotEmpty();

                    $kodeAsal = $ikutRemidi ? 'R' : ($ikutSusulan ? 'S' : null);

                    if (!$sudahSelesai) {
                        $qualityGate['belum_ujian']++;
                        $nilaiPerUjian[$ujianItem->id] = [
                            'status'     => 'belum_ujian',
                            'nilai'      => null,
                            'nilai_awal' => null,
                            'kode'       => null,
                            'tuntas'     => false,
                        ];
                    } elseif ($essayBelumDinilai) {
                        $qualityGate['menunggu_essay']++;
                        $nilaiPerUjian[$ujianItem->id] = [
                            'status'     => 'menunggu_essay',
                            'nilai'      => null,
                            'nilai_awal' => (float) $sesi->nilai_awal,
                            'kode'       => $kodeAsal,
                            'tuntas'     => false,
                        ];
                    } else {
                        $skorFinal = round((float) $sesi->nilai_akhir, 2);
                        $kkmUjian = (float) ($ujianItem->kkm ?? 75);
                        $kumpulanNilaiValid[] = $skorFinal;

                        $nilaiPerUjian[$ujianItem->id] = [
                            'status'     => 'selesai',
                            'nilai'      => $skorFinal,
                            'nilai_awal' => round((float) $sesi->nilai_awal, 2),
                            'kode'       => $kodeAsal,
                            'tuntas'     => $skorFinal >= $kkmUjian,
                        ];
                    }
                }

                $rataRata = count($kumpulanNilaiValid) > 0
                    ? round(array_sum($kumpulanNilaiValid) / count($kumpulanNilaiValid), 2)
                    : null;

                return [
                    'siswa_id'        => $siswa->id,
                    'nama'            => $siswa->user?->name ?? $siswa->nama_siswa ?? ('Siswa #' . $siswa->id),
                    'nisn'            => $siswa->nis ?? '-',
                    'nilai_per_ujian' => $nilaiPerUjian,
                    'rata_rata'       => $rataRata,
                ];
            });

            if ($selectedUjian) {
                $kkmDetail = (float) ($selectedUjian->kkm ?? 75);
                $soalsDetail = $selectedUjian->soals;
                $soalPG = $soalsDetail->whereIn('tipe_soal', ['single_choice', 'multiple_choice']);
                $soalEssay = $soalsDetail->where('tipe_soal', 'essay');

                $anakRemidiIds = $ujianAnakList->where('parent_ujian_id', $selectedUjian->id)->where('jenis_ujian', 'remidi')->pluck('id');
                $anakSusulanIds = $ujianAnakList->where('parent_ujian_id', $selectedUjian->id)->where('jenis_ujian', 'susulan')->pluck('id');

                $barisDetail = $daftarSiswa->map(function ($siswa) use ($selectedUjian, $sesiRegulerAll, $sesiAnakSelesai, $anakRemidiIds, $anakSusulanIds, $soalPG, $soalEssay, $jawabanAll, $kkmDetail) {
                    $sesi = $sesiRegulerAll->where('ujian_id', $selectedUjian->id)->where('siswa_id', $siswa->id)->first();
                    $jawabanSiswa = ($jawabanAll->get($siswa->id) ?? collect())->keyBy('soal_id');
                    $sudahSelesai = $sesi && ($sesi->status === 'selesai' || !is_null($sesi->waktu_selesai));

                    $pgBenar = 0;
                    $pgSalah = 0;
                    $skorPG = 0;
                    foreach ($soalPG as $s) {
                        $hasil = $this->hitungSkorSatuSoal($s, $jawabanSiswa->get($s->id));
                        $skorPG += $hasil['skor'];
                        if ($hasil['is_benar']) {
                            $pgBenar++;
                        } else {
                            $pgSalah++;
                        }
                    }

                    $essayDinilai = 0;
                    $skorEssay = 0;
                    foreach ($soalEssay as $se) {
                        $j = $jawabanSiswa->get($se->id);
                        if ($j && !is_null($j->skor_essay)) {
                            $essayDinilai++;
                            $skorEssay += (float) $j->skor_essay;
                        }
                    }

                    $menungguEssay = $sudahSelesai && ($soalEssay->count() > 0) && ($essayDinilai < $soalEssay->count());
                    $ikutRemidi = ($sesi && (int) $sesi->jumlah_remidi > 0)
                        || $sesiAnakSelesai->whereIn('ujian_id', $anakRemidiIds)->where('siswa_id', $siswa->id)->isNotEmpty();
                    $ikutSusulan = $sesiAnakSelesai->whereIn('ujian_id', $anakSusulanIds)->where('siswa_id', $siswa->id)->isNotEmpty();
                    $kodeAsal = $ikutRemidi ? 'R' : ($ikutSusulan ? 'S' : null);

                    $nilaiAwal = $sesi ? round((float) $sesi->nilai_awal, 2) : null;
                    $nilaiAkhir = $sesi ? round((float) $sesi->nilai_akhir, 2) : null;

                    if (!$sudahSelesai) {
                        $statusKategori = 'belum_ujian';
                        $statusLabel = 'Belum Ujian';
                    } elseif ($menungguEssay) {
                        $statusKategori = 'menunggu_essay';
                        $statusLabel = 'Menunggu Koreksi Essay';
                    } elseif ($nilaiAkhir >= $kkmDetail) {
                        $statusKategori = 'tuntas';
                        $statusLabel = 'Tuntas';
                    } else {
                        $statusKategori = 'remidi';
                        $statusLabel = 'Belum Tuntas';
                    }

                    return [
                        'siswa_id'        => $siswa->id,
                        'nama'            => $siswa->user?->name ?? $siswa->nama_siswa ?? ('Siswa #' . $siswa->id),
                        'nisn'            => $siswa->nis ?? '-',
                        'sudah_selesai'   => $sudahSelesai,
                        'pg_benar'        => $pgBenar,
                        'pg_salah'        => $pgSalah,
                        'total_pg'        => $soalPG->count(),
                        'skor_pg'         => round($skorPG, 2),
                        'total_essay'     => $soalEssay->count(),
                        'skor_essay'      => round($skorEssay, 2),
                        'menunggu_essay'  => $menungguEssay,
                        'nilai_awal'      => $nilaiAwal,
                        'nilai_akhir'     => $nilaiAkhir,
                        'kode'            => $kodeAsal,
                        'status_kategori' => $statusKategori,
                        'status_label'    => $statusLabel,
                    ];
                });
            }
        }

        return $this->successResponse([
            'daftar_tahun_ajaran'   => $daftarTahunAjaran,
            'selected_tahun_ajaran' => $selectedTahunAjaran,
            'selected_semester'     => $selectedSemester,
            'selected_pengampu_id'  => $selectedPengampu?->id,
            'mode'                  => $mode,
            'ujian_regulers'        => $ujianRegulers->map(fn ($u) => [
                'id'    => $u->id,
                'judul' => $u->judul,
                'kkm'   => $u->kkm,
            ]),
            'selected_ujian_id'     => $selectedUjian?->id,
            'quality_gate'          => $qualityGate,
            'baris_leger'           => $barisLeger,
            'baris_detail'          => $barisDetail,
        ]);
    }

    // =========================================================================
    // FITUR UJIAN SISWA (MOBILE CBT)
    // =========================================================================

    /**
     * SISWA: Menampilkan daftar ujian untuk kelas siswa (beserta status pengerjaan & filter peserta_khusus).
     */
    public function siswaIndex(Request $request)
    {
        $siswa = $request->user()?->siswa;
        $kelas = $siswa?->kelas;
        $kelasId = $kelas?->id;

        if (!$kelasId) {
            return $this->notFoundResponse('Data kelas siswa tidak ditemukan.');
        }

        $ujians = Ujian::with([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.guruMapel.guru',
            'pengampuKelas.kelas',
            'seleksiUjians' => function ($q) use ($siswa) {
                if ($siswa) {
                    $q->where('siswa_id', $siswa->id);
                }
            },
            'seleksiUjians.nilais',
        ])
            ->withCount('soals')
            ->whereHas('pengampuKelas', function ($q) use ($kelasId, $siswa) {
                $q->where('kelas_id', $kelasId)->forSiswa($siswa);
            })
            ->whereIn('status', ['published', 'scheduled', 'completed'])
            ->latest('waktu_mulai')
            ->get()
            ->filter(function ($u) use ($siswa) {
                if (in_array($u->jenis_ujian, ['remidi', 'susulan']) && !empty($u->peserta_khusus)) {
                    return $siswa && in_array((int) $siswa->id, array_map('intval', $u->peserta_khusus));
                }
                return true;
            })
            ->values()
            ->map(function ($u) {
                $u->sinkronisasiStatus();
                $sesi = $u->seleksiUjians->first();
                $nilai = $sesi?->nilais?->first();
                $sudahDikerjakan = $nilai !== null || ($sesi && $sesi->status === 'selesai');
                $skorTampil = $nilai?->nilai ?? ($sesi && $sesi->status === 'selesai' ? $sesi->nilai_akhir : null);

                return [
                    'id'               => $u->id,
                    'judul'            => $u->judul,
                    'kisi_kisi'        => $u->kisi_kisi,
                    'jenis_ujian'      => $u->jenis_ujian ?? 'reguler',
                    'durasi_menit'     => (int) ($u->durasi_menit ?? 60),
                    'total_soal'       => (int) ($u->soals_count ?? 0),
                    'kkm'              => (float) ($u->kkm ?? 75),
                    'tampilkan_nilai'  => (bool) $u->tampilkan_nilai,
                    'sesi'             => (int) ($u->sesi ?? 1),
                    'status'           => $u->status,
                    'status_label'     => $u->status_label,
                    'waktu_mulai'      => $u->waktu_mulai?->toIso8601String(),
                    'waktu_mulai_sesi' => $u->waktu_mulai_sesi?->toIso8601String(),
                    'deadline'         => $u->deadline?->toIso8601String(),
                    'mapel'            => $u->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                    'kelas'            => $u->pengampuKelas?->kelas?->nama_kelas,
                    'guru'             => $u->pengampuKelas?->guruMapel?->guru?->nama,
                    'sudah_mulai'      => $sesi !== null,
                    'sudah_dikerjakan' => $sudahDikerjakan,
                    'status_sesi'      => $sesi?->status,
                    'waktu_selesai'    => $sesi?->waktu_selesai,
                    'nilai_siswa'      => $u->tampilkan_nilai ? $skorTampil : null,
                ];
            });

        return $this->successResponse($ujians);
    }

    /**
     * SISWA: Detail ujian & kisi-kisi untuk siswa.
     */
    public function siswaShow(Request $request, Ujian $ujian)
    {
        $siswa = $request->user()?->siswa;
        $kelas = $siswa?->kelas;
        $kelasId = $kelas?->id;

        if (!$kelasId || $ujian->pengampuKelas?->kelas_id !== $kelasId) {
            return $this->forbiddenResponse('Akses ditolak.');
        }

        if (!$ujian->pengampuKelas?->guruMapel?->mapel?->isAccessibleBySiswa($siswa)) {
            return $this->forbiddenResponse('Mata pelajaran ujian ini tidak sesuai dengan agama Anda.');
        }

        if (in_array($ujian->jenis_ujian, ['remidi', 'susulan']) && !empty($ujian->peserta_khusus)) {
            if (!in_array((int) $siswa->id, array_map('intval', $ujian->peserta_khusus))) {
                return $this->forbiddenResponse('Anda tidak terdaftar sebagai peserta pada ujian remidi/susulan ini.');
            }
        }

        $ujian->sinkronisasiStatus();

        $ujian->load([
            'pengampuKelas.guruMapel.mapel',
            'pengampuKelas.guruMapel.guru',
            'soals',
            'seleksiUjians' => fn ($q) => $q->where('siswa_id', $siswa->id),
            'seleksiUjians.nilais',
        ]);

        $sesi = $ujian->seleksiUjians->first();
        $nilai = $sesi?->nilais?->first();

        $ringkasanTipe = [
            'single_choice'   => $ujian->soals->where('tipe_soal', 'single_choice')->count(),
            'multiple_choice' => $ujian->soals->where('tipe_soal', 'multiple_choice')->count(),
            'essay'           => $ujian->soals->where('tipe_soal', 'essay')->count(),
            'matching'        => $ujian->soals->where('tipe_soal', 'matching')->count(),
            'total_soal'      => $ujian->soals->count(),
            'total_poin'      => $ujian->soals->sum('bobot') ?: 100,
        ];

        $waktuMulaiSesi = Carbon::parse($ujian->waktu_mulai_sesi ?? $ujian->waktu_mulai ?? now());
        $batasAkhirSesi = $waktuMulaiSesi->copy()->addMinutes((int) $ujian->durasi_menit);

        return $this->successResponse([
            'id'               => $ujian->id,
            'judul'            => $ujian->judul,
            'deskripsi'        => $ujian->deskripsi,
            'kisi_kisi'        => $ujian->kisi_kisi,
            'jenis_ujian'      => $ujian->jenis_ujian ?? 'reguler',
            'durasi_menit'     => $ujian->durasi_menit,
            'kkm'              => $ujian->kkm,
            'tampilkan_nilai'  => (bool) $ujian->tampilkan_nilai,
            'sesi'             => $ujian->sesi ?? 1,
            'status'           => $ujian->status,
            'status_label'     => $ujian->status_label,
            'waktu_mulai'      => $ujian->waktu_mulai,
            'waktu_mulai_sesi' => $waktuMulaiSesi,
            'batas_akhir_sesi' => $batasAkhirSesi,
            'deadline'         => $ujian->deadline,
            'mapel'            => $ujian->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
            'kelas'            => $ujian->pengampuKelas?->kelas?->nama_kelas,
            'guru'             => $ujian->pengampuKelas?->guruMapel?->guru?->nama,
            'ringkasan_tipe'   => $ringkasanTipe,
            'sudah_mulai'      => $sesi !== null,
            'sudah_dikerjakan' => $nilai !== null || ($sesi && $sesi->status === 'selesai'),
            'status_sesi'      => $sesi?->status,
            'nilai_siswa'      => $nilai?->nilai ?? ($sesi && $sesi->status === 'selesai' ? $sesi->nilai_akhir : null),
        ]);
    }

    /**
     * SISWA: Konfirmasi sebelum masuk ruang ujian.
     */
    public function siswaKonfirmasi(Request $request, Ujian $ujian)
    {
        return $this->siswaShow($request, $ujian);
    }

    /**
     * Memastikan ujian milik kelas siswa (serta sesuai agama dan peserta khusus jika remidi/susulan).
     */
    private function authorizeUjianSiswaApi(Ujian $ujian, $siswa)
    {
        if (!$siswa) {
            return $this->forbiddenResponse('Profil siswa tidak ditemukan.');
        }

        $ujian->loadMissing(['pengampuKelas.guruMapel.mapel']);

        $kelas = $siswa->kelas;
        $kelasId = $kelas?->id;

        if (!$kelasId || (int) $ujian->pengampuKelas?->kelas_id !== (int) $kelasId) {
            return $this->forbiddenResponse('Anda tidak memiliki akses ke ujian kelas lain.');
        }

        $mapel = $ujian->pengampuKelas?->guruMapel?->mapel;
        if ($mapel && method_exists($mapel, 'isAccessibleBySiswa') && !$mapel->isAccessibleBySiswa($siswa)) {
            return $this->forbiddenResponse('Mata pelajaran ujian ini tidak sesuai dengan agama Anda.');
        }

        if (in_array($ujian->jenis_ujian, ['remidi', 'susulan']) && !empty($ujian->peserta_khusus)) {
            if (!in_array((int) $siswa->id, array_map('intval', $ujian->peserta_khusus))) {
                return $this->forbiddenResponse('Anda tidak terdaftar sebagai peserta pada ujian remidi/susulan ini.');
            }
        }

        return null;
    }

    /**
     * SISWA: Validasi Token Sesi & Jadwal Sesi sebelum membuka lembar soal ujian.
     */
    public function siswaValidasiToken(Request $request, Ujian $ujian)
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required', 'string'],
        ], [
            'token.required' => 'Token ujian wajib diisi.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $siswa = $request->user()?->siswa;
        if ($denied = $this->authorizeUjianSiswaApi($ujian, $siswa)) {
            return $denied;
        }

        $ujian->sinkronisasiStatus();

        if ($ujian->status !== 'published') {
            return $this->errorResponse('Ujian ini sedang tidak aktif.', 422);
        }

        $inputToken = strtoupper(trim($request->token));
        $tokenKelas = strtoupper(trim((string) $ujian->token));
        $nomorSesiKelas = $ujian->sesi ?? 1;

        if (!empty($tokenKelas) && $inputToken !== $tokenKelas) {
            if ($ujian->id_grup_ujian) {
                $ujianSesiLain = Ujian::where('id_grup_ujian', $ujian->id_grup_ujian)
                    ->whereRaw('UPPER(token) = ?', [$inputToken])
                    ->first();

                if ($ujianSesiLain) {
                    return $this->errorResponse("Token tersebut milik Sesi {$ujianSesiLain->sesi}. Kelas Anda terdaftar pada Sesi {$nomorSesiKelas}.", 422);
                }
            }

            return $this->errorResponse('Token ujian tidak valid atau belum dirilis oleh guru pengawas.', 422);
        }

        $waktuMulaiSesi = Carbon::parse($ujian->waktu_mulai_sesi ?? $ujian->waktu_mulai ?? now());
        $batasAkhirSesi = $waktuMulaiSesi->copy()->addMinutes((int) $ujian->durasi_menit);

        if (now()->lt($waktuMulaiSesi)) {
            return $this->errorResponse("Sesi {$nomorSesiKelas} belum dimulai. Jadwal sesi Anda dibuka pada pukul " . $waktuMulaiSesi->format('H:i') . " WIB.", 422);
        }

        if (now()->greaterThanOrEqualTo($batasAkhirSesi)) {
            return $this->errorResponse("Waktu pengerjaan untuk Sesi {$nomorSesiKelas} telah berakhir pada pukul " . $batasAkhirSesi->format('H:i') . " WIB.", 422);
        }

        if (session()->isStarted()) {
            session(['token_ujian_verified_' . $ujian->id => $ujian->token]);
        }
        Cache::put("token_ujian_verified_{$siswa->id}_{$ujian->id}", $ujian->token, now()->addHours(12));

        $sesi = SeleksiUjian::firstOrCreate(
            [
                'ujian_id' => $ujian->id,
                'siswa_id' => $siswa->id,
            ],
            [
                'nilai_awal'        => 0,
                'nilai_akhir'       => 0,
                'jumlah_remidi'     => 0,
                'waktu_mulai'       => now(),
                'status'            => 'sedang_mengerjakan',
                'pelanggaran_count' => 0,
            ]
        );

        if (!$sesi->waktu_mulai) {
            $sesi->update(['waktu_mulai' => now(), 'status' => 'sedang_mengerjakan']);
        }

        return $this->successResponse([
            'seleksi_id' => $sesi->id,
            'status'     => $sesi->status,
            'sisa_detik' => max(0, (int) now()->diffInSeconds($batasAkhirSesi, false)),
        ], 'Token ujian valid. Selamat mengerjakan!');
    }

    /**
     * SISWA: Mengambil lembar soal ujian aktif beserta sisa waktu & jawaban tersimpan.
     */
    public function siswaKerjakan(Request $request, Ujian $ujian)
    {
        $siswa = $request->user()?->siswa;
        if ($denied = $this->authorizeUjianSiswaApi($ujian, $siswa)) {
            return $denied;
        }

        $ujian->sinkronisasiStatus();

        if ($ujian->status !== 'published') {
            return $this->errorResponse('Ujian ini sedang ditarik dari publikasi atau belum aktif.', 422);
        }

        $sesi = SeleksiUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswa->id)
            ->first();

        if ($sesi && ($sesi->status === 'selesai' || !is_null($sesi->waktu_selesai))) {
            return $this->errorResponse('Ujian Anda telah selesai.', 400, [
                'nilai_akhir' => $sesi->nilai_akhir,
            ]);
        }

        if (!$sesi) {
            return $this->forbiddenResponse('Silakan validasi token sesi ujian terlebih dahulu.');
        }

        $waktuMulaiSesi = Carbon::parse($ujian->waktu_mulai_sesi ?? $ujian->waktu_mulai ?? $sesi->waktu_mulai ?? now());
        $batasAkhirSesi = $waktuMulaiSesi->copy()->addMinutes((int) $ujian->durasi_menit);
        $sisaDetik = max(0, (int) now()->diffInSeconds($batasAkhirSesi, false));

        $soals = $ujian->soals()->orderBy('urutan', 'asc')->orderBy('id', 'asc')->get();
        $soalsRaw = $ujian->soals()->orderBy('urutan', 'asc')->orderBy('id', 'asc')->get()->values();
        $idSiswaAcak = (int) $siswa->id;
        $idUjianAcak = (int) $ujian->id;

        if ($soalsRaw->count() > 1) {
            $urutanDasar = $soalsRaw->sortBy(fn ($item) => md5("base-ujian-{$idUjianAcak}-soal-{$item->id}"))->values();
            $indexPertama = ($idSiswaAcak + $idUjianAcak) % $soalsRaw->count();
            $soalPertama = $urutanDasar->get($indexPertama);
            $sisaSoal = $urutanDasar->forget($indexPertama)
                ->sortBy(fn ($item) => md5("acak-ujian-{$idUjianAcak}-siswa-{$idSiswaAcak}-soal-{$item->id}"))
                ->values();

            $soals = collect([$soalPertama])->concat($sisaSoal)->values();
        } else {
            $soals = $soalsRaw;
        }
        $savedAnswers = Menjawab::where('siswa_id', $siswa->id)
            ->whereIn('soal_id', $soals->pluck('id'))
            ->pluck('jawaban_dipilih', 'soal_id')
            ->toArray();

        $soalsData = $soals->map(function ($soal, $index) use ($savedAnswers) {
            $pilihan = is_array($soal->pilihan) ? $soal->pilihan : json_decode((string) $soal->pilihan, true) ?? [];
            $opsiFormatted = [];
            foreach ($pilihan as $k => $v) {
                $kode = is_numeric($k) ? chr(97 + (int) $k) : strtolower((string) $k);
                $opsiFormatted[] = [
                    'kode'  => $kode,
                    'label' => strtoupper($kode),
                    'teks'  => $v,
                ];
            }

            $gambarUrl = null;
            if (!empty($soal->gambar)) {
                $pathBersih = ltrim((string) $soal->gambar, '/');
                if (str_starts_with($pathBersih, 'http://') || str_starts_with($pathBersih, 'https://')) {
                    $gambarUrl = $pathBersih;
                } elseif (str_starts_with($pathBersih, 'storage/')) {
                    $gambarUrl = asset($pathBersih);
                } else {
                    $gambarUrl = asset('storage/' . $pathBersih);
                }
            }

            return [
                'id'           => $soal->id,
                'nomor'        => $index + 1,
                'pertanyaan'   => $soal->pertanyaan,
                'tipe_soal'    => $soal->tipe_soal,
                'gambar'       => $gambarUrl,
                'bobot'        => (float) ($soal->bobot ?? 1),
                'pilihan'      => $opsiFormatted,
                'saved_answer' => $savedAnswers[$soal->id] ?? null,
            ];
        });

        return $this->successResponse([
            'ujian' => [
                'id'           => $ujian->id,
                'judul'        => $ujian->judul,
                'durasi_menit' => $ujian->durasi_menit,
                'sesi'         => $ujian->sesi ?? 1,
            ],
            'sesi' => [
                'id'                => $sesi->id,
                'status'            => $sesi->status,
                'pelanggaran_count' => (int) ($sesi->pelanggaran_count ?? 0),
                'sisa_detik'        => $sisaDetik,
            ],
            'soals'         => $soalsData,
            'saved_answers' => (object) $savedAnswers,
        ]);
    }

    /**
     * SISWA: Auto-save satu jawaban soal ujian.
     */
    public function siswaSimpanJawaban(Request $request, Ujian $ujian)
    {
        $validator = Validator::make($request->all(), [
            'soal_id' => ['required', 'exists:soals,id'],
            'jawaban' => ['nullable'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $siswa = $request->user()?->siswa;
        if ($denied = $this->authorizeUjianSiswaApi($ujian, $siswa)) {
            return $denied;
        }

        $soal = Soal::where('ujian_id', $ujian->id)->find($request->soal_id);
        if (!$soal) {
            return $this->notFoundResponse('Soal tidak ditemukan pada ujian ini.');
        }

        $rawJawaban = $request->jawaban;
        $jawabanString = is_array($rawJawaban) ? implode(',', $rawJawaban) : (string) $rawJawaban;

        $kunci = $soal->kunci_jawaban;
        $isBenar = false;

        if ($soal->tipe_soal === 'multiple_choice') {
            $cleanKunci = is_array($kunci)
                ? array_map('strtolower', $kunci)
                : array_filter(explode(',', strtolower((string) $kunci)));
            $cleanJawaban = is_array($rawJawaban)
                ? array_map('strtolower', $rawJawaban)
                : array_filter(explode(',', strtolower((string) $rawJawaban)));

            sort($cleanKunci);
            sort($cleanJawaban);
            $isBenar = ($cleanKunci === $cleanJawaban);
        } elseif ($soal->tipe_soal === 'single_choice') {
            $isBenar = (strtolower(trim($jawabanString)) === strtolower(trim(is_array($kunci) ? ($kunci[0] ?? '') : (string) $kunci)));
        }

        Menjawab::updateOrCreate(
            [
                'soal_id'  => $soal->id,
                'siswa_id' => $siswa->id,
            ],
            [
                'jawaban_dipilih' => $jawabanString,
                'benar'           => $isBenar,
            ]
        );

        return $this->successResponse([
            'soal_id' => $soal->id,
            'jawaban' => $jawabanString,
        ], 'Jawaban berhasil disimpan otomatis.');
    }

    /**
     * SISWA: Heartbeat cek status sesi ujian (anti-cheat & timer).
     */
    public function siswaCekStatusSesi(Request $request, Ujian $ujian)
    {
        $siswa = $request->user()?->siswa;
        if ($denied = $this->authorizeUjianSiswaApi($ujian, $siswa)) {
            return $denied;
        }

        if ($ujian->status === 'unpublished' || $ujian->status === 'draft') {
            return $this->successResponse([
                'status'  => 'ditarik',
                'message' => 'Ujian telah ditarik dari publikasi oleh guru pengawas.',
            ]);
        }

        $siswaId = $siswa->id;
        $sesi = SeleksiUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswaId)
            ->first();

        if (!$sesi) {
            return $this->successResponse(['status' => 'belum_mulai']);
        }

        $waktuMulaiSesi = Carbon::parse($ujian->waktu_mulai_sesi ?? $ujian->waktu_mulai ?? $sesi->waktu_mulai ?? now());
        $batasAkhirSesi = $waktuMulaiSesi->copy()->addMinutes((int) $ujian->durasi_menit);
        $sisaDetik = max(0, (int) now()->diffInSeconds($batasAkhirSesi, false));

        return $this->successResponse([
            'status'            => $sesi->status,
            'pelanggaran_count' => (int) ($sesi->pelanggaran_count ?? 0),
            'sisa_detik'        => $sisaDetik,
            'is_timeout'        => ($sisaDetik <= 0 && $sesi->status !== 'selesai'),
        ]);
    }

    /**
     * SISWA: Catat pelanggaran pindah aplikasi/layar (anti-cheat).
     */
    public function siswaCatatPelanggaran(Request $request, Ujian $ujian)
    {
        $siswaId = $request->user()?->siswa?->id;

        $sesi = SeleksiUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswaId)
            ->first();

        if (!$sesi || in_array($sesi->status, ['selesai', 'terkunci'], true)) {
            return $this->successResponse(['status' => 'ignored']);
        }

        $sesi->increment('pelanggaran_count');
        $sesi->refresh();

        $maxToleransi = 3;
        $isLocked = false;

        // Mulai pelanggaran ke-3, 4, 5, 6, dst. langsung terkunci otomatis
        if ($sesi->pelanggaran_count >= $maxToleransi) {
            $sesi->update(['status' => 'terkunci']);
            $isLocked = true;
        }

        return $this->successResponse([
            'pelanggaran_count' => (int) $sesi->pelanggaran_count,
            'max_toleransi'     => $maxToleransi,
            'is_locked'         => $isLocked,
        ]);
    }

    /**
     * SISWA: Buka kunci sesi ujian yang terblokir dengan kode dari pengawas.
     */
    public function siswaBukaKunciDenganKode(Request $request, Ujian $ujian)
    {
        $validator = Validator::make($request->all(), [
            'kode' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $siswa = $request->user()?->siswa;
        if ($denied = $this->authorizeUjianSiswaApi($ujian, $siswa)) {
            return $denied;
        }

        $siswaId = $siswa->id;
        $sesi = SeleksiUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswaId)
            ->first();

        if (!$sesi) {
            return $this->notFoundResponse('Sesi ujian tidak ditemukan.');
        }

        if ($sesi->status !== 'terkunci') {
            return $this->successResponse($sesi, 'Sesi ujian sudah aktif.');
        }

        $inputKode = strtoupper(trim($request->kode));
        $kodeAktif = strtoupper(trim((string) $ujian->unlock_code));

        if (empty($kodeAktif) || $inputKode !== $kodeAktif) {
            return $this->errorResponse('Kode buka blokir tidak valid atau sudah diperbarui oleh pengawas.', 422);
        }

        $sesi->update([
            'status'            => 'sedang_mengerjakan',
            'pelanggaran_count' => 0,
        ]);

        return $this->successResponse($sesi, 'Blokir berhasil dibuka. Silakan lanjutkan pengerjaan ujian.');
    }

    /**
     * SISWA: Submit ujian, kalkulasi skor proporsional, dan simpan nilai.
     */
    public function siswaSubmit(Request $request, Ujian $ujian)
    {
        $siswa = $request->user()?->siswa;
        if ($denied = $this->authorizeUjianSiswaApi($ujian, $siswa)) {
            return $denied;
        }

        $siswaId = $siswa->id;

        $sesi = SeleksiUjian::firstOrCreate(
            [
                'ujian_id' => $ujian->id,
                'siswa_id' => $siswaId,
            ],
            [
                'nilai_awal'        => 0,
                'nilai_akhir'       => 0,
                'jumlah_remidi'     => 0,
                'waktu_mulai'       => now(),
                'status'            => 'sedang_mengerjakan',
                'pelanggaran_count' => 0,
            ]
        );

        // Jika klien mengirim batch jawaban sekaligus saat submit
        if ($request->filled('jawaban') && is_array($request->input('jawaban'))) {
            $soalsMap = $ujian->soals()->get()->keyBy('id');
            foreach ($request->input('jawaban') as $soalId => $rawJwb) {
                $soal = $soalsMap->get((int) $soalId);
                if (!$soal) {
                    continue;
                }
                $jwbStr = is_array($rawJwb) ? implode(',', $rawJwb) : (string) $rawJwb;
                $hasil = $this->hitungSkorSatuSoal($soal, new Menjawab(['jawaban_dipilih' => $jwbStr]));
                Menjawab::updateOrCreate(
                    ['soal_id' => $soal->id, 'siswa_id' => $siswaId],
                    ['jawaban_dipilih' => $jwbStr, 'benar' => $hasil['is_benar']]
                );
            }
        }

        $result = DB::transaction(function () use ($ujian, $siswaId, $sesi) {
            $soals = $ujian->soals;
            $soalIds = $soals->pluck('id');

            $jawabanSiswa = Menjawab::where('siswa_id', $siswaId)
                ->whereIn('soal_id', $soalIds)
                ->get()
                ->keyBy('soal_id');

            $totalBobot = $soals->sum('bobot') ?: 100;
            $skorDiperoleh = 0;

            foreach ($soals as $soal) {
                $skorDiperoleh += $this->hitungSkorSatuSoal($soal, $jawabanSiswa->get($soal->id))['skor'];
            }

            $nilaiAkhir = $totalBobot > 0 ? min(100, round(($skorDiperoleh / $totalBobot) * 100, 2)) : 0;

            $waktuAwal = $sesi->waktu_mulai ?? $sesi->created_at ?? now();
            $durasiDetik = (int) Carbon::parse($waktuAwal)->diffInSeconds(now());

            $sesi->update([
                'status'        => 'selesai',
                'waktu_selesai' => now(),
                'nilai_awal'    => $nilaiAkhir,
                'nilai_akhir'   => $nilaiAkhir,
            ]);

            $nilaiRecord = Nilai::updateOrCreate(
                [
                    'seleksi_ujian_id' => $sesi->id,
                    'siswa_id'         => $siswaId,
                ],
                [
                    'nilai'        => $nilaiAkhir,
                    'status'       => 'selesai',
                    'durasi'       => $durasiDetik,
                    'tugas_id'     => null,
                    'quiz_id'      => null,
                    'file_jawaban' => null,
                ]
            );

            foreach ($soals->where('tipe_soal', 'essay') as $soalEssay) {
                Menjawab::firstOrCreate(
                    ['soal_id' => $soalEssay->id, 'siswa_id' => $siswaId],
                    ['jawaban_dipilih' => '', 'benar' => false, 'skor_essay' => null]
                );
            }

            $adaEssay = $soals->where('tipe_soal', 'essay')->isNotEmpty();
            if (!$adaEssay && $ujian->parent_ujian_id && in_array($ujian->jenis_ujian, ['remidi', 'susulan'])) {
                $ujianInduk = Ujian::find($ujian->parent_ujian_id);
                if ($ujianInduk) {
                    $sesiInduk = SeleksiUjian::firstOrCreate(
                        ['ujian_id' => $ujianInduk->id, 'siswa_id' => $siswaId],
                        ['nilai_awal' => $nilaiAkhir, 'nilai_akhir' => $nilaiAkhir, 'jumlah_remidi' => 0, 'status' => 'selesai', 'waktu_selesai' => now()]
                    );

                    $kkmInduk = (float) ($ujianInduk->kkm ?? 75);
                    $nilaiAwalInduk = (float) $sesiInduk->nilai_awal;

                    if ($ujian->jenis_ujian === 'susulan') {
                        $nilaiFinalInduk = $nilaiAkhir;
                        $sesiInduk->update([
                            'nilai_awal'    => $nilaiFinalInduk,
                            'nilai_akhir'   => $nilaiFinalInduk,
                            'status'        => 'selesai',
                            'waktu_selesai' => $sesiInduk->waktu_selesai ?? now(),
                        ]);
                    } else {
                        $kebijakan = $ujian->kebijakan_remidi ?? 'kkm';
                        if ($kebijakan === 'terbaik') {
                            $nilaiFinalInduk = max($nilaiAwalInduk, $nilaiAkhir);
                        } elseif ($kebijakan === 'rata_rata') {
                            $nilaiFinalInduk = round(($nilaiAwalInduk + $nilaiAkhir) / 2, 2);
                        } else {
                            $nilaiFinalInduk = min($kkmInduk, max($nilaiAwalInduk, $nilaiAkhir));
                        }

                        $sesiInduk->update([
                            'nilai_akhir'   => $nilaiFinalInduk,
                            'jumlah_remidi' => (int) $sesiInduk->jumlah_remidi + 1,
                        ]);
                    }

                    Nilai::updateOrCreate(
                        ['seleksi_ujian_id' => $sesiInduk->id, 'siswa_id' => $siswaId],
                        ['nilai' => $nilaiFinalInduk, 'status' => 'selesai']
                    );
                }
            }

            return [
                'seleksi_id'   => $sesi->id,
                'nilai'        => $nilaiRecord->nilai,
                'durasi_detik' => $durasiDetik,
                'status'       => 'selesai',
            ];
        });

        return $this->successResponse($result, 'Ujian berhasil dikumpulkan!');
    }

    /**
     * SISWA: Menampilkan ringkasan skor dan hasil pengerjaan ujian.
     */
    public function siswaHasil(Request $request, Ujian $ujian)
    {
        $siswa = $request->user()?->siswa;
        if ($denied = $this->authorizeUjianSiswaApi($ujian, $siswa)) {
            return $denied;
        }

        $ujian->load(['pengampuKelas.guruMapel.mapel', 'pengampuKelas.guruMapel.guru', 'pengampuKelas.kelas']);

        $siswaId = $siswa->id;

        $sesi = SeleksiUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $siswaId)
            ->first();

        if (!$sesi) {
            return $this->notFoundResponse('Anda belum menyelesaikan ujian ini.');
        }

        $nilai = Nilai::where('seleksi_ujian_id', $sesi->id)
            ->where('siswa_id', $siswaId)
            ->first();

        $skorNilai = $nilai ? (float) $nilai->nilai : (float) ($sesi->nilai_akhir ?? $sesi->nilai_awal ?? 0);

        $soalIds = $ujian->soals()->pluck('id');
        $totalSoal = $soalIds->count();

        $jawaban = Menjawab::where('siswa_id', $siswaId)
            ->whereIn('soal_id', $soalIds)
            ->get();

        $totalBenar = $jawaban->where('benar', true)->count();
        $totalSalah = max(0, $totalSoal - $totalBenar);
        $adaEssay = $ujian->soals()->where('tipe_soal', 'essay')->exists();
        $essayBelumDinilai = $adaEssay && $jawaban->whereNull('skor_essay')->count() > 0;

        return $this->successResponse([
            'ujian' => [
                'id'              => $ujian->id,
                'judul'           => $ujian->judul,
                'mapel'           => $ujian->pengampuKelas?->guruMapel?->mapel?->nama_mapel,
                'kelas'           => $ujian->pengampuKelas?->kelas?->nama_kelas,
                'guru'            => $ujian->pengampuKelas?->guruMapel?->guru?->nama,
                'kkm'             => $ujian->kkm,
                'tampilkan_nilai' => (bool) $ujian->tampilkan_nilai,
            ],
            'summary' => [
                'seleksi_id'          => $sesi->id,
                'nilai'               => $ujian->tampilkan_nilai ? $skorNilai : null,
                'nilai_awal'          => $ujian->tampilkan_nilai ? (float) $sesi->nilai_awal : null,
                'nilai_akhir'         => $ujian->tampilkan_nilai ? (float) $sesi->nilai_akhir : null,
                'tuntas'              => $skorNilai >= (float) ($ujian->kkm ?? 75),
                'status'              => $sesi->status,
                'total_soal'          => $totalSoal,
                'total_benar'         => $totalBenar,
                'total_salah'         => $totalSalah,
                'ada_essay'           => $adaEssay,
                'essay_belum_dinilai' => $essayBelumDinilai,
                'durasi_detik'        => $nilai?->durasi,
                'waktu_mulai'         => $sesi->waktu_mulai,
                'waktu_selesai'       => $sesi->waktu_selesai,
            ],
        ]);
    }

    // =========================================================================
    // HELPER INTERNAL PENILAIAN UJIAN
    // =========================================================================

    private function hitungSkorSatuSoal(Soal $soal, ?Menjawab $jawab): array
    {
        $bobotSoal = (float) ($soal->bobot ?? 0);

        if ($bobotSoal <= 0 || (!is_null($soal->kesulitan) && (int) $soal->kesulitan === 0)) {
            return [
                'skor'     => 0,
                'is_benar' => false,
                'dianulir' => true,
            ];
        }

        if ($soal->tipe_soal === 'essay') {
            $skor = ($jawab && !is_null($jawab->skor_essay)) ? (float) $jawab->skor_essay : 0;
            return [
                'skor'     => $skor,
                'is_benar' => $skor >= ($bobotSoal * 0.5),
            ];
        }

        if (!$jawab || empty($jawab->jawaban_dipilih)) {
            return ['skor' => 0, 'is_benar' => false];
        }

        $kunci = $soal->kunci_jawaban;

        if ($soal->tipe_soal === 'multiple_choice') {
            $cleanKunci = is_array($kunci)
                ? array_map('strtolower', array_map('trim', $kunci))
                : array_filter(array_map('strtolower', array_map('trim', explode(',', (string) $kunci))));

            $jawabanArr = array_filter(array_map('strtolower', array_map('trim', explode(',', (string) $jawab->jawaban_dipilih))));
            $totalKunci = count($cleanKunci);

            if ($totalKunci > 0) {
                $benar = count(array_intersect($jawabanArr, $cleanKunci));
                $salah = count(array_diff($jawabanArr, $cleanKunci));
                $net = max(0, $benar - $salah);
                $skor = $bobotSoal * ($net / $totalKunci);
                return [
                    'skor'     => $skor,
                    'is_benar' => ($benar === $totalKunci && $salah === 0),
                ];
            }
            return ['skor' => 0, 'is_benar' => false];
        }

        $kunciTunggal = is_array($kunci) ? ($kunci[0] ?? '') : (string) $kunci;
        $isBenar = strtolower(trim((string) $jawab->jawaban_dipilih)) === strtolower(trim((string) $kunciTunggal));

        return [
            'skor'     => $isBenar ? $bobotSoal : 0,
            'is_benar' => $isBenar,
        ];
    }

    private function rekalkulasiNilaiSiswa(int $ujianId, int $siswaId): array
    {
        $ujian = Ujian::with('soals')->findOrFail($ujianId);
        $sesi = SeleksiUjian::where('ujian_id', $ujianId)->where('siswa_id', $siswaId)->firstOrFail();

        $soals = $ujian->soals;
        $jawabanMap = Menjawab::where('siswa_id', $siswaId)
            ->whereIn('soal_id', $soals->pluck('id'))
            ->get()
            ->keyBy('soal_id');

        $totalBobot = $soals->sum('bobot') ?: 100;
        $totalSkor = 0;

        foreach ($soals as $s) {
            $totalSkor += $this->hitungSkorSatuSoal($s, $jawabanMap->get($s->id))['skor'];
        }

        $nilaiUjianIni = min(100, round(($totalSkor / $totalBobot) * 100, 2));

        $sesi->update([
            'nilai_awal'  => $nilaiUjianIni,
            'nilai_akhir' => $nilaiUjianIni,
        ]);

        Nilai::updateOrCreate(
            ['seleksi_ujian_id' => $sesi->id, 'siswa_id' => $siswaId],
            ['nilai' => $nilaiUjianIni, 'status' => 'selesai']
        );

        if ($ujian->parent_ujian_id && in_array($ujian->jenis_ujian, ['remidi', 'susulan'])) {
            $ujianInduk = Ujian::find($ujian->parent_ujian_id);
            if ($ujianInduk) {
                $sesiInduk = SeleksiUjian::firstOrCreate(
                    ['ujian_id' => $ujianInduk->id, 'siswa_id' => $siswaId],
                    ['nilai_awal' => $nilaiUjianIni, 'nilai_akhir' => $nilaiUjianIni, 'jumlah_remidi' => 0, 'status' => 'selesai', 'waktu_selesai' => now()]
                );

                $kkmInduk = (float) ($ujianInduk->kkm ?? 75);
                $nilaiAwalInduk = (float) $sesiInduk->nilai_awal;

                if ($ujian->jenis_ujian === 'susulan') {
                    $nilaiFinalInduk = $nilaiUjianIni;
                    $sesiInduk->update([
                        'nilai_awal'    => $nilaiFinalInduk,
                        'nilai_akhir'   => $nilaiFinalInduk,
                        'status'        => 'selesai',
                        'waktu_selesai' => $sesiInduk->waktu_selesai ?? now(),
                    ]);
                } else {
                    $kebijakan = $ujian->kebijakan_remidi ?? 'kkm';
                    if ($kebijakan === 'terbaik') {
                        $nilaiFinalInduk = max($nilaiAwalInduk, $nilaiUjianIni);
                    } elseif ($kebijakan === 'rata_rata') {
                        $nilaiFinalInduk = round(($nilaiAwalInduk + $nilaiUjianIni) / 2, 2);
                    } else {
                        $nilaiFinalInduk = min($kkmInduk, max($nilaiAwalInduk, $nilaiUjianIni));
                    }

                    $sesiInduk->update([
                        'nilai_akhir'   => $nilaiFinalInduk,
                        'jumlah_remidi' => max(1, (int) $sesiInduk->jumlah_remidi),
                    ]);
                }

                Nilai::updateOrCreate(
                    ['seleksi_ujian_id' => $sesiInduk->id, 'siswa_id' => $siswaId],
                    ['nilai' => $nilaiFinalInduk, 'status' => 'selesai']
                );
            }
        }

        $kkm = (float) ($ujian->kkm ?? 75);
        return [
            'nilai_akhir' => $nilaiUjianIni,
            'status_kkm'  => $nilaiUjianIni >= $kkm ? 'Tuntas' : 'Remidi',
        ];
    }

    private function normalizeKunciPilihan($kunci): array
    {
        if (!is_array($kunci)) {
            $kunci = $kunci !== null && $kunci !== '' ? [$kunci] : [];
        }

        return collect($kunci)
            ->map(function ($value) {
                $value = strtolower(trim((string) $value));
                if (preg_match('/^[0-5]$/', $value)) {
                    return chr(97 + (int) $value);
                }
                return $value;
            })
            ->filter(fn ($value) => preg_match('/^[a-f]$/', $value))
            ->unique()
            ->values()
            ->all();
    }
}
