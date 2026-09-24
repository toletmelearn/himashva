<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_create_and_update_are_logged(): void
    {
        $product = Product::factory()->create(['price' => 100]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'auditable_type' => Product::class,
            'auditable_id' => $product->id,
        ]);

        $product->update(['price' => 150]);

        $log = AuditLog::where('auditable_id', $product->id)
            ->where('auditable_type', Product::class)
            ->where('action', 'updated')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(100, $log->before['price']);
        $this->assertEquals(150, $log->after['price']);
    }

    public function test_coupon_delete_is_logged(): void
    {
        $coupon = Coupon::create([
            'code' => 'TESTCODE', 'type' => 'fixed', 'value' => 10, 'per_user_limit' => 1, 'is_active' => true,
        ]);
        $couponId = $coupon->id;
        $coupon->delete();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'deleted',
            'auditable_type' => Coupon::class,
            'auditable_id' => $couponId,
        ]);
    }

    public function test_user_password_is_never_logged(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret-password')]);

        $log = AuditLog::where('auditable_id', $user->id)
            ->where('auditable_type', User::class)
            ->where('action', 'created')
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayNotHasKey('password', $log->after);
    }
}
