@extends('layouts.app')

@section('title', 'Tambah Siswa - LearnPoint')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">
            <span>➕</span> Tambah Siswa Baru
        </h1>
        <p class="page-subtitle">Isi formulir di bawah ini untuk menambahkan data siswa ke dalam sistem.</p>
    </div>
    <div>
        <a href="{{ route('siswa.index') }}" class="btn btn-secondary">
            ← Kembali
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Formulir Data Siswa</h2>
    </div>
    <div class="card-body">
        <form action="{{ route('siswa.store') }}" method="POST">
            @csrf

            <div class="form-grid form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="nama_siswa">Nama Lengkap Siswa <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="nama_siswa" name="nama_siswa" class="form-control" value="{{ old('nama_siswa') }}" placeholder="Masukkan nama siswa..." required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="nis">NIS (Nomor Induk Siswa) <span style="color:var(--danger)">*</span></label>
                    <input type="number" id="nis" name="nis" class="form-control" value="{{ old('nis') }}" placeholder="Masukkan NIS..." required>
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="alamat">Alamat Lengkap <span style="color:var(--danger)">*</span></label>
                    <textarea id="alamat" name="alamat" class="form-control" placeholder="Masukkan alamat lengkap siswa..." required>{{ old('alamat') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="tanggal_lahir">Tanggal Lahir <span style="color:var(--danger)">*</span></label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="jenis_kelamin">Jenis Kelamin <span style="color:var(--danger)">*</span></label>
                    <select id="jenis_kelamin" name="jenis_kelamin" class="form-select" required>
                        <option value="" disabled {{ old('jenis_kelamin') ? '' : 'selected' }}>-- Pilih Jenis Kelamin --</option>
                        <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="wali_murid">Nama Wali Murid <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="wali_murid" name="wali_murid" class="form-control" value="{{ old('wali_murid') }}" placeholder="Masukkan nama orang tua / wali..." required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="nohp_wali">No. HP Wali <span style="color:var(--danger)">*</span></label>
                    <input type="number" id="nohp_wali" name="nohp_wali" class="form-control" value="{{ old('nohp_wali') }}" placeholder="Contoh: 081234567890" required>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('siswa.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    💾 Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
