<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthAndSingleSessionTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Operator Test',
            'username' => 'operator_test',
            'email' => 'operator@test.local',
            'password' => Hash::make('password123'),
            'role' => 'operator',
            'status' => 'aktif',
        ], $overrides));
    }

    public function test_login_page_does_not_show_google_sso_button(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertDontSee('Masuk dengan Google');
        $response->assertDontSee('google.login');
    }

    public function test_authenticated_user_visiting_root_or_login_is_redirected_to_dashboard(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect(route('dashboard'));

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_web_login_stores_current_session_id_and_invalidates_previous_session(): void
    {
        $user = $this->createUser();

        // Login pertama (Device A)
        $response = $this->post(route('login'), [
            'login' => 'operator_test',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        $user->refresh();
        $firstSessionId = $user->current_session_id;
        $this->assertNotNull($firstSessionId);

        $sessionCookieName = config('session.cookie');
        $sessionCookieValue = $response->getCookie($sessionCookieName)?->getValue();
        $this->assertNotNull($sessionCookieValue);

        // Request berikutnya dari Device A dengan sesi yang sama harus tetap valid
        $this->withCookie($sessionCookieName, $sessionCookieValue)
            ->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);

        // Simulasikan login di perangkat baru (Device B) yang mengganti current_session_id
        $user->forceFill([
            'current_session_id' => 'new-device-session-id-12345',
        ])->save();

        // Reset in-memory guard cache agar mensimulasikan request HTTP baru di PHP-FPM
        Auth::forgetGuards();

        // Request berikutnya dari Device A harus otomatis logout dan diarahkan ke /login
        $kickedResponse = $this->withCookie($sessionCookieName, $sessionCookieValue)
            ->get(route('dashboard'));
        $kickedResponse->assertRedirect(route('login'));
        $kickedResponse->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_invalidated_web_session_returns_401_for_json_requests(): void
    {
        $user = $this->createUser();

        $loginResponse = $this->post(route('login'), [
            'login' => 'operator_test',
            'password' => 'password123',
        ]);
        $loginResponse->assertRedirect('/dashboard');

        $sessionCookieName = config('session.cookie');
        $sessionCookieValue = $loginResponse->getCookie($sessionCookieName)?->getValue();

        $user->forceFill([
            'current_session_id' => 'another-device-session-id',
        ])->save();

        Auth::forgetGuards();

        $response = $this->withCookie($sessionCookieName, $sessionCookieValue)
            ->getJson(route('dashboard'));
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertGuest();
    }

    public function test_manual_logout_clears_current_session_id(): void
    {
        $user = $this->createUser();

        $loginResponse = $this->post(route('login'), [
            'login' => 'operator_test',
            'password' => 'password123',
        ]);
        $loginResponse->assertRedirect('/dashboard');

        $sessionCookieName = config('session.cookie');
        $sessionCookieValue = $loginResponse->getCookie($sessionCookieName)?->getValue();

        $user->refresh();
        $this->assertNotNull($user->current_session_id);

        $this->withCookie($sessionCookieName, $sessionCookieValue)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $user->refresh();
        $this->assertNull($user->current_session_id);
    }

    public function test_api_login_revokes_previous_tokens_and_returns_401_for_old_token(): void
    {
        $user = $this->createUser([
            'username' => 'guru_api',
            'email' => 'guru_api@test.local',
            'role' => 'guru',
        ]);

        // Login Mobile Device 1
        $firstLogin = $this->postJson('/api/login', [
            'login' => 'guru_api',
            'password' => 'password123',
            'device_name' => 'Android Phone 1',
        ]);

        $firstLogin->assertOk()
            ->assertJsonPath('data.previous_session_revoked', false)
            ->assertJsonPath('data.device_name', 'Android Phone 1');
        $firstToken = $firstLogin->json('data.token');
        $this->assertNotEmpty($firstToken);
        $this->assertEquals(1, $user->tokens()->count());

        // Cek sesi Device 1 via /api/auth/check masih valid
        $this->withHeader('Authorization', 'Bearer ' . $firstToken)
            ->getJson('/api/auth/check')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.authenticated', true)
            ->assertJsonPath('data.device_name', 'Android Phone 1');

        // Login Mobile Device 2
        $secondLogin = $this->postJson('/api/login', [
            'login' => 'guru_api',
            'password' => 'password123',
            'device_name' => 'Android Phone 2',
        ]);

        $secondLogin->assertOk()
            ->assertJsonPath('data.previous_session_revoked', true)
            ->assertJsonPath('data.device_name', 'Android Phone 2');
        $secondToken = $secondLogin->json('data.token');
        $this->assertNotEmpty($secondToken);
        $this->assertNotEquals($firstToken, $secondToken);

        // Hanya 1 token aktif tersisa di database
        $this->assertEquals(1, $user->tokens()->count());

        // Reset in-memory auth cache agar Sanctum benar-benar memverifikasi token di database
        Auth::forgetGuards();

        // Token Device 1 harus ditolak dengan 401 JSON terstandarisasi (bahkan tanpa Accept: application/json)
        $this->withHeader('Authorization', 'Bearer ' . $firstToken)
            ->get('/api/me')
            ->assertStatus(401)
            ->assertJson([
                'success' => false,
                'code' => 'SESSION_EXPIRED',
            ]);

        // Token Device 2 tetap valid
        Auth::forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $secondToken)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.authenticated', true)
            ->assertJsonPath('data.device_name', 'Android Phone 2');
    }

    public function test_web_session_and_mobile_api_token_remain_independent(): void
    {
        $user = $this->createUser([
            'username' => 'guru_dual',
            'email' => 'guru_dual@test.local',
            'role' => 'guru',
        ]);

        // Login Web
        $webLogin = $this->post(route('login'), [
            'login' => 'guru_dual',
            'password' => 'password123',
        ]);
        $webLogin->assertRedirect('/dashboard');
        $sessionCookieName = config('session.cookie');
        $sessionCookieValue = $webLogin->getCookie($sessionCookieName)?->getValue();

        $user->refresh();
        $webSessionId = $user->current_session_id;
        $this->assertNotNull($webSessionId);

        // Login Mobile API tidak menghapus sesi Web
        $apiLogin = $this->postJson('/api/login', [
            'login' => 'guru_dual',
            'password' => 'password123',
            'device_name' => 'Flutter Device',
        ]);
        $apiLogin->assertOk();
        $apiToken = $apiLogin->json('data.token');

        $user->refresh();
        $this->assertSame($webSessionId, $user->current_session_id);
        $this->assertEquals(1, $user->tokens()->count());

        // Sesi Web tetap valid
        Auth::forgetGuards();
        $this->withCookie($sessionCookieName, $sessionCookieValue)
            ->get(route('dashboard'))
            ->assertRedirect(route('guru.dashboard'));

        // Token Mobile tetap valid
        Auth::forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $apiToken)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.authenticated', true);
    }
}
