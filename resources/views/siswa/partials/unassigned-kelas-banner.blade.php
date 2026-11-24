@if(empty($kelas))
    <div class="rounded-2xl border border-amber-200 bg-amber-50/90 p-4 sm:p-5 flex items-start gap-3.5 shadow-2xs" role="alert">
        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
        </div>
        <div class="space-y-1">
            <h3 class="text-sm font-bold text-amber-900">Belum Ditempatkan di Kelas</h3>
            <p class="text-xs sm:text-sm text-amber-800/90 leading-relaxed">
                Akun kamu saat ini belum terdaftar pada kelas mana pun untuk periode akademik aktif. Daftar mata pelajaran, materi, tugas, kuis, dan ujian akan otomatis muncul setelah operator/admin sekolah menempatkanmu ke kelas.
            </p>
        </div>
    </div>
@endif
