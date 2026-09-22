@extends('layouts.app')

@section('title', 'Materi Pelajaran')

@section('content')


@php
// Data fallback daftar mata pelajaran (dapat di-override langsung dari Controller melalui $daftarMapel)
$daftarMapel = $daftarMapel ?? [
    [
        'id' => 1,
        'nama' => 'Bahasa Indonesia',
        'guru' => 'Purwanto, M.Pd',
        'bg_color' => 'bg-[#DE5744]', // Merah Jingga / Terracotta
        'badge' => 'book',
        'illustration' => 'stationery'
    ],
    [
        'id' => 2,
        'nama' => 'IPS',
        'guru' => 'Purwanto, M.Pd',
        'bg_color' => 'bg-[#F4955B]', // Oranye Soft
        'badge' => 'globe',
        'illustration' => 'globe-easel'
    ],
    [
        'id' => 3,
        'nama' => 'IPA',
        'guru' => 'Purwanto, M.Pd',
        'bg_color' => 'bg-[#23A76B]', // Hijau Mint / Teal
        'badge' => 'beaker',
        'illustration' => 'lab-flask'
    ],
    [
        'id' => 4,
        'nama' => 'Bahasa Inggris',
        'guru' => 'Purwanto, M.Pd',
        'bg_color' => 'bg-[#2563EB]', // Biru Royal
        'badge' => 'text-aa',
        'illustration' => 'booth-speech'
    ],
    [
        'id' => 5,
        'nama' => 'Agama',
        'guru' => 'Purwanto, M.Pd',
        'bg_color' => 'bg-[#4D4B84]', // Ungu Tua / Indigo
        'badge' => 'dome',
        'illustration' => 'mosque'
    ],
    [
        'id' => 6,
        'nama' => 'Matematika',
        'guru' => 'Purwanto, M.Pd',
        'bg_color' => 'bg-[#E8A329]', // Kuning Kunyit / Gold
        'badge' => 'calc',
        'illustration' => 'math-ruler'
    ],
    [
        'id' => 7,
        'nama' => 'Pendidikan Kewarganegaraan',
        'guru' => 'Dra. Sri Wahyuni',
        'bg_color' => 'bg-[#D72841]', // Merah Crimson
        'badge' => 'shield',
        'illustration' => 'shield-star'
    ],
    [
        'id' => 8,
        'nama' => 'PJOK',
        'guru' => 'Budi Santoso, S.Pd',
        'bg_color' => 'bg-[#0F8B7E]', // Hijau Toska Tua
        'badge' => 'bolt',
        'illustration' => 'basketball'
    ],
    [
        'id' => 9,
        'nama' => 'Prakarya & KWU',
        'guru' => 'Hidayati, S.Pd',
        'bg_color' => 'bg-[#B531B7]', // Ungu Magenta
        'badge' => 'palette',
        'illustration' => 'art-palette'
    ],
    [
        'id' => 10,
        'nama' => 'Informatika / TIK',
        'guru' => 'Rizky Pratama, M.Kom',
        'bg_color' => 'bg-[#283747]', // Biru Gelap / Navy
        'badge' => 'code',
        'illustration' => 'laptop-code'
    ],
];
@endphp

