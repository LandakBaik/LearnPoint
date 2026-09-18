<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Tambah Siswa</title>
</head>
<body>
    <h1>tambah siswa</h1>

    <form action="{{ route('siswa.store') }}" method="POST">
        @csrf

        <label>Nama Siswa</label><br>
        <input type="text" name="nama_siswa"><br><br>

        <label>NIS</label><br>
        <input type="text" name="nis"><br><br>

        <label>Alamat</label><br>
        <textarea name="alamat"></textarea><br><br>

        <label>Tanggal Lahir</label><br>
        <input type="date" name="tanggal_lahir"><br><br>

        <label>Jenis Kelamin</label><br>
        <select name="jenis_kelamin">
            <option value="L">Laki-laki</option>
            <option value="P">Perempuan</option>
        </select><br><br>

        <label>Wali Murid</label><br>
        <input type="text" name="wali_murid"><br><br>

        <label>No HP Wali</label><br>
        <input type="text" name="nohp_wali"><br><br>

        <button type="submit">Simpan</button>
    </form>
        @if ($errors->any())
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

</body>
</html>
