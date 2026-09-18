@extends('layouts.app')

@section('title', 'Detail Siswa - LearnPoint')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">
            <span>👤</span> Detail Data Siswa
        </h1>
        <p class="page-subtitle">Rincian informasi lengkap siswa <strong>{{ $siswa->nama_siswa }}</strong>.</p>
    </div>
    <div class="btn-group">
        <a href="{{ route('siswa.edit', $siswa->id) }}" class="btn btn-warning">
            ✏️ Edit Data
        </a>
        <a href="{{ route('siswa.index') }}" class="btn btn-secondary">
            ← Kembali
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Informasi Siswa (#{{ $siswa->id }})</h2>
        <div>
            @if($siswa->jenis_kelamin == 'L')
                <span class="badge badge-l">Laki-laki</span>
            @else
                <span class="badge badge-p">Perempuan</span>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">ID Siswa</div>
                <div class="detail-value">#{{ $siswa->id }}</div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Nama Lengkap</div>
                <div class="detail-value">{{ $siswa->nama_siswa }}</div>
            </div>

            <div class="detail-item">
                <div class="detail-label">NIS (Nomor Induk Siswa)</div>
                <div class="detail-value">{{ $siswa->nis }}</div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Jenis Kelamin</div>
                <div class="detail-value">
                    {{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Tanggal Lahir</div>
                <div class="detail-value">
                    {{ \Carbon\Carbon::parse($siswa->tanggal_lahir)->translatedFormat('d F Y') ?? $siswa->tanggal_lahir }}
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Nama Wali Murid</div>
                <div class="detail-value">{{ $siswa->wali_murid ?? '-' }}</div>
            </div>

            <div class="detail-item">
                <div class="detail-label">No. HP Wali</div>
                <div class="detail-value">
                    @if($siswa->nohp_wali)
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siswa->nohp_wali) }}" target="_blank" style="color: var(--primary); text-decoration: none;">
                            📞 {{ $siswa->nohp_wali }}
                        </a>
                    @else
                        -
                    @endif
                </div>
            </div>

            <div class="detail-item" style="grid-column: 1 / -1;">
                <div class="detail-label">Alamat Lengkap</div>
                <div class="detail-value" style="white-space: pre-line;">{{ $siswa->alamat }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
