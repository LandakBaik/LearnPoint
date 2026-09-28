<?php

namespace App\Http\Controllers;

use App\Models\AnggotaKelas;
use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\PengampuKelas;
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
            $guruMapels = $guru->guruMapels()
                ->has('pengampuKelases')
                ->with(['mapel', 'pengampuKelases.kelas'])
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

        // 2. Data Jadwal Guru (dari Guru & GuruMapel yang aktif mengajar di kelas)
        $gurus = Guru::with([
            'guruMapels' => function ($q) {
                $q->has('pengampuKelases')->with(['mapel', 'pengampuKelases.kelas']);
            }
        ])->latest()->get();

        $mapels = Mapel::orderBy('nama_mapel')->get();

        $activeTab = $request->query('tab', 'kelas');

        return view('admin.jadwal.index', compact('kelases', 'gurus', 'mapels', 'activeTab'));
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
        if ($kelas->jadwal && Storage::disk('public')->exists($kelas->jadwal)) {
            Storage::disk('public')->delete($kelas->jadwal);
        }

        // Simpan langsung pada tabel kelases
        $kelas->update(['jadwal' => $path]);

        return redirect()->route('admin.jadwal.index', ['tab' => 'kelas'])
            ->with('success', "Foto jadwal untuk kelas {$kelas->nama_kelas} berhasil diunggah!");
    }

    /**
     * Upload/Ganti Foto Jadwal Guru (Spesifik per Mata Pelajaran).
     */
    public function uploadJadwalGuru(Request $request)
    {
        $request->validate([
            'guru_id'           => 'required|exists:gurus,id',
            'guru_mapel_id'     => 'nullable',
            'mapel_id'          => 'nullable|exists:mapels,id',
            'foto_jadwal'       => 'required|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $guru = Guru::findOrFail($request->guru_id);
        $path = $request->file('foto_jadwal')->store('jadwal/guru', 'public');

        // 1. Jika dipilih guru_mapel_id spesifik (ID numerik valid)
        if ($request->filled('guru_mapel_id') && is_numeric($request->guru_mapel_id) && (int)$request->guru_mapel_id > 0) {
            $guruMapel = GuruMapel::where('guru_id', $guru->id)->findOrFail($request->guru_mapel_id);
            if ($guruMapel->jadwal && Storage::disk('public')->exists($guruMapel->jadwal)) {
                Storage::disk('public')->delete($guruMapel->jadwal);
            }
            $guruMapel->update(['jadwal' => $path]);
            $mapelName = $guruMapel->mapel->nama_mapel ?? 'Mata Pelajaran';
            $message = "Foto jadwal mata pelajaran {$mapelName} untuk Guru {$guru->nama} berhasil diunggah!";
        }
        // 2. Jika dipilih opsi tambah mapel baru (mapel_id dikirim)
        elseif ($request->filled('mapel_id')) {
            $guruMapel = GuruMapel::firstOrCreate(
                ['guru_id' => $guru->id, 'mapel_id' => $request->mapel_id]
            );
            if ($guruMapel->jadwal && Storage::disk('public')->exists($guruMapel->jadwal)) {
                Storage::disk('public')->delete($guruMapel->jadwal);
            }
            $guruMapel->update(['jadwal' => $path]);
            $mapelName = $guruMapel->mapel->nama_mapel ?? 'Mata Pelajaran';
            $message = "Foto jadwal mata pelajaran {$mapelName} untuk Guru {$guru->nama} berhasil diunggah!";
        }
        // 3. Fallback jika tidak memilih spesifik
        else {
            $firstGm = $guru->guruMapels()->has('pengampuKelases')->first();
            if ($firstGm) {
                if ($firstGm->jadwal && Storage::disk('public')->exists($firstGm->jadwal)) {
                    Storage::disk('public')->delete($firstGm->jadwal);
                }
                $firstGm->update(['jadwal' => $path]);
                $mapelName = $firstGm->mapel->nama_mapel ?? 'Mata Pelajaran';
                $message = "Foto jadwal mata pelajaran {$mapelName} untuk Guru {$guru->nama} berhasil diunggah!";
            } else {
                $firstMapel = Mapel::first();
                if ($firstMapel) {
                    $gm = GuruMapel::firstOrCreate([
                        'guru_id'  => $guru->id,
                        'mapel_id' => $firstMapel->id,
                    ]);
                    if ($gm->jadwal && Storage::disk('public')->exists($gm->jadwal)) {
                        Storage::disk('public')->delete($gm->jadwal);
                    }
                    $gm->update(['jadwal' => $path]);
                }
                $message = "Foto jadwal mengajar untuk Guru {$guru->nama} berhasil diunggah!";
            }
        }

        return redirect()->route('admin.jadwal.index', ['tab' => 'guru'])
            ->with('success', $message);
    }

    /**
     * Hapus Foto Jadwal Kelas.
     */
    public function destroyJadwalKelas(Kelas $kelas)
    {
        if ($kelas->jadwal && Storage::disk('public')->exists($kelas->jadwal)) {
            Storage::disk('public')->delete($kelas->jadwal);
        }

        $kelas->update(['jadwal' => null]);

        return redirect()->route('admin.jadwal.index', ['tab' => 'kelas'])
            ->with('success', "Foto jadwal kelas {$kelas->nama_kelas} berhasil dihapus!");
    }

    /**
     * Hapus Foto Jadwal Guru (Per GuruMapel atau Per Guru).
     */
    public function destroyJadwalGuru($id)
    {
        $guruMapel = GuruMapel::find($id);
        if ($guruMapel) {
            if ($guruMapel->jadwal && Storage::disk('public')->exists($guruMapel->jadwal)) {
                Storage::disk('public')->delete($guruMapel->jadwal);
            }
            $guruMapel->update(['jadwal' => null]);
            $mapelName = $guruMapel->mapel->nama_mapel ?? 'Mata Pelajaran';
            $message = "Foto jadwal mapel {$mapelName} berhasil dihapus!";
        } else {
            $guru = Guru::find($id);
            if ($guru) {
                foreach ($guru->guruMapels as $gm) {
                    if ($gm->jadwal && Storage::disk('public')->exists($gm->jadwal)) {
                        Storage::disk('public')->delete($gm->jadwal);
                    }
                    $gm->update(['jadwal' => null]);
                }
            }
            $message = "Foto jadwal guru berhasil dihapus!";
        }

        return redirect()->route('admin.jadwal.index', ['tab' => 'guru'])
            ->with('success', $message);
    }
}
