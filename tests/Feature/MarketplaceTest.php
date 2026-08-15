<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
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

        $ad = $user->ads()->first();
        $response->assertRedirect(route('ads.show', $ad));
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

    public function test_user_cannot_create_more_than_two_ads_per_day(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'user']);
        $this->makeAd(['user_id' => $user->id]);
        $this->makeAd(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('ads.create'))
            ->assertOk()
            ->assertSee('You can post up to 2 ads per day')
            ->assertDontSee('Submit for review');

        $this->actingAs($user)
            ->from(route('ads.create'))
            ->post('/ads', $this->newAdPayload(['title' => 'Third bike']))
            ->assertRedirect(route('ads.create'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('ads', 2);
        $this->assertDatabaseMissing('ads', ['title' => 'Third bike']);
    }

    public function test_ads_from_previous_days_do_not_count_toward_daily_limit(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'user']);

        Carbon::setTestNow(now()->subDay());
        $this->makeAd(['user_id' => $user->id]);
        $this->makeAd(['user_id' => $user->id]);
        Carbon::setTestNow();

        $this->actingAs($user)
            ->get(route('ads.create'))
            ->assertOk()
            ->assertSee('Submit for review');

        $this->actingAs($user)
            ->post('/ads', $this->newAdPayload(['title' => 'Today bike']))
            ->assertRedirect();

        $this->assertDatabaseHas('ads', [
            'user_id' => $user->id,
            'title' => 'Today bike',
        ]);
        $this->assertSame(3, $user->ads()->count());
    }

    public function test_updating_an_ad_does_not_count_toward_daily_limit(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $this->makeAd(['user_id' => $owner->id]);
        $ad = $this->makeAd([
            'user_id' => $owner->id,
            'status' => 'approved',
            'title' => 'Old title',
        ]);

        $this->actingAs($owner)->put(route('ads.update', $ad), [
            'title' => 'Updated title',
            'description' => $ad->description,
            'price' => $ad->price,
            'category' => $ad->category,
            'location' => $ad->location,
            'contact_email' => $ad->contactEmail(),
            'contact_phone' => $ad->contactPhone(),
        ])->assertRedirect(route('ads.show', $ad));

        $this->assertDatabaseHas('ads', [
            'id' => $ad->id,
            'title' => 'Updated title',
        ]);
        $this->assertTrue($owner->fresh()->hasReachedDailyAdLimit());
        $this->assertSame(2, $owner->ads()->count());
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

    public function test_home_page_search_filters_approved_ads_by_title_and_description(): void
    {
        Ad::factory()->create(['title' => 'Vintage bike', 'description' => 'Great condition', 'status' => 'approved']);
        Ad::factory()->create(['title' => 'Office chair', 'description' => 'Almost new', 'status' => 'approved']);
        Ad::factory()->create(['title' => 'Hidden bike', 'description' => 'Pending review', 'status' => 'pending']);

        $this->get(route('ads.index', ['q' => 'bike']))
            ->assertOk()
            ->assertSee('Vintage bike')
            ->assertDontSee('Office chair')
            ->assertDontSee('Hidden bike');

        $this->get(route('ads.index', ['q' => 'Almost']))
            ->assertOk()
            ->assertSee('Office chair')
            ->assertDontSee('Vintage bike');
    }

    public function test_home_page_filters_by_category_location_and_price(): void
    {
        Ad::factory()->create([
            'title' => 'Belgrade bike',
            'category' => 'sale',
            'location' => 'Belgrade',
            'price' => 120,
            'status' => 'approved',
        ]);
        Ad::factory()->create([
            'title' => 'Novi Sad lesson',
            'category' => 'services',
            'location' => 'Novi Sad',
            'price' => 40,
            'status' => 'approved',
        ]);
        Ad::factory()->create([
            'title' => 'Expensive bike',
            'category' => 'sale',
            'location' => 'Belgrade',
            'price' => 900,
            'status' => 'approved',
        ]);

        $this->get(route('ads.index', ['category' => 'sale']))
            ->assertOk()
            ->assertSee('Belgrade bike')
            ->assertSee('Expensive bike')
            ->assertDontSee('Novi Sad lesson');

        $this->get(route('ads.index', ['location' => 'Novi']))
            ->assertOk()
            ->assertSee('Novi Sad lesson')
            ->assertDontSee('Belgrade bike');

        $this->get(route('ads.index', ['min_price' => 100, 'max_price' => 200]))
            ->assertOk()
            ->assertSee('Belgrade bike')
            ->assertDontSee('Novi Sad lesson')
            ->assertDontSee('Expensive bike');
    }

    public function test_home_page_sorts_approved_ads_by_price(): void
    {
        Ad::factory()->create(['title' => 'Cheap bike', 'price' => 50, 'status' => 'approved']);
        Ad::factory()->create(['title' => 'Mid bike', 'price' => 150, 'status' => 'approved']);
        Ad::factory()->create(['title' => 'Pricey bike', 'price' => 400, 'status' => 'approved']);

        $this->get(route('ads.index', ['sort' => 'price_asc']))
            ->assertOk()
            ->assertSeeInOrder(['Cheap bike', 'Mid bike', 'Pricey bike']);

        $this->get(route('ads.index', ['sort' => 'price_desc']))
            ->assertOk()
            ->assertSeeInOrder(['Pricey bike', 'Mid bike', 'Cheap bike']);
    }

    public function test_home_page_shows_empty_filter_state(): void
    {
        Ad::factory()->create(['title' => 'Vintage bike', 'status' => 'approved']);

        $this->get(route('ads.index', ['q' => 'no-such-ad']))
            ->assertOk()
            ->assertSee('No ads match these filters')
            ->assertSee('Clear filters')
            ->assertDontSee('Vintage bike');
    }

    public function test_location_suggestions_match_city_prefix(): void
    {
        $this->assertContains('Podgorica', \App\Support\Cities::suggest('Pod'));
        $this->assertContains('Podujevo', \App\Support\Cities::suggest('pod'));
        $this->assertSame([], \App\Support\Cities::suggest('Po'));
    }

    public function test_home_page_includes_location_catalog_for_autocomplete(): void
    {
        $this->get(route('ads.index'))
            ->assertOk()
            ->assertSee('location-cities', false)
            ->assertSee('Podgorica', false)
            ->assertSee('location-suggestions', false);
    }

    public function test_location_prefix_filters_ads_and_partial_skips_layout(): void
    {
        Ad::factory()->create(['title' => 'Podgorica bike', 'location' => 'Podgorica', 'status' => 'approved']);
        Ad::factory()->create(['title' => 'Belgrade bike', 'location' => 'Beograd', 'status' => 'approved']);

        $this->get(route('ads.index', ['location' => 'Pod']))
            ->assertOk()
            ->assertSee('Podgorica bike')
            ->assertDontSee('Belgrade bike')
            ->assertSee('Marketplace');

        $this->get(route('ads.index', ['location' => 'Pod', 'partial' => 1]))
            ->assertOk()
            ->assertSee('Podgorica bike')
            ->assertDontSee('Belgrade bike')
            ->assertDontSee('Marketplace')
            ->assertDontSee('Latest ads');
    }

    public function test_invalid_listing_filters_are_rejected(): void
    {
        $this->from(route('ads.index'))
            ->get(route('ads.index', ['category' => 'cars', 'sort' => 'popular']))
            ->assertRedirect(route('ads.index'))
            ->assertSessionHasErrors(['category', 'sort']);
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

    public function test_password_fields_have_show_hide_toggle(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Show password')
            ->assertSee('password-toggle', false)
            ->assertSee('bi-eye', false);

        $register = $this->get(route('register'))
            ->assertOk()
            ->assertSee('Show password')
            ->getContent();

        $this->assertSame(1, substr_count($register, 'data-target="password"'));
        $this->assertSame(1, substr_count($register, 'data-target="password_confirmation"'));
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

    public function test_approved_ad_leaves_pending_queue_and_appears_in_reviewed_table(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ad = Ad::factory()->create(['title' => 'Bike to review', 'status' => 'pending']);

        $this->actingAs($admin)
            ->post('/admin/ads/'.$ad->id.'/approve')
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('No ads are waiting for review.')
            ->assertSee('Reviewed ads')
            ->assertSee('Bike to review');
    }

    public function test_admin_navbar_has_a_single_admin_link(): void
    {
        $admin = User::factory()->create(['name' => 'Admin', 'role' => 'admin']);

        $html = $this->actingAs($admin)->get(route('admin.dashboard'))->getContent();

        $this->assertSame(1, substr_count($html, '>'.e(__('ui.admin')).'</a>'));
        $this->assertStringContainsString(__('ui.profile'), $html);
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

    public function test_owner_sees_pending_ad_on_home_and_profile(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $ad = $this->makeAd([
            'user_id' => $user->id,
            'title' => 'My pending bike',
            'status' => 'pending',
        ]);
        $this->makeAd([
            'title' => 'Someone else pending',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('ads.index'))
            ->assertOk()
            ->assertSee('Your ads')
            ->assertSee('My pending bike')
            ->assertDontSee('Someone else pending');

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('My pending bike')
            ->assertSee('Pending')
            ->assertSee(route('ads.show', $ad), false);
    }

    public function test_guest_can_view_approved_ad_but_not_contact_details(): void
    {
        $ad = $this->makeAd([
            'title' => 'Approved bike',
            'description' => 'Great condition',
            'status' => 'approved',
            'contact_info' => 'seller@example.com · 0601234567',
        ]);

        $this->get(route('ads.show', $ad))
            ->assertOk()
            ->assertSee('Approved bike')
            ->assertSee('Great condition')
            ->assertSee('Log in to see the seller')
            ->assertDontSee('seller@example.com')
            ->assertDontSee('0601234567');
    }

    public function test_authenticated_user_can_see_contact_details_on_approved_ad(): void
    {
        $viewer = User::factory()->create(['role' => 'user']);
        $ad = $this->makeAd([
            'status' => 'approved',
            'contact_info' => 'seller@example.com · 0601234567',
        ]);

        $this->actingAs($viewer)
            ->get(route('ads.show', $ad))
            ->assertOk()
            ->assertSee('seller@example.com')
            ->assertSee('0601234567');
    }

    public function test_guest_cannot_view_pending_ad(): void
    {
        $ad = $this->makeAd(['status' => 'pending', 'title' => 'Hidden pending bike']);

        $this->get(route('ads.show', $ad))->assertForbidden();
    }

    public function test_owner_can_view_pending_ad(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $ad = $this->makeAd(['user_id' => $owner->id, 'status' => 'pending', 'title' => 'My pending bike']);

        $this->actingAs($owner)
            ->get(route('ads.show', $ad))
            ->assertOk()
            ->assertSee('My pending bike')
            ->assertSee('Pending')
            ->assertSee('Edit');
    }

    public function test_admin_can_view_pending_ad_from_dashboard_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ad = $this->makeAd(['status' => 'pending', 'title' => 'Needs review']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('ads.show', $ad), false);

        $this->actingAs($admin)
            ->get(route('ads.show', $ad))
            ->assertOk()
            ->assertSee('Needs review');
    }

    public function test_home_page_links_to_ad_details(): void
    {
        $ad = $this->makeAd(['title' => 'Clickable bike', 'status' => 'approved']);

        $this->get(route('ads.index'))
            ->assertOk()
            ->assertSee(route('ads.show', $ad), false);
    }

    public function test_guest_is_redirected_from_edit_ad_page(): void
    {
        $ad = $this->makeAd(['status' => 'approved']);

        $this->get(route('ads.edit', $ad))->assertRedirect(route('login'));
    }

    public function test_owner_can_update_an_ad(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $ad = $this->makeAd([
            'user_id' => $owner->id,
            'status' => 'approved',
            'title' => 'Old title',
            'contact_info' => 'old@example.com · 0601111111',
        ]);

        $response = $this->actingAs($owner)->put(route('ads.update', $ad), [
            'title' => 'New title',
            'description' => $ad->description,
            'price' => 250,
            'category' => 'services',
            'location' => 'Novi Sad',
            'contact_email' => 'new@example.com',
            'contact_phone' => '0609999999',
        ]);

        $response->assertRedirect(route('ads.show', $ad));
        $this->assertDatabaseHas('ads', [
            'id' => $ad->id,
            'title' => 'New title',
            'price' => 250,
            'category' => 'services',
            'location' => 'Novi Sad',
            'contact_info' => 'new@example.com · 0609999999',
            'status' => 'approved',
        ]);
    }

    public function test_rejected_ad_returns_to_pending_after_owner_update(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $ad = $this->makeAd([
            'user_id' => $owner->id,
            'status' => 'rejected',
            'title' => 'Needs changes',
        ]);

        $this->actingAs($owner)->put(route('ads.update', $ad), [
            'title' => 'Fixed title',
            'description' => $ad->description,
            'price' => $ad->price,
            'category' => $ad->category,
            'location' => $ad->location,
            'contact_email' => $ad->contactEmail(),
            'contact_phone' => $ad->contactPhone(),
        ])->assertRedirect(route('ads.show', $ad));

        $this->assertDatabaseHas('ads', [
            'id' => $ad->id,
            'title' => 'Fixed title',
            'status' => 'pending',
        ]);
    }

    public function test_other_user_cannot_update_or_delete_an_ad(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $other = User::factory()->create(['role' => 'user']);
        $ad = $this->makeAd(['user_id' => $owner->id, 'status' => 'approved']);

        $this->actingAs($other)->get(route('ads.edit', $ad))->assertForbidden();
        $this->actingAs($other)->put(route('ads.update', $ad), [
            'title' => 'Hacked title',
            'description' => $ad->description,
            'price' => $ad->price,
            'category' => $ad->category,
            'location' => $ad->location,
            'contact_email' => 'hack@example.com',
            'contact_phone' => '0600000000',
        ])->assertForbidden();
        $this->actingAs($other)->delete(route('ads.destroy', $ad))->assertForbidden();

        $this->assertDatabaseHas('ads', ['id' => $ad->id, 'title' => $ad->title]);
    }

    public function test_owner_can_delete_an_ad(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create(['role' => 'user']);
        $ad = $this->makeAd(['user_id' => $owner->id, 'status' => 'approved']);
        Storage::disk('public')->put('ads/test.jpg', 'fake');

        $this->actingAs($owner)
            ->delete(route('ads.destroy', $ad))
            ->assertRedirect(route('ads.index'));

        $this->assertDatabaseMissing('ads', ['id' => $ad->id]);
        $this->assertDatabaseCount('ad_images', 0);
        Storage::disk('public')->assertMissing('ads/test.jpg');
    }

    public function test_owner_can_pause_an_approved_ad(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $ad = $this->makeAd([
            'user_id' => $owner->id,
            'title' => 'Bike to pause',
            'status' => 'approved',
        ]);

        $this->actingAs($owner)
            ->patch(route('ads.status', $ad), ['status' => 'paused'])
            ->assertRedirect(route('ads.show', $ad));

        $this->assertDatabaseHas('ads', ['id' => $ad->id, 'status' => 'paused']);

        $this->beGuest()
            ->get(route('ads.index'))
            ->assertOk()
            ->assertDontSee('Bike to pause');

        $this->actingAs($owner)
            ->get(route('ads.index'))
            ->assertOk()
            ->assertSee('Bike to pause');
    }

    public function test_guest_cannot_view_paused_or_sold_ad(): void
    {
        $paused = $this->makeAd(['status' => 'paused', 'title' => 'Hidden paused bike']);
        $sold = $this->makeAd(['status' => 'sold', 'title' => 'Hidden sold bike']);

        $this->get(route('ads.show', $paused))->assertForbidden();
        $this->get(route('ads.show', $sold))->assertForbidden();
    }

    public function test_owner_can_resume_a_paused_ad_without_admin_review(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $ad = $this->makeAd([
            'user_id' => $owner->id,
            'title' => 'Bike back on sale',
            'status' => 'paused',
        ]);

        $this->actingAs($owner)
            ->patch(route('ads.status', $ad), ['status' => 'approved'])
            ->assertRedirect(route('ads.show', $ad));

        $this->assertDatabaseHas('ads', ['id' => $ad->id, 'status' => 'approved']);

        $this->get(route('ads.index'))
            ->assertOk()
            ->assertSee('Bike back on sale');
    }

    public function test_owner_can_mark_approved_and_paused_ads_as_sold(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $approved = $this->makeAd([
            'user_id' => $owner->id,
            'title' => 'Sold from live',
            'status' => 'approved',
        ]);
        $paused = $this->makeAd([
            'user_id' => $owner->id,
            'title' => 'Sold from paused',
            'status' => 'paused',
        ]);

        $this->actingAs($owner)
            ->patch(route('ads.status', $approved), ['status' => 'sold'])
            ->assertRedirect(route('ads.show', $approved));
        $this->actingAs($owner)
            ->patch(route('ads.status', $paused), ['status' => 'sold'])
            ->assertRedirect(route('ads.show', $paused));

        $this->assertDatabaseHas('ads', ['id' => $approved->id, 'status' => 'sold']);
        $this->assertDatabaseHas('ads', ['id' => $paused->id, 'status' => 'sold']);

        $this->beGuest()
            ->get(route('ads.index'))
            ->assertOk()
            ->assertDontSee('Sold from live')
            ->assertDontSee('Sold from paused');
    }

    public function test_owner_cannot_change_sold_or_pending_ad_status(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $sold = $this->makeAd(['user_id' => $owner->id, 'status' => 'sold']);
        $pending = $this->makeAd(['user_id' => $owner->id, 'status' => 'pending']);
        $rejected = $this->makeAd(['user_id' => $owner->id, 'status' => 'rejected']);

        $this->actingAs($owner)->patch(route('ads.status', $sold), ['status' => 'approved'])->assertForbidden();
        $this->actingAs($owner)->patch(route('ads.status', $pending), ['status' => 'paused'])->assertForbidden();
        $this->actingAs($owner)->patch(route('ads.status', $rejected), ['status' => 'sold'])->assertForbidden();

        $this->assertDatabaseHas('ads', ['id' => $sold->id, 'status' => 'sold']);
        $this->assertDatabaseHas('ads', ['id' => $pending->id, 'status' => 'pending']);
        $this->assertDatabaseHas('ads', ['id' => $rejected->id, 'status' => 'rejected']);
    }

    public function test_other_user_and_admin_cannot_change_owner_status(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $other = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $ad = $this->makeAd(['user_id' => $owner->id, 'status' => 'approved']);

        $this->actingAs($other)->patch(route('ads.status', $ad), ['status' => 'paused'])->assertForbidden();
        $this->actingAs($admin)->patch(route('ads.status', $ad), ['status' => 'sold'])->assertForbidden();

        $this->assertDatabaseHas('ads', ['id' => $ad->id, 'status' => 'approved']);
    }

    public function test_owner_sees_status_actions_on_ad_details(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $approved = $this->makeAd(['user_id' => $owner->id, 'status' => 'approved']);
        $paused = $this->makeAd(['user_id' => $owner->id, 'status' => 'paused']);
        $pending = $this->makeAd(['user_id' => $owner->id, 'status' => 'pending']);

        $approvedHtml = $this->actingAs($owner)
            ->get(route('ads.show', $approved))
            ->assertOk()
            ->assertSeeInOrder(['Pause', 'Mark as sold', 'Edit', 'Delete'])
            ->assertDontSee('Put back on sale')
            ->getContent();
        $this->assertStringContainsString('name="status" value="paused"', $approvedHtml);
        $this->assertStringContainsString('adImageLightbox', $approvedHtml);
        $this->assertStringContainsString('Click the photo to view it full size.', $approvedHtml);

        $pausedHtml = $this->actingAs($owner)
            ->get(route('ads.show', $paused))
            ->assertOk()
            ->assertSee('Put back on sale')
            ->assertSee('Mark as sold')
            ->getContent();
        $this->assertStringNotContainsString('name="status" value="paused"', $pausedHtml);

        $pendingHtml = $this->actingAs($owner)
            ->get(route('ads.show', $pending))
            ->assertOk()
            ->assertSee('Pending')
            ->getContent();
        $this->assertStringNotContainsString('name="status"', $pendingHtml);
    }

    public function test_profile_lists_paused_and_sold_ads_with_status(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $this->makeAd(['user_id' => $owner->id, 'title' => 'Paused bike', 'status' => 'paused']);
        $this->makeAd(['user_id' => $owner->id, 'title' => 'Sold bike', 'status' => 'sold']);

        $this->actingAs($owner)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Paused bike')
            ->assertSee('Sold bike')
            ->assertSee('Paused')
            ->assertSee('Sold');
    }

    public function test_owner_cannot_remove_all_photos_without_adding_new_ones(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $ad = $this->makeAd(['user_id' => $owner->id, 'status' => 'approved']);
        $imageId = $ad->images()->first()->id;

        $this->actingAs($owner)
            ->from(route('ads.edit', $ad))
            ->put(route('ads.update', $ad), [
                'title' => $ad->title,
                'description' => $ad->description,
                'price' => $ad->price,
                'category' => $ad->category,
                'location' => $ad->location,
                'contact_email' => $ad->contactEmail(),
                'contact_phone' => $ad->contactPhone(),
                'remove_images' => [$imageId],
            ])
            ->assertRedirect(route('ads.edit', $ad))
            ->assertSessionHasErrors('images');

        $this->assertDatabaseHas('ad_images', ['id' => $imageId]);
    }

    private function beGuest(): self
    {
        $this->app['auth']->forgetGuards();

        return $this;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function newAdPayload(array $overrides = []): array
    {
        return array_merge([
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
        ], $overrides);
    }

    private function makeAd(array $overrides = []): Ad
    {
        $ad = Ad::factory()->create($overrides);
        $ad->images()->create(['path' => 'ads/test.jpg']);

        return $ad->fresh(['images']);
    }
}
