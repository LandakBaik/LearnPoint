@extends('layouts.app')

@section('title', 'Data Siswa - LearnPoint')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">
            <span>📋</span> Data Siswa
        </h1>
        <p class="page-subtitle">Kelola daftar seluruh siswa yang terdaftar di sistem LearnPoint.</p>
    </div>
    <div>
        <a href="{{ route('siswa.create') }}" class="btn btn-primary">
            <span>+</span> Tambah Siswa
        </a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Nama Siswa</th>
                    <th>NIS</th>
                    <th>Jenis Kelamin</th>
                    <th>Wali Murid</th>
                    <th>No. HP Wali</th>
                    <th style="width: 200px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($siswa as $data)
                    <tr>
                        <td><strong>#{{ $data->id }}</strong></td>
                        <td>
                            <strong style="color: var(--primary);">{{ $data->nama_siswa }}</strong>
                        </td>
                        <td>{{ $data->nis }}</td>
                        <td>
                            @if($data->jenis_kelamin == 'L')
                                <span class="badge badge-l">Laki-laki</span>
                            @else
                                <span class="badge badge-p">Perempuan</span>
                            @endif
                        </td>
                        <td>{{ $data->wali_murid ?? '-' }}</td>
                        <td>{{ $data->nohp_wali ?? '-' }}</td>
                        <td style="text-align: center;">
                            <div class="btn-group">
                                <a href="{{ route('siswa.show', $data->id) }}" class="btn btn-sm btn-secondary" title="Detail">
                                    👁️ Detail
                                </a>
                                <a href="{{ route('siswa.edit', $data->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                    ✏️ Edit
                                </a>
                                <form action="{{ route('siswa.destroy', $data->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data siswa ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                        🗑️ Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="empty-state-icon">📂</div>
                                <h3>Belum Ada Data Siswa</h3>
                                <p style="margin-top: 0.5rem; margin-bottom: 1rem;">Silakan tambahkan data siswa baru melalui tombol di bawah.</p>
                                <a href="{{ route('siswa.create') }}" class="btn btn-primary btn-sm">
                                    + Tambah Siswa Sekarang
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
