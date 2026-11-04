@php
    $planetKey = strtolower($planet->key ?? $planet->nama ?? '');
    $planetNama = strtolower($planet->nama ?? '');
    $sizeClass = $size ?? 'w-14 h-14';
@endphp

@if(str_contains($planetKey, 'matematika') || str_contains($planetNama, 'matematika') || str_contains($planetKey, 'mtk'))
    {{-- ======================================================== --}}
    {{-- PLANET MATEMATIKA (Cosmic Ice & Crystalline Ring Planet) --}}
    {{-- ======================================================== --}}
    <svg class="{{ $sizeClass }} drop-shadow-md shrink-0 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6 select-none" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <radialGradient id="mathPlanetGrad-{{ $planet->id ?? 'def' }}" cx="35%" cy="30%" r="65%">
                <stop offset="0%" stop-color="#BFDBFE"/>
                <stop offset="35%" stop-color="#3B82F6"/>
                <stop offset="75%" stop-color="#1D4ED8"/>
                <stop offset="100%" stop-color="#0F2851"/>
            </radialGradient>
            <linearGradient id="mathRingGrad-{{ $planet->id ?? 'def' }}" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#BFDBFE" stop-opacity="0.95"/>
                <stop offset="40%" stop-color="#60A5FA" stop-opacity="0.8"/>
                <stop offset="75%" stop-color="#93C5FD" stop-opacity="0.9"/>
                <stop offset="100%" stop-color="#DBEAFE" stop-opacity="0.6"/>
            </linearGradient>
            <clipPath id="mathPlanetClip-{{ $planet->id ?? 'def' }}">
                <circle cx="36" cy="36" r="22"/>
            </clipPath>
        </defs>

        <!-- Ambient Stars -->
        <circle cx="14" cy="18" r="1.2" fill="#93C5FD" opacity="0.8"/>
        <circle cx="58" cy="16" r="1.5" fill="#BFDBFE" opacity="0.9"/>
        <circle cx="62" cy="48" r="1.2" fill="#60A5FA" opacity="0.7"/>

        <!-- Back Half of Ring -->
        <path d="M10 39 C 10 27, 62 17, 62 29" stroke="url(#mathRingGrad-{{ $planet->id ?? 'def' }})" stroke-width="4.5" stroke-linecap="round" opacity="0.6"/>
        <path d="M12 40 C 12 29, 60 19, 60 30" stroke="#E0F2FE" stroke-width="1.5" stroke-linecap="round" opacity="0.8"/>

        <!-- Main Planet Sphere -->
        <circle cx="36" cy="36" r="22" fill="url(#mathPlanetGrad-{{ $planet->id ?? 'def' }})"/>

        <!-- Surface Cosmic Bands (Clipped to Planet) -->
        <g clip-path="url(#mathPlanetClip-{{ $planet->id ?? 'def' }})">
            <path d="M14 31 Q 36 41 58 31" stroke="#60A5FA" stroke-width="3" opacity="0.35" fill="none"/>
            <path d="M15 38 Q 36 48 57 38" stroke="#1E40AF" stroke-width="2.5" opacity="0.5" fill="none"/>
            <path d="M18 45 Q 36 53 54 45" stroke="#93C5FD" stroke-width="1.8" opacity="0.4" fill="none"/>
            <path d="M14 36 Q 36 45 58 36" stroke="#0B1E3B" stroke-width="3" opacity="0.45" fill="none"/>
            <circle cx="28" cy="26" r="3" fill="#60A5FA" opacity="0.3"/>
            <circle cx="44" cy="44" r="2" fill="#1E40AF" opacity="0.4"/>
        </g>

        <!-- Front Half of Ring -->
        <path d="M10 39 C 10 47, 62 39, 62 29" stroke="url(#mathRingGrad-{{ $planet->id ?? 'def' }})" stroke-width="4.5" stroke-linecap="round"/>
        <path d="M12 40 C 12 47, 60 40, 60 30" stroke="#FFFFFF" stroke-width="1.5" stroke-linecap="round" opacity="0.9"/>

        <!-- Orbiting Mini Moon -->
        <circle cx="16" cy="22" r="3" fill="#E0F2FE"/>
        <circle cx="16" cy="22" r="1.5" fill="#93C5FD"/>
    </svg>

