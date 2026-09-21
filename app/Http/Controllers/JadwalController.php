<?php

namespace App\Http\Controllers;

use App\Models\AnggotaKelas;
use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\Mapel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class JadwalController extends Controller
{
    /**
     * Halaman Jadwal Mengajar Guru (/guru/jadwal).
     */
    public function index()
    {
        $user = Auth::user();
        $guru = $user->guru;

        $guruMapels = collect();
        $jadwalUrl = null;

        if ($guru) {
            $guruMapels = GuruMapel::with(['mapel', 'kelas'])
                ->where('guru_id', $guru->id)
                ->get();

            $jadwalUrl = $guru->jadwal_url;
        }

        return view('Guru.Jadwal', compact('guru', 'guruMapels', 'jadwalUrl'));
    }

    /**
     * Halaman Pengelolaan Jadwal untuk Admin/Operator.
     */
    public function adminIndex(Request $request)
    {
        // 1. Data Jadwal Kelas
        $kelases = Kelas::with(['guru', 'siswas', 'anggotaKelases'])
            ->latest()
            ->get();

        // 2. Data Jadwal Guru (dari Guru & GuruMapel)
        $gurus = Guru::with(['guruMapels.mapel', 'guruMapels.kelas'])
            ->latest()
            ->get();

        $guruMapels = GuruMapel::with(['guru', 'mapel', 'kelas'])
            ->latest()
            ->get();

        $mapels = Mapel::all();

        $activeTab = $request->query('tab', 'kelas');

        return view('admin.jadwal.index', compact('kelases', 'gurus', 'guruMapels', 'mapels', 'activeTab'));
    }

    /**
     * Upload/Ganti Foto Jadwal Kelas.
     */
    public function uploadJadwalKelas(Request $request)
    {
        $request->validate([
            'kelas_id'    => 'required|exists:kelases,id',
            'foto_jadwal' => 'required|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $kelas = Kelas::findOrFail($request->kelas_id);

        // Upload file baru
        $path = $request->file('foto_jadwal')->store('jadwal/kelas', 'public');

        // Hapus file lama jika ada
        $oldJadwal = AnggotaKelas::where('kelas_id', $kelas->id)
            ->whereNotNull('jadwal')
            ->value('jadwal');

        if ($oldJadwal && Storage::disk('public')->exists($oldJadwal)) {
            Storage::disk('public')->delete($oldJadwal);
        }

        // Perbarui semua anggota kelas di kelas tersebut
        $anggotaCount = AnggotaKelas::where('kelas_id', $kelas->id)->count();

        if ($anggotaCount > 0) {
            AnggotaKelas::where('kelas_id', $kelas->id)->update(['jadwal' => $path]);
        } else {
            // Jika ada siswa di kelas tersebut tapi belum tercatat di anggota_kelases
            $siswas = $kelas->siswas;
            foreach ($siswas as $siswa) {
                AnggotaKelas::create([
                    'kelas_id'     => $kelas->id,
                    'siswa_id'     => $siswa->id,
                    'tahun_ajaran' => '2026/2027',
                    'semester'     => 'ganjil',
                    'jadwal'       => $path,
                ]);
            }
        }

        return redirect()->route('admin.jadwal.index', ['tab' => 'kelas'])
            ->with('success', "Foto jadwal untuk kelas {$kelas->nama_kelas} berhasil diunggah!");
    }

    /**
     * Upload/Ganti Foto Jadwal Guru.
     */
    public function uploadJadwalGuru(Request $request)
    {
        $request->validate([
            'guru_id'       => 'required|exists:gurus,id',
            'guru_mapel_id' => 'nullable|exists:guru_mapels,id',
            'foto_jadwal'   => 'required|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $guru = Guru::findOrFail($request->guru_id);
        $path = $request->file('foto_jadwal')->store('jadwal/guru', 'public');

        if ($request->filled('guru_mapel_id')) {
            $guruMapel = GuruMapel::findOrFail($request->guru_mapel_id);
            if ($guruMapel->jadwal && Storage::disk('public')->exists($guruMapel->jadwal)) {
                Storage::disk('public')->delete($guruMapel->jadwal);
            }
            $guruMapel->update(['jadwal' => $path]);
        } else {
            // Update ke seluruh guru_mapel guru tersebut atau guru_mapel pertama
            $oldJadwal = $guru->jadwal;
            if ($oldJadwal && Storage::disk('public')->exists($oldJadwal)) {
                Storage::disk('public')->delete($oldJadwal);
            }

            if ($guru->guruMapels()->count() > 0) {
                $guru->guruMapels()->update(['jadwal' => $path]);
            } else {
                // Jika belum punya penugasan guru_mapel, buatkan penugasan umum dengan mapel & kelas pertama
                $firstMapel = Mapel::first();
                $firstKelas = Kelas::first();
                if ($firstMapel && $firstKelas) {
                    GuruMapel::create([
                        'guru_id'  => $guru->id,
                        'mapel_id' => $firstMapel->id,
                        'kelas_id' => $firstKelas->id,
                        'jadwal'   => $path,
                    ]);
                }
            }
        }

        return redirect()->route('admin.jadwal.index', ['tab' => 'guru'])
            ->with('success', "Foto jadwal mengajar untuk Guru {$guru->nama} berhasil diunggah!");
    }

    /**
     * Hapus Foto Jadwal Kelas.
     */
    public function destroyJadwalKelas(Kelas $kelas)
    {
        $jadwal = AnggotaKelas::where('kelas_id', $kelas->id)
            ->whereNotNull('jadwal')
            ->value('jadwal');

        if ($jadwal && Storage::disk('public')->exists($jadwal)) {
            Storage::disk('public')->delete($jadwal);
        }

        AnggotaKelas::where('kelas_id', $kelas->id)->update(['jadwal' => null]);

        return redirect()->route('admin.jadwal.index', ['tab' => 'kelas'])
            ->with('success', "Foto jadwal kelas {$kelas->nama_kelas} berhasil dihapus!");
    }

    /**
     * Hapus Foto Jadwal Guru.
     */
    public function destroyJadwalGuru(GuruMapel $guruMapel)
    {
        if ($guruMapel->jadwal && Storage::disk('public')->exists($guruMapel->jadwal)) {
            Storage::disk('public')->delete($guruMapel->jadwal);
        }

        $guruMapel->update(['jadwal' => null]);

        return redirect()->route('admin.jadwal.index', ['tab' => 'guru'])
            ->with('success', "Foto jadwal guru berhasil dihapus!");
    }
}
