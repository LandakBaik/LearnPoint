<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\AnggotaKelas;
use App\Models\Kelas;
use App\Models\PengampuKelas;
use App\Services\AcademicPeriodService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AcademicPeriodController extends Controller
{
    public function __construct(
        protected AcademicPeriodService $periodService
    ) {}

    public function index(Request $request)
    {
        $activePeriod = $this->periodService->getActivePeriod();
        $periods = AcademicPeriod::withCount(['anggotaKelases', 'pengampuKelases'])
            ->orderByDesc('tahun_ajaran')
            ->orderByRaw("CASE WHEN semester = 'genap' THEN 2 ELSE 1 END DESC")
            ->get();

        $suggestedNext = $this->periodService->suggestNextPeriod($activePeriod);
        $kelases = Kelas::orderBy('tingkatan')->orderBy('nama_kelas')->get();

        $sourceCounts = AnggotaKelas::where('academic_period_id', $activePeriod->id)
            ->whereHas('siswa', fn ($q) => $q->where('status', 'aktif'))
            ->selectRaw('kelas_id, COUNT(*) as total')
            ->groupBy('kelas_id')
            ->pluck('total', 'kelas_id');

        $sourceStudents = AnggotaKelas::with(['siswa', 'kelas'])
            ->where('academic_period_id', $activePeriod->id)
            ->whereHas('siswa', fn ($q) => $q->where('status', 'aktif'))
            ->get()
            ->sortBy(fn ($ak) => ($ak->kelas?->tingkatan ?? 99) . '-' . ($ak->kelas?->nama_kelas ?? '') . '-' . ($ak->siswa?->nama_siswa ?? ''));

        $totalPeriods = $periods->count();
        $activeStudentCount = $sourceCounts->sum();
        $activePengampuCount = PengampuKelas::where('academic_period_id', $activePeriod->id)->count();

        return view('admin.tahun_ajaran.index', compact(
            'activePeriod',
            'periods',
            'suggestedNext',
            'kelases',
            'sourceCounts',
            'sourceStudents',
            'totalPeriods',
            'activeStudentCount',
            'activePengampuCount'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun_ajaran'    => ['required', 'string', 'max:20', 'regex:/^\d{4}\/\d{4}$/'],
            'semester'        => ['required', 'in:ganjil,genap'],
            'tanggal_mulai'   => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'activate'        => ['nullable', 'boolean'],
        ], [
            'tahun_ajaran.regex' => 'Format Tahun Ajaran harus YYYY/YYYY (contoh: 2026/2027).',
        ]);

        $period = $this->periodService->createPeriod(
            $validated['tahun_ajaran'],
            $validated['semester'],
            $validated['tanggal_mulai'] ?? null,
            $validated['tanggal_selesai'] ?? null,
            (bool) ($validated['activate'] ?? false)
        );

        return redirect()->route('admin.tahun-ajaran.index')
            ->with('success', "Periode {$period->label} berhasil disimpan" . ($period->is_active ? ' dan diaktifkan.' : '.'));
    }

    public function update(Request $request, AcademicPeriod $academicPeriod)
    {
        $validated = $request->validate([
            'tahun_ajaran'    => ['required', 'string', 'max:20', 'regex:/^\d{4}\/\d{4}$/'],
            'semester'        => [
                'required',
                'in:ganjil,genap',
                Rule::unique('academic_periods')->where(fn ($q) => $q->where('tahun_ajaran', $request->tahun_ajaran))->ignore($academicPeriod->id),
            ],
            'tanggal_mulai'   => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ], [
            'tahun_ajaran.regex' => 'Format Tahun Ajaran harus YYYY/YYYY (contoh: 2026/2027).',
            'semester.unique'    => 'Kombinasi Tahun Ajaran dan Semester tersebut sudah ada.',
        ]);

        $updated = $this->periodService->updatePeriod($academicPeriod, $validated);

        return redirect()->route('admin.tahun-ajaran.index')
            ->with('success', "Periode {$updated->label} berhasil diperbarui.");
    }

    public function destroy(AcademicPeriod $academicPeriod)
    {
        try {
            $label = $academicPeriod->label;
            $this->periodService->deletePeriod($academicPeriod);

            return redirect()->route('admin.tahun-ajaran.index')
                ->with('success', "Periode {$label} berhasil dihapus.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function activate(AcademicPeriod $academicPeriod)
    {
        $this->periodService->activatePeriod($academicPeriod);

        return redirect()->route('admin.tahun-ajaran.index')
            ->with('success', "Periode aktif sekarang berpindah ke {$academicPeriod->label}.");
    }

    public function copySemester(Request $request)
    {
        $validated = $request->validate([
            'source_period_id' => ['required', 'exists:academic_periods,id'],
            'tahun_ajaran'     => ['required', 'string', 'max:20', 'regex:/^\d{4}\/\d{4}$/'],
            'semester'         => ['required', 'in:ganjil,genap'],
            'copy_pengampu'    => ['nullable', 'boolean'],
        ]);

        $from = AcademicPeriod::findOrFail($validated['source_period_id']);
        $to = $this->periodService->createPeriod(
            $validated['tahun_ajaran'],
            $validated['semester'],
            null,
            null,
            false
        );

        if ($from->id === $to->id) {
            return back()->with('error', 'Periode tujuan harus berbeda dari periode asal.');
        }

        $result = $this->periodService->copySemesterMemberships(
            $from,
            $to,
            (bool) ($validated['copy_pengampu'] ?? true),
            true
        );

        return redirect()->route('admin.tahun-ajaran.index')
            ->with('success', "Berhasil berpindah ke {$to->label}: {$result['copied_students']} keanggotaan siswa dan {$result['copied_pengampu']} pengampu mapel disalin.");
    }

    public function promote(Request $request)
    {
        $validated = $request->validate([
            'source_period_id'   => ['required', 'exists:academic_periods,id'],
            'tahun_ajaran'       => ['required', 'string', 'max:20', 'regex:/^\d{4}\/\d{4}$/'],
            'semester'           => ['required', 'in:ganjil,genap'],
            'mapping'            => ['required', 'array'],
            'stay_student_ids'   => ['nullable', 'array'],
            'stay_student_ids.*' => ['integer', 'exists:siswas,id'],
        ]);

        $from = AcademicPeriod::findOrFail($validated['source_period_id']);
        $to = $this->periodService->createPeriod(
            $validated['tahun_ajaran'],
            $validated['semester'],
            null,
            null,
            false
        );

        if ($from->id === $to->id) {
            return back()->with('error', 'Periode tujuan kenaikan kelas harus berbeda dari periode asal.');
        }

        $stayIds = array_map('intval', $validated['stay_student_ids'] ?? []);
        $result = $this->periodService->promoteStudents(
            $from,
            $to,
            $validated['mapping'],
            $stayIds,
            [],
            true
        );

        return redirect()->route('admin.tahun-ajaran.index')
            ->with('success', "Kenaikan kelas ke {$to->label} selesai: {$result['promoted']} naik kelas, {$result['stayed']} tinggal kelas, {$result['graduated']} lulus/alumni.");
    }
}