@elseif(str_contains($planetKey, 'ipa') || str_contains($planetNama, 'ipa') || str_contains($planetNama, 'alam') || str_contains($planetNama, 'biologi') || str_contains($planetNama, 'fisika'))
    {{-- ======================================================== --}}
    {{-- PLANET IPA (Lush Biosphere Terra / Nature Emerald Planet)--}}
    {{-- ======================================================== --}}
    <svg class="{{ $sizeClass }} drop-shadow-md shrink-0 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6 select-none" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <radialGradient id="ipaOceanGrad-{{ $planet->id ?? 'def' }}" cx="35%" cy="30%" r="65%">
                <stop offset="0%" stop-color="#A7F3D0"/>
                <stop offset="30%" stop-color="#10B981"/>
                <stop offset="75%" stop-color="#059669"/>
                <stop offset="100%" stop-color="#064E3B"/>
            </radialGradient>
            <clipPath id="ipaPlanetClip-{{ $planet->id ?? 'def' }}">
                <circle cx="36" cy="36" r="22"/>
            </clipPath>
        </defs>

        <!-- Ambient Stars -->
        <circle cx="12" cy="24" r="1.2" fill="#A7F3D0" opacity="0.8"/>
        <circle cx="58" cy="18" r="1.5" fill="#6EE7B7" opacity="0.7"/>
        <circle cx="60" cy="52" r="1.2" fill="#D1FAE5" opacity="0.8"/>

        <!-- Atmosphere Aura Glow -->
        <circle cx="36" cy="36" r="23" stroke="#A7F3D0" stroke-width="1.5" opacity="0.4"/>

        <!-- Main Planet Sphere (Oceans) -->
        <circle cx="36" cy="36" r="22" fill="url(#ipaOceanGrad-{{ $planet->id ?? 'def' }})"/>

        <!-- Landmasses & Flora Continents (Clipped) -->
        <g clip-path="url(#ipaPlanetClip-{{ $planet->id ?? 'def' }})">
            <!-- Continent North-West -->
            <path d="M22 24 C 25 20, 34 22, 33 28 C 32 32, 26 33, 22 30 Z" fill="#34D399" opacity="0.9"/>
            <path d="M24 23 C 27 21, 31 23, 30 26 C 29 28, 25 28, 24 26 Z" fill="#D1FAE5" opacity="0.6"/>

            <!-- Continent East -->
            <path d="M38 22 C 45 20, 52 26, 49 34 C 47 38, 42 39, 40 33 C 38 29, 36 26, 38 22 Z" fill="#34D399" opacity="0.95"/>
            <path d="M42 24 C 46 23, 49 28, 46 32 C 44 34, 41 33, 41 29 Z" fill="#ECFDF5" opacity="0.65"/>

            <!-- Continent South -->
            <path d="M26 38 C 30 36, 36 39, 38 45 C 37 51, 28 52, 25 46 C 23 42, 24 39, 26 38 Z" fill="#059669" opacity="0.85"/>
            <path d="M28 41 C 31 39, 34 41, 35 45 C 33 48, 29 48, 27 44 Z" fill="#6EE7B7" opacity="0.7"/>

            <!-- Cloud Swirls / Atmosphere -->
            <path d="M16 32 Q 24 34 32 30 Q 42 26 54 33" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" opacity="0.45" fill="none"/>
            <path d="M20 44 Q 30 46 40 42 Q 48 40 56 46" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" opacity="0.35" fill="none"/>

            <!-- 3D Planet Shadow -->
            <path d="M14 36 A 22 22 0 0 0 58 36 A 22 14 0 0 1 14 36 Z" fill="#022C22" opacity="0.35" transform="rotate(-30 36 36)"/>
        </g>

        <!-- Orbit Line & Moon -->
        <ellipse cx="36" cy="36" rx="28" ry="12" stroke="#6EE7B7" stroke-width="0.8" stroke-dasharray="3 3" opacity="0.35" transform="rotate(-15 36 36)"/>
        <circle cx="58" cy="26" r="3.5" fill="#D1FAE5"/>
        <circle cx="57" cy="25" r="1.5" fill="#A7F3D0"/>
    </svg>

