<?php

namespace App\Http\Controllers;

use App\Models\GuruMapel;
use App\Models\PengampuKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GuruMapelController extends Controller
{
    /**
     * Simpan penugasan guru pengampu mapel pada kelas tertentu.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'guru_id'     => 'required|exists:gurus,id',
            'mapel_id'    => 'required|exists:mapels,id',
            'kelas_ids'   => 'nullable|array',
            'kelas_ids.*' => 'exists:kelases,id',
            'kelas_id'    => 'nullable|exists:kelases,id',
        ]);

        $kelasIds = $request->input('kelas_ids', []);
        if ($request->filled('kelas_id') && !in_array($request->kelas_id, $kelasIds)) {
            $kelasIds[] = $request->kelas_id;
        }

        if (empty($kelasIds)) {
            return back()->withErrors(['kelas_ids' => 'Pilih minimal satu kelas untuk diampu.']);
        }

        $guruMapel = GuruMapel::firstOrCreate([
            'guru_id'  => $validated['guru_id'],
            'mapel_id' => $validated['mapel_id'],
        ]);

        $addedCount = 0;
        foreach ($kelasIds as $kelasId) {
            $exists = PengampuKelas::where('guru_mapel_id', $guruMapel->id)
                ->where('kelas_id', $kelasId)
                ->exists();

            if (!$exists) {
                PengampuKelas::create([
                    'guru_mapel_id' => $guruMapel->id,
                    'kelas_id'      => $kelasId,
                ]);
                $addedCount++;
            }
        }

        if ($addedCount === 0) {
            return back()->withErrors(['kelas_ids' => 'Guru ini sudah terdaftar sebagai pengampu di seluruh kelas yang Anda pilih.']);
        }

        return back()->with('success', "Berhasil menugaskan guru ke {$addedCount} kelas!");
    }

    /**
     * Hapus penugasan guru pengampu mapel pada kelas tertentu.
     */
    public function destroy(PengampuKelas $guruMapel)
    {
        $parentGuruMapel = $guruMapel->guruMapel;
        $guruMapel->delete();

        // Jika guru sudah tidak mengajar di kelas mana pun untuk mapel ini,
        // bersihkan data guru_mapel dan file fotonya agar tidak menggantung di kelola jadwal.
        if ($parentGuruMapel && $parentGuruMapel->pengampuKelases()->count() === 0) {
            if ($parentGuruMapel->jadwal && Storage::disk('public')->exists($parentGuruMapel->jadwal)) {
                Storage::disk('public')->delete($parentGuruMapel->jadwal);
            }
            $parentGuruMapel->delete();
        }

        return back()->with('success', 'Penugasan guru pengampu mapel berhasil dihapus!');
    }
}
