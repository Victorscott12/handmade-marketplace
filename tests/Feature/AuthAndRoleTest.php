<?php

namespace Tests\Feature;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kiểm thử đăng nhập và phân quyền. Chạy: php artisan test
 * (Cần bật extension pdo_sqlite trong php.ini của XAMPP vì test dùng SQLite trong bộ nhớ.)
 */
class AuthAndRoleTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role, string $status = 'active'): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role, 'status' => $status])->save();

        return $user;
    }

    public function test_buyer_can_register(): void
    {
        $this->post('/register', [
            'name' => 'Người Mua', 'email' => 'mua@test.vn',
            'password' => 'password123', 'password_confirmation' => 'password123',
            'account_type' => 'buyer',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'mua@test.vn', 'role' => 'buyer']);
    }

    public function test_seller_registration_goes_to_create_shop(): void
    {
        $this->post('/register', [
            'name' => 'Người Bán', 'email' => 'ban@test.vn',
            'password' => 'password123', 'password_confirmation' => 'password123',
            'account_type' => 'seller',
        ])->assertRedirect(route('seller.shop.create'));

        $this->assertDatabaseHas('users', ['email' => 'ban@test.vn', 'role' => 'seller']);
    }

    public function test_cannot_register_as_admin(): void
    {
        $this->post('/register', [
            'name' => 'Hacker', 'email' => 'hack@test.vn',
            'password' => 'password123', 'password_confirmation' => 'password123',
            'account_type' => 'admin',
        ])->assertSessionHasErrors('account_type');

        $this->assertDatabaseMissing('users', ['email' => 'hack@test.vn']);
    }

    public function test_login_redirects_by_role(): void
    {
        $admin = $this->makeUser('admin');

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_blocked_user_cannot_login(): void
    {
        $user = $this->makeUser('buyer', 'blocked');

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
        $this->get('/seller/dashboard')->assertRedirect(route('login'));
    }

    public function test_buyer_cannot_open_admin_or_seller_area(): void
    {
        $buyer = $this->makeUser('buyer');

        $this->actingAs($buyer)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($buyer)->get('/seller/dashboard')->assertForbidden();
    }

    public function test_seller_with_pending_shop_is_sent_to_pending_page(): void
    {
        $seller = $this->makeUser('seller');
        $seller->shop()->create(['name' => 'Shop Test', 'slug' => 'shop-test']); // mặc định pending

        $this->actingAs($seller)->get('/seller/dashboard')
            ->assertRedirect(route('seller.shop.pending'));
    }

    public function test_seller_with_approved_shop_sees_dashboard(): void
    {
        $seller = $this->makeUser('seller');
        $shop = $seller->shop()->create(['name' => 'Shop Test', 'slug' => 'shop-test']);
        $shop->forceFill(['status' => Shop::STATUS_APPROVED])->save();

        $this->actingAs($seller)->get('/seller/dashboard')
            ->assertOk()
            ->assertSee('Shop Test');
    }

    public function test_buyer_can_become_seller(): void
    {
        $buyer = $this->makeUser('buyer');

        $this->actingAs($buyer)->post('/profile/become-seller')
            ->assertRedirect(route('seller.shop.create'));

        $this->assertSame('seller', $buyer->fresh()->role);
    }
}
