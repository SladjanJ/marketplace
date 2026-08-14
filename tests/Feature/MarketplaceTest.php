<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_is_logged_in(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('ads.index'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);
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
        $response->assertSessionHasErrors(['email', 'password', 'password_confirmation']);
        $this->assertGuest();
    }

    public function test_user_can_login_and_logout(): void
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

    public function test_authenticated_user_can_create_an_ad_with_images(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->post('/ads', [
            'title' => 'Vintage bike',
            'description' => 'Great condition',
            'price' => 120,
            'category' => 'sale',
            'location' => 'Belgrade',
            'contact_email' => 'seller@example.com',
            'contact_phone' => '0601234567',
            'images' => [
                UploadedFile::fake()->create('bike-1.jpg', 100, 'image/jpeg'),
            ],
        ]);

        $response->assertRedirect(route('ads.index'));
        $this->assertDatabaseHas('ads', [
            'title' => 'Vintage bike',
            'user_id' => $user->id,
            'status' => 'pending',
            'contact_info' => 'seller@example.com · 0601234567',
        ]);
        $this->assertDatabaseCount('ad_images', 1);
    }

    public function test_guest_is_redirected_from_create_ad_page(): void
    {
        $this->get(route('ads.create'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_create_ad_form(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('ads.create'))
            ->assertOk()
            ->assertSee('Create a new ad')
            ->assertSee('Submit for review');
    }

    public function test_home_page_shows_create_ad_call_to_action(): void
    {
        $this->get(route('ads.index'))
            ->assertOk()
            ->assertSee('Create a new ad')
            ->assertSee('New ad');
    }

    public function test_home_page_lists_only_approved_ads(): void
    {
        Ad::factory()->create(['title' => 'Approved bike', 'status' => 'approved']);
        Ad::factory()->create(['title' => 'Pending bike', 'status' => 'pending']);

        $this->get(route('ads.index'))
            ->assertOk()
            ->assertSee('Approved bike')
            ->assertDontSee('Pending bike');
    }

    public function test_user_can_request_password_reset_link(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'reset@example.com']);

        $response = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'reset@example.com',
        ]);

        $response->assertRedirect('/forgot-password');
        $response->assertSessionHas('success');
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'reset2@example.com',
            'password' => 'old-password',
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'reset2@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_login_page_has_forgot_password_link(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Forgot password?')
            ->assertSee(route('password.request'), false);
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

    public function test_first_visit_shows_language_popup(): void
    {
        $this->get(route('ads.index'))
            ->assertOk()
            ->assertSee('Choose your language / Izaberite jezik')
            ->assertSee('languageModal', false);
    }

    public function test_guest_can_switch_entire_ui_to_serbian(): void
    {
        $this->from('/')->post(route('locale.update'), ['locale' => 'sr']);

        $this->get(route('ads.index'))
            ->assertOk()
            ->assertSee('Najnoviji oglasi')
            ->assertSee('Novi oglas')
            ->assertSee('Prijava')
            ->assertDontSee('Latest ads')
            ->assertDontSee('languageModal', false);
    }

    public function test_closing_language_popup_keeps_english_and_hides_it(): void
    {
        $this->from('/')->post(route('locale.update'), ['locale' => 'en']);

        $this->get(route('ads.index'))
            ->assertOk()
            ->assertSee('Latest ads')
            ->assertSee('New ad')
            ->assertDontSee('languageModal', false);
    }

    public function test_create_ad_categories_follow_selected_language(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('ads.create'))
            ->assertOk()
            ->assertSee('Category')
            ->assertSee('Sale')
            ->assertSee('Services');

        $this->actingAs($user)
            ->from('/')
            ->post(route('locale.update'), ['locale' => 'sr']);

        $this->actingAs($user)
            ->get(route('ads.create'))
            ->assertOk()
            ->assertSee('Kategorija')
            ->assertSee('Prodaja')
            ->assertSee('Usluge')
            ->assertSee('Izaberi kategoriju');
    }

    public function test_guest_is_redirected_from_profile(): void
    {
        $this->get(route('profile.show'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_change_language_from_profile(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Settings')
            ->assertSee('Language');

        $this->actingAs($user)
            ->from(route('profile.show'))
            ->post(route('locale.update'), ['locale' => 'sr'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('sr', $user->fresh()->locale);

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Podešavanja')
            ->assertSee('Jezik');
    }
}
