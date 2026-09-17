<!DOCTYPE html>
<html>
<head>
    <title>Detail Siswa</title>
</head>
<body>

    <h1>Detail Siswa</h1>

    <p>ID: {{ $siswa->id }}</p>
    <p>Nama: {{ $siswa->nama_siswa }}</p>
    <p>NIS: {{ $siswa->nis }}</p>
    <p>Alamat: {{ $siswa->alamat }}</p>
    <p>Tanggal Lahir: {{ $siswa->tanggal_lahir }}</p>
    <p>Jenis Kelamin: {{ $siswa->jenis_kelamin }}</p>
    <p>Wali Murid: {{ $siswa->wali_murid }}</p>
    <p>No HP Wali: {{ $siswa->nohp_wali }}</p>

    <a href="{{ route('siswa.index') }}">Kembali</a>

</body>
</html>
