<!DOCTYPE html>
<html>
<head>
    <title>Data Siswa</title>
</head>
<body>

    <h1>Data Siswa</h1>

    <a href="{{ route('siswa.create') }}">Tambah Siswa</a>

    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>NIS</th>
            <th>Jenis Kelamin</th>
            <th>Aksi</th>
        </tr>

        @foreach ($siswa as $data)
            <tr>
                <td>{{ $data->id }}</td>
                <td>{{ $data->nama_siswa }}</td>
                <td>{{ $data->nis }}</td>
                <td>{{ $data->jenis_kelamin }}</td>
                <td>
                    <a href="{{ route('siswa.show', $data->id) }}">detail</a>
                    <a href="{{ route('siswa.edit', $data->id) }}">edit</a>

                    <form action="{{ route('siswa.destroy', $data->id) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')

                        <button type="submit">hapus</button>

                    </form>
                </td>
            </tr>
        @endforeach

    </table>

</body>
</html>