@elseif(str_contains($planetKey, 'ips') || str_contains($planetNama, 'ips') || str_contains($planetNama, 'sosial') || str_contains($planetNama, 'sejarah') || str_contains($planetNama, 'geografi'))
    {{-- ======================================================== --}}
    {{-- PLANET IPS (Cosmic Violet / Civilization Amethyst World)  --}}
    {{-- ======================================================== --}}
    <svg class="{{ $sizeClass }} drop-shadow-md shrink-0 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6 select-none" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <radialGradient id="ipsPlanetGrad-{{ $planet->id ?? 'def' }}" cx="35%" cy="30%" r="65%">
                <stop offset="0%" stop-color="#DDD6FE"/>
                <stop offset="35%" stop-color="#8B5CF6"/>
                <stop offset="75%" stop-color="#6D28D9"/>
                <stop offset="100%" stop-color="#3B0764"/>
            </radialGradient>
            <linearGradient id="ipsRingGrad-{{ $planet->id ?? 'def' }}" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#F472B6" stop-opacity="0.95"/>
                <stop offset="45%" stop-color="#C084FC" stop-opacity="0.85"/>
                <stop offset="80%" stop-color="#E879F9" stop-opacity="0.9"/>
                <stop offset="100%" stop-color="#FCE7F3" stop-opacity="0.65"/>
            </linearGradient>
            <clipPath id="ipsPlanetClip-{{ $planet->id ?? 'def' }}">
                <circle cx="36" cy="36" r="22"/>
            </clipPath>
        </defs>

        <!-- Ambient Stars -->
        <circle cx="15" cy="16" r="1.5" fill="#F472B6" opacity="0.8"/>
        <circle cx="56" cy="14" r="1.2" fill="#E9D5FF" opacity="0.75"/>
        <circle cx="62" cy="46" r="1.5" fill="#C084FC" opacity="0.8"/>

        <!-- Back Half of Dual Ring -->
        <path d="M8 33 C 8 20, 64 25, 64 38" stroke="url(#ipsRingGrad-{{ $planet->id ?? 'def' }})" stroke-width="4.5" stroke-linecap="round" opacity="0.5"/>
        <path d="M12 33 C 12 23, 60 27, 60 37" stroke="#FDF4FF" stroke-width="1.3" stroke-linecap="round" opacity="0.75"/>

        <!-- Main Planet Sphere -->
        <circle cx="36" cy="36" r="22" fill="url(#ipsPlanetGrad-{{ $planet->id ?? 'def' }})"/>

        <!-- Gas Giant Curved Bands (Clipped) -->
        <g clip-path="url(#ipsPlanetClip-{{ $planet->id ?? 'def' }})">
            <path d="M14 26 Q 36 31 58 26" stroke="#DDD6FE" stroke-width="2.5" opacity="0.45" fill="none"/>
            <path d="M14 32 Q 36 38 58 32" stroke="#A855F7" stroke-width="3" opacity="0.5" fill="none"/>
            <path d="M14 39 Q 36 46 58 39" stroke="#4C1D95" stroke-width="3.5" opacity="0.6" fill="none"/>
            <path d="M16 46 Q 36 53 56 46" stroke="#F472B6" stroke-width="2" opacity="0.4" fill="none"/>

            <!-- Storm Eye -->
            <ellipse cx="44" cy="42" rx="4" ry="2.5" fill="#F43F5E" opacity="0.65"/>
            <ellipse cx="44" cy="42" rx="2.5" ry="1.5" fill="#FDA4AF" opacity="0.85"/>

            <!-- 3D Shadow -->
            <path d="M14 36 A 22 22 0 0 0 58 36 A 22 14 0 0 1 14 36 Z" fill="#2E1065" opacity="0.4" transform="rotate(20 36 36)"/>
        </g>

        <!-- Front Half of Dual Ring -->
        <path d="M8 33 C 8 46, 64 51, 64 38" stroke="url(#ipsRingGrad-{{ $planet->id ?? 'def' }})" stroke-width="4.5" stroke-linecap="round"/>
        <path d="M12 33 C 12 43, 60 47, 60 37" stroke="#FFFFFF" stroke-width="1.3" stroke-linecap="round" opacity="0.9"/>

        <!-- Amethyst Moon -->
        <circle cx="18" cy="52" r="3.2" fill="#F472B6"/>
        <circle cx="17.5" cy="51.5" r="1.3" fill="#FCE7F3"/>
    </svg>

@else
    {{-- ======================================================== --}}
    {{-- PLANET UNIVERSAL / OTHER SUBJECTS (Sun / Amber Planet)   --}}
    {{-- ======================================================== --}}
    <svg class="{{ $sizeClass }} drop-shadow-md shrink-0 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6 select-none" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <radialGradient id="defPlanetGrad-{{ $planet->id ?? 'def' }}" cx="35%" cy="30%" r="65%">
                <stop offset="0%" stop-color="#FDE68A"/>
                <stop offset="35%" stop-color="#F59E0B"/>
                <stop offset="75%" stop-color="#D97706"/>
                <stop offset="100%" stop-color="#78350F"/>
            </radialGradient>
            <clipPath id="defPlanetClip-{{ $planet->id ?? 'def' }}">
                <circle cx="36" cy="36" r="22"/>
            </clipPath>
        </defs>

        <circle cx="14" cy="20" r="1.5" fill="#FDE68A" opacity="0.8"/>
        <circle cx="60" cy="18" r="1.2" fill="#FEF3C7" opacity="0.75"/>
        <circle cx="36" cy="36" r="23" stroke="#FDE68A" stroke-width="1.5" opacity="0.4"/>
        <circle cx="36" cy="36" r="22" fill="url(#defPlanetGrad-{{ $planet->id ?? 'def' }})"/>

        <g clip-path="url(#defPlanetClip-{{ $planet->id ?? 'def' }})">
            <circle cx="28" cy="28" r="4" fill="#B45309" opacity="0.35"/>
            <circle cx="27" cy="27" r="3" fill="#78350F" opacity="0.25"/>
            <circle cx="44" cy="40" r="5" fill="#B45309" opacity="0.35"/>
            <circle cx="43" cy="39" r="3.5" fill="#78350F" opacity="0.25"/>
            <circle cx="26" cy="46" r="3" fill="#B45309" opacity="0.3"/>
            <path d="M14 36 A 22 22 0 0 0 58 36 A 22 14 0 0 1 14 36 Z" fill="#451A03" opacity="0.35" transform="rotate(30 36 36)"/>
        </g>

        <ellipse cx="36" cy="36" rx="28" ry="11" stroke="#FCD34D" stroke-width="0.8" stroke-dasharray="3 3" opacity="0.35" transform="rotate(25 36 36)"/>
        <circle cx="58" cy="46" r="3" fill="#FDE68A"/>
    </svg>
@endif

