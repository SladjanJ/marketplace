<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_requires_email_verification(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticated();

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registration_validation_errors_are_shown(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    public function test_user_can_verify_email_and_land_on_home(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect(route('ads.index'));
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_verified_user_can_login_and_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $login = $this->post('/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $login->assertRedirect(route('ads.index'));
        $this->assertAuthenticatedAs($user);

        $logout = $this->post('/logout');
        $logout->assertRedirect(route('ads.index'));
        $this->assertGuest();
    }

    public function test_unverified_user_is_sent_to_verification_notice_on_login(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'pending@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'email' => 'pending@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_unverified_user_cannot_create_an_ad(): void
    {
        Storage::fake('public');

        $user = User::factory()->unverified()->create(['role' => 'user']);

        $response = $this->actingAs($user)->post('/ads', [
            'title' => 'Vintage bike',
            'description' => 'Great condition',
            'price' => 120,
            'category' => 'Prodaja',
            'location' => 'Belgrade',
            'contact_info' => 'Call me',
            'images' => [
                UploadedFile::fake()->create('bike-1.jpg', 100, 'image/jpeg'),
            ],
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertDatabaseCount('ads', 0);
    }

    public function test_authenticated_user_can_create_an_ad_with_images(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->post('/ads', [
            'title' => 'Vintage bike',
            'description' => 'Great condition',
            'price' => 120,
            'category' => 'Prodaja',
            'location' => 'Belgrade',
            'contact_info' => 'Call me',
            'images' => [
                UploadedFile::fake()->create('bike-1.jpg', 100, 'image/jpeg'),
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ads', ['title' => 'Vintage bike', 'user_id' => $user->id]);
        $this->assertDatabaseCount('ad_images', 1);
    }

    public function test_admin_can_approve_a_pending_ad(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $ad = Ad::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

        $response = $this->actingAs($admin)->post('/admin/ads/' . $ad->id . '/approve');

        $response->assertRedirect();
        $this->assertDatabaseHas('ads', ['id' => $ad->id, 'status' => 'approved']);
    }
}
