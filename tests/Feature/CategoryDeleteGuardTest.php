<?php

namespace Tests\Feature;

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryDeleteGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_can_delete_returns_false_for_a_category_with_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $this->assertFalse(CategoryResource::canDelete($category));
    }

    public function test_can_delete_returns_true_for_an_empty_category(): void
    {
        $category = Category::factory()->create();

        $this->assertTrue(CategoryResource::canDelete($category));
    }

    public function test_can_delete_any_is_always_false(): void
    {
        $this->assertFalse(CategoryResource::canDeleteAny());
    }

    public function test_delete_action_is_hidden_for_a_category_with_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        Livewire::actingAs($this->admin())
            ->test(ListCategories::class)
            ->assertTableActionHidden('delete', $category);

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_deleting_an_empty_category_via_table_action_succeeds(): void
    {
        $category = Category::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(ListCategories::class)
            ->callTableAction('delete', $category);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
