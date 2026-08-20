<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_scope_only_returns_records_for_the_requested_business(): void
    {
        $firstBusiness = Business::factory()->create();
        $secondBusiness = Business::factory()->create();
        $firstCategory = Category::factory()->for($firstBusiness)->create();
        Category::factory()->for($secondBusiness)->create();

        $categories = Category::query()->forBusiness($firstBusiness)->get();

        $this->assertCount(1, $categories);
        $this->assertTrue($categories->first()->is($firstCategory));
    }

    public function test_business_admin_cannot_authorize_another_business_resource(): void
    {
        $firstBusiness = Business::factory()->create();
        $secondBusiness = Business::factory()->create();
        $admin = User::factory()->for($firstBusiness)->create();
        $ownCategory = Category::factory()->for($firstBusiness)->create();
        $otherCategory = Category::factory()->for($secondBusiness)->create();

        $this->assertTrue(Gate::forUser($admin)->allows('update', $ownCategory));
        $this->assertTrue(Gate::forUser($admin)->denies('update', $otherCategory));
    }
}
