<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruNuptkAndProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_guru_with_16_digit_nuptk(): void
    {
        $admin = User::factory()->create([
            'role'   => 'operator',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($admin)->post(route('guru.store'), [
            'nama'  => 'Siti Aminah, M.Pd.',
            'nuptk' => '1234567890123456',
        ]);

        $response->assertRedirect(route('guru.index'));

        $this->assertDatabaseHas('gurus', [
            'nama'          => 'Siti Aminah, M.Pd.',
            'nuptk'         => '1234567890123456',
            'nomor_telepon' => null,
            'jenis_kelamin' => null,
        ]);

        $this->assertDatabaseHas('users', [
            'username' => '1234567890123456',
            'role'     => 'guru',
        ]);
    }

    public function test_guru_nuptk_must_be_16_digits(): void
    {
        $admin = User::factory()->create([
            'role'   => 'operator',
            'status' => 'aktif',
        ]);

        // 15 digits
        $response = $this->actingAs($admin)->post(route('guru.store'), [
            'nama'  => 'Guru Salah',
            'nuptk' => '123456789012345',
        ]);
        $response->assertSessionHasErrors(['nuptk']);

        // 18 digits (old NIP length)
        $response = $this->actingAs($admin)->post(route('guru.store'), [
            'nama'  => 'Guru Salah',
            'nuptk' => '198501012010011005',
        ]);
        $response->assertSessionHasErrors(['nuptk']);
    }

    public function test_guru_can_update_nomor_telepon_and_jenis_kelamin_on_profile_but_not_name_or_email(): void
    {
        $guru = Guru::create([
            'nama'          => 'Budi Santoso, S.Pd.',
            'nuptk'         => '9876543210987654',
            'status'        => 'aktif',
            'nomor_telepon' => null,
            'jenis_kelamin' => null,
        ]);

        $userGuru = User::factory()->create([
            'name'     => 'Budi Santoso, S.Pd.',
            'email'    => 'budi@guru.learnpoint.sch.id',
            'username' => '9876543210987654',
            'role'     => 'guru',
            'status'   => 'aktif',
            'guru_id'  => $guru->id,
        ]);

        $response = $this->actingAs($userGuru)->put(route('profile.update'), [
            'name'          => 'Nama Baru Mencoba Ubah',
            'email'         => 'emailbaru@guru.learnpoint.sch.id',
            'nomor_telepon' => '081234567890',
            'jenis_kelamin' => 'Laki-laki',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success');

        // Nama dan email tidak boleh berubah
        $this->assertEquals('Budi Santoso, S.Pd.', $userGuru->fresh()->name);
        $this->assertEquals('budi@guru.learnpoint.sch.id', $userGuru->fresh()->email);
        $this->assertEquals('Budi Santoso, S.Pd.', $guru->fresh()->nama);

        // Nomor telepon dan jenis kelamin berhasil diperbarui
        $this->assertEquals('9876543210987654', $userGuru->fresh()->nuptk);
        $this->assertEquals('Laki-laki', $userGuru->fresh()->jenis_kelamin);
        $this->assertEquals('081234567890', $userGuru->fresh()->nomor_telepon);
        $this->assertNull($userGuru->fresh()->tanggal_lahir);
    }

    public function test_admin_cannot_update_name_or_email_only_password(): void
    {
        $operator = User::factory()->create([
            'name'     => 'Admin Operator',
            'email'    => 'admin@learnpoint.sch.id',
            'role'     => 'operator',
            'status'   => 'aktif',
            'guru_id'  => null,
            'password' => bcrypt('passwordlama123'),
        ]);

        $response = $this->actingAs($operator)->put(route('profile.update'), [
            'name'                  => 'Admin Operator Diubah',
            'email'                 => 'adminbaru@learnpoint.sch.id',
            'password'              => 'passwordbaru123',
            'password_confirmation' => 'passwordbaru123',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success');

        // Nama dan email tetap sama (tidak berubah)
        $this->assertEquals('Admin Operator', $operator->fresh()->name);
        $this->assertEquals('admin@learnpoint.sch.id', $operator->fresh()->email);

        // Password berhasil diubah
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('passwordbaru123', $operator->fresh()->password));
    }

    public function test_siswa_cannot_update_name_or_email_only_password(): void
    {
        $siswaUser = User::factory()->create([
            'name'     => 'Siswa Belajar',
            'email'    => 'siswa@learnpoint.sch.id',
            'role'     => 'siswa',
            'status'   => 'aktif',
            'password' => bcrypt('passwordlama123'),
        ]);

        $response = $this->actingAs($siswaUser)->put(route('profile.update'), [
            'name'                  => 'Nama Siswa Palsu',
            'email'                 => 'siswabaru@learnpoint.sch.id',
            'password'              => 'passwordbaru123',
            'password_confirmation' => 'passwordbaru123',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success');

        // Nama dan email tetap sama (tidak berubah)
        $this->assertEquals('Siswa Belajar', $siswaUser->fresh()->name);
        $this->assertEquals('siswa@learnpoint.sch.id', $siswaUser->fresh()->email);

        // Password berhasil diubah
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('passwordbaru123', $siswaUser->fresh()->password));
    }
}