<div class="w-full space-y-6">

  {{-- 1. Sub-Header Halaman --}}
  <header class="space-y-1">
    <h1 class="text-2xl font-bold tracking-tight text-slate-800">
      Materi Pelajaran
    </h1>
    <p class="text-sm text-gray-500 font-normal">
      Pilih mata pelajaran untuk melihat materi pembelajaran, rangkuman, dan modul interaktif.
    </p>
  </header>

  {{-- 2. Grid Card Mata Pelajaran (1 Kolom Mobile, 2 Kolom Tablet, 3 Kolom Desktop) --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 md:gap-6 pt-1">
    @foreach ($daftarMapel as $item)
      <a href="{{ route('siswa.materi.show', $item['id']) }}"
         class="group relative overflow-hidden rounded-3xl p-6 md:p-7 {{ $item['bg_color'] }} text-white shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 flex flex-col justify-between min-h-[165px] md:min-h-[175px]">
        
        {{-- Ikon Transparan Kecil di Pojok Kiri Atas --}}
        <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-white shadow-xs">
          @if ($item['badge'] === 'book')
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
          @elseif ($item['badge'] === 'globe')
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="9" stroke-width="2" stroke-linecap="round"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h18M12 3a14.5 14.5 0 0 1 0 18M12 3a14.5 14.5 0 0 0 0 18"/>
            </svg>
          @elseif ($item['badge'] === 'beaker')
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.414 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
          @elseif ($item['badge'] === 'text-aa')
            <span class="font-bold text-sm tracking-tight leading-none">Aa</span>
          @elseif ($item['badge'] === 'dome')
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v3m0 0a6 6 0 0 1 6 6v7a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-7a6 6 0 0 1 6-6zm0 0V3m-4 16h8"/>
            </svg>
          @elseif ($item['badge'] === 'calc')
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
          @elseif ($item['badge'] === 'shield')
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
          @elseif ($item['badge'] === 'bolt')
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
          @elseif ($item['badge'] === 'palette')
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
          @elseif ($item['badge'] === 'code')
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
            </svg>
          @endif
        </div>

        {{-- Nama Mata Pelajaran & Nama Guru --}}
        <div class="relative z-10 mt-6">
          <h2 class="text-xl font-bold tracking-tight text-white group-hover:translate-x-0.5 transition-transform duration-200">
            {{ $item['nama'] }}
          </h2>
          <p class="text-xs text-white/80 font-normal mt-1">
            {{ $item['guru'] }}
          </p>
        </div>

        {{-- Ilustrasi / Vektor Khas di Pojok Kanan Bawah --}}
        <div class="absolute -bottom-1 right-2 pointer-events-none select-none opacity-95 group-hover:scale-105 transition-transform duration-300">
          @if ($item['illustration'] === 'stationery')
            {{-- Bahasa Indonesia: Kotak pensil & penggaris --}}
            <svg class="w-24 h-24" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect x="26" y="52" width="56" height="26" rx="4" fill="#5890D8" />
              <rect x="24" y="47" width="60" height="9" rx="3" fill="#E8A736" />
              <line x1="42" y1="56" x2="42" y2="78" stroke="#3A6FB5" stroke-width="2" stroke-linecap="round" />
              <line x1="58" y1="56" x2="58" y2="78" stroke="#3A6FB5" stroke-width="2" stroke-linecap="round" />
              <ellipse cx="54" cy="79" rx="30" ry="4" fill="#000000" fill-opacity="0.15" />
              <rect x="65" y="20" width="12" height="34" rx="2" transform="rotate(15 65 20)" fill="#FFFFFF" fill-opacity="0.9" />
              <line x1="68" y1="26" x2="72" y2="27" stroke="#94A3B8" stroke-width="1.5" />
              <line x1="70" y1="33" x2="74" y2="34" stroke="#94A3B8" stroke-width="1.5" />
              <line x1="72" y1="40" x2="76" y2="41" stroke="#94A3B8" stroke-width="1.5" />
              <path d="M48 20L53 14L58 19L53 25L48 20Z" fill="#F43F5E" />
              <path d="M53 14L55 11L57 13L55 15L53 14Z" fill="#1E293B" />
              <rect x="36" y="27" width="8" height="28" transform="rotate(-30 36 27)" fill="#FBBF24" />
            </svg>

          @elseif ($item['illustration'] === 'globe-easel')
            {{-- IPS: Globe & Easel --}}
            <svg class="w-24 h-24" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
              <line x1="28" y1="78" x2="44" y2="35" stroke="#FFFFFF" stroke-opacity="0.4" stroke-width="3" stroke-linecap="round" />
              <line x1="56" y1="78" x2="46" y2="35" stroke="#FFFFFF" stroke-opacity="0.4" stroke-width="3" stroke-linecap="round" />
              <line x1="33" y1="62" x2="52" y2="62" stroke="#FFFFFF" stroke-opacity="0.4" stroke-width="2" stroke-linecap="round" />
              <ellipse cx="68" cy="80" rx="12" ry="3" fill="#FFFFFF" fill-opacity="0.75" />
              <rect x="66" y="68" width="4" height="12" rx="2" fill="#FFFFFF" fill-opacity="0.9" />
              <path d="M54 52C54 62 61 69 71 68C76 67.5 81 64 83 58" stroke="#FFFFFF" stroke-opacity="0.9" stroke-width="3" stroke-linecap="round" fill="none" />
              <circle cx="68" cy="50" r="16" fill="#8CE1FF" />
              <path d="M62 44C65 42 70 43 73 40C76 37 78 41 80 43C78 48 76 53 71 55C66 57 60 52 62 44Z" fill="#78C679" fill-opacity="0.8" />
              <ellipse cx="68" cy="50" rx="16" ry="16" stroke="#FFFFFF" stroke-opacity="0.4" stroke-width="1.5" />
            </svg>

          @elseif ($item['illustration'] === 'lab-flask')
            {{-- IPA: Tabung Erlenmeyer & Molekul --}}
            <svg class="w-24 h-24" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="28" cy="46" r="4" fill="#FBBF24" />
              <circle cx="34" cy="56" r="3.5" fill="#60A5FA" />
              <circle cx="26" cy="62" r="2.5" fill="#F87171" />
              <line x1="28" y1="46" x2="34" y2="56" stroke="#FFFFFF" stroke-opacity="0.5" stroke-width="1.5" />
              <line x1="34" y1="56" x2="26" y2="62" stroke="#FFFFFF" stroke-opacity="0.5" stroke-width="1.5" />
              <path d="M57 32H67V44L82 72C83.5 75 81.5 78 78 78H46C42.5 78 40.5 75 42 72L57 44V32Z" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none" />
              <path d="M47 64L44 71C43 73 44.5 75 46.5 75H77.5C79.5 75 81 73 80 71L77 64C72 66 68 63 62 63C56 63 52 65 47 64Z" fill="#A7F3D0" fill-opacity="0.75" />
              <circle cx="60" cy="69" r="2" fill="#FFFFFF" fill-opacity="0.8" />
              <circle cx="68" cy="71" r="1.5" fill="#FFFFFF" fill-opacity="0.8" />
              <line x1="55" y1="32" x2="69" y2="32" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" />
            </svg>

          @elseif ($item['illustration'] === 'booth-speech')
            {{-- Bahasa Inggris: Kotak Telepon Merah & Balon Kata --}}
            <svg class="w-24 h-24" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect x="30" y="22" width="22" height="17" rx="5" fill="#FFFFFF" />
              <polygon points="36,39 42,39 34,44" fill="#FFFFFF" />
              <text x="35" y="34" font-size="9" font-weight="bold" fill="#2563EB" font-family="sans-serif">Aa</text>
              <path d="M58 36C58 32 63 30 71 30C79 30 84 32 84 36V79H58V36Z" fill="#DC2626" />
              <rect x="62" y="38" width="8" height="15" fill="#FFFFFF" rx="1.5" />
              <rect x="72" y="38" width="8" height="15" fill="#FFFFFF" rx="1.5" />
              <rect x="62" y="56" width="8" height="15" fill="#FFFFFF" rx="1.5" />
              <rect x="72" y="56" width="8" height="15" fill="#FFFFFF" rx="1.5" />
              <line x1="56" y1="79" x2="86" y2="79" stroke="#991B1B" stroke-width="3" stroke-linecap="round" />
            </svg>

          @elseif ($item['illustration'] === 'mosque')
            {{-- Agama: Kubah & Menara Masjid --}}
            <svg class="w-24 h-24" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="48" cy="32" r="4.5" fill="#FEF08A" />
              <circle cx="50" cy="31" r="4" fill="#4D4B84" />
              <ellipse cx="43" cy="38" rx="6" ry="2.5" fill="#FFFFFF" fill-opacity="0.3" />
              <rect x="71" y="44" width="9" height="36" rx="2" fill="#8E8DA8" />
              <path d="M70 44L75.5 35L81 44H70Z" fill="#FDE047" />
              <circle cx="75.5" cy="33" r="1.5" fill="#FDE047" />
              <rect x="69" y="58" width="13" height="3" rx="1.5" fill="#6C698E" />
              <path d="M44 80V63C44 54 50 51 56 51C62 51 68 54 68 63V80H44Z" fill="#757398" />
              <line x1="56" y1="51" x2="56" y2="47" stroke="#FDE047" stroke-width="2" stroke-linecap="round" />
              <circle cx="56" cy="46" r="1.5" fill="#FDE047" />
            </svg>

          @elseif ($item['illustration'] === 'math-ruler')
            {{-- Matematika: Penggaris Segitiga & Simbol Matematika --}}
            <svg class="w-24 h-24" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
              <text x="44" y="38" font-size="11" font-family="sans-serif" font-weight="bold" fill="#FFFFFF" fill-opacity="0.7">π</text>
              <text x="66" y="34" font-size="10" font-family="sans-serif" font-weight="bold" fill="#FFFFFF" fill-opacity="0.7">√x</text>
              <text x="38" y="55" font-size="12" font-family="sans-serif" font-weight="bold" fill="#FFFFFF" fill-opacity="0.7">%</text>
              <text x="54" y="56" font-size="11" font-family="sans-serif" font-weight="bold" fill="#FFFFFF" fill-opacity="0.7">±</text>
              <polygon points="52,80 90,80 90,44" fill="#FFFFFF" fill-opacity="0.9" />
              <polygon points="62,76 86,76 86,54" fill="#E8A329" />
              <line x1="56" y1="80" x2="56" y2="78" stroke="#D97706" stroke-width="1.5" />
              <line x1="64" y1="80" x2="64" y2="78" stroke="#D97706" stroke-width="1.5" />
              <line x1="72" y1="80" x2="72" y2="78" stroke="#D97706" stroke-width="1.5" />
              <line x1="80" y1="80" x2="80" y2="78" stroke="#D97706" stroke-width="1.5" />
            </svg>

          @elseif ($item['illustration'] === 'shield-star')
            {{-- PKn: Perisai Bintang Pancasila --}}
            <svg class="w-24 h-24" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M36 68C42 74 48 72 52 68" stroke="#FFFFFF" stroke-opacity="0.8" stroke-width="2.5" stroke-linecap="round" fill="none" />
              <path d="M54 36C64 36 78 30 78 30C78 54 74 68 54 78C34 68 30 54 30 30C30 30 44 36 54 36Z" fill="#B91C1C" stroke="#FFFFFF" stroke-width="3" stroke-linejoin="round" />
              <path d="M54 41C61 41 72 37 72 37C72 54 69 64 54 72C39 64 36 54 36 37C36 37 47 41 54 41Z" fill="#DC2626" />
              <polygon points="54,46 56.5,53 64,53 58,57.5 60.5,64.5 54,60 47.5,64.5 50,57.5 44,53 51.5,53" fill="#FACC15" />
            </svg>

          @elseif ($item['illustration'] === 'basketball')
            {{-- PJOK: Bola Basket & Peluit --}}
            <svg class="w-24 h-24" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect x="24" y="66" width="14" height="7" rx="3" fill="#6EE7B7" />
              <circle cx="38" cy="70" r="5" fill="#34D399" />
              <circle cx="28" cy="69" r="1.5" fill="#064E3B" />
              <circle cx="68" cy="62" r="18" fill="#F97316" />
              <line x1="50" y1="62" x2="86" y2="62" stroke="#FFFFFF" stroke-width="1.8" />
              <line x1="68" y1="44" x2="68" y2="80" stroke="#FFFFFF" stroke-width="1.8" />
              <path d="M57 49C63 55 63 69 57 75" stroke="#FFFFFF" stroke-width="1.8" fill="none" />
              <path d="M79 49C73 55 73 69 79 75" stroke="#FFFFFF" stroke-width="1.8" fill="none" />
            </svg>

          @elseif ($item['illustration'] === 'art-palette')
            {{-- Prakarya & KWU: Palet Warna & Kuas --}}
            <svg class="w-24 h-24" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M48 46C56 42 66 43 72 49C78 55 83 66 76 74C71 79 59 81 51 77C45 74 38 77 34 71C30 65 34 52 48 46Z" fill="#FFFFFF" fill-opacity="0.9" />
              <circle cx="44" cy="68" r="3.5" fill="#B531B7" />
              <circle cx="56" cy="51" r="3" fill="#3B82F6" />
              <circle cx="68" cy="56" r="3" fill="#F43F5E" />
              <circle cx="71" cy="67" r="3" fill="#EAB308" />
              <circle cx="58" cy="73" r="3" fill="#10B981" />
              <line x1="30" y1="46" x2="52" y2="64" stroke="#FDE047" stroke-width="3" stroke-linecap="round" />
              <path d="M26 43L30 46L27 49L23 45L26 43Z" fill="#1E293B" />
            </svg>

          @elseif ($item['illustration'] === 'laptop-code')
            {{-- Informatika / TIK: Laptop & Kode </> --}}
            <svg class="w-24 h-24" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect x="42" y="36" width="38" height="26" rx="3" fill="#3B82F6" stroke="#60A5FA" stroke-width="1.5" />
              <text x="51" y="53" font-size="12" font-family="monospace" font-weight="bold" fill="#FFFFFF">&lt;/&gt;</text>
              <path d="M34 64L88 64C89.5 64 90 65.5 89 67L84 72C83.5 72.5 82.5 73 81.5 73H40.5C39.5 73 38.5 72.5 38 72L33 67C32 65.5 32.5 64 34 64Z" fill="#64748B" />
              <rect x="56" y="65" width="10" height="2" rx="1" fill="#94A3B8" />
            </svg>
          @endif
        </div>

      </a>
    @endforeach
  </div>

</div>
@endsection
