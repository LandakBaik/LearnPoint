<!DOCTYPE html>
<html>
<head>
    <title>Edit Siswa</title>
</head>
<body>

    <h1>Edit Siswa</h1>

    <form action="{{ route('siswa.update', $siswa->id) }}" method="POST">
        @csrf
        @method('PUT')

        <label>Nama Siswa</label><br>
        <input type="text" name="nama_siswa" value="{{ $siswa->nama_siswa }}"><br><br>

        <label>NIS</label><br>
        <input type="text" name="nis" value="{{ $siswa->nis }}"><br><br>

        <label>Alamat</label><br>
        <textarea name="alamat">{{ $siswa->alamat }}</textarea><br><br>

        <label>Tanggal Lahir</label><br>
        <input type="date" name="tanggal_lahir" value="{{ $siswa->tanggal_lahir }}"><br><br>

        <label>Jenis Kelamin</label><br>
        <select name="jenis_kelamin">
            <option value="L" {{ $siswa->jenis_kelamin == 'L' ? 'selected' : '' }}>
                Laki-laki
            </option>
            <option value="P" {{ $siswa->jenis_kelamin == 'P' ? 'selected' : '' }}>
                Perempuan
            </option>
        </select><br><br>

        <label>Wali Murid</label><br>
        <input type="text" name="wali_murid" value="{{ $siswa->wali_murid }}"><br><br>

        <label>No HP Wali</label><br>
        <input type="text" name="nohp_wali" value="{{ $siswa->nohp_wali }}"><br><br>

        <button type="submit">Update</button>
    </form>

</body>
</html>
