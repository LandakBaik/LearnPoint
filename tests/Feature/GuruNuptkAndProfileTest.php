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

    public function test_guru_can_update_nomor_telepon_and_jenis_kelamin_on_profile(): void
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
            'name'          => 'Budi Santoso, M.Pd.',
            'email'         => 'budi@guru.learnpoint.sch.id',
            'nomor_telepon' => '081234567890',
            'jenis_kelamin' => 'Laki-laki',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('gurus', [
            'id'            => $guru->id,
            'nama'          => 'Budi Santoso, M.Pd.',
            'nuptk'         => '9876543210987654',
            'nomor_telepon' => '081234567890',
            'jenis_kelamin' => 'Laki-laki',
        ]);

        $this->assertEquals('9876543210987654', $userGuru->fresh()->nuptk);
        $this->assertEquals('Laki-laki', $userGuru->fresh()->jenis_kelamin);
        $this->assertEquals('081234567890', $userGuru->fresh()->nomor_telepon);
        // Ensure no birth date extraction exists for guru
        $this->assertNull($userGuru->fresh()->tanggal_lahir);
    }

    public function test_non_guru_role_cannot_fill_guru_fields_on_profile(): void
    {
        $operator = User::factory()->create([
            'name'     => 'Admin Operator',
            'email'    => 'admin@learnpoint.sch.id',
            'role'     => 'operator',
            'status'   => 'aktif',
            'guru_id'  => null,
        ]);

        $response = $this->actingAs($operator)->put(route('profile.update'), [
            'name'          => 'Admin Operator Update',
            'email'         => 'admin@learnpoint.sch.id',
            'nomor_telepon' => '08999999999',
            'jenis_kelamin' => 'Laki-laki',
        ]);

        $response->assertRedirect(route('profile.show'));
        $this->assertEquals('Admin Operator Update', $operator->fresh()->name);
        $this->assertNull($operator->fresh()->nomor_telepon);
    }
}
