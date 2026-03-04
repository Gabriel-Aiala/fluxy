<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCrudScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_create_view_uses_flux_layout_and_hides_organization_input(): void
    {
        $context = $this->makeUserContext();

        $response = $this
            ->actingAs($context['user'])
            ->get(route('categories.create'));

        $response->assertOk();
        $response->assertSee('flux-shell', false);
        $response->assertSee('flux-card', false);
        $response->assertDontSee('name="organization_id"', false);
    }

    public function test_category_store_uses_logged_user_organization_and_rejects_organization_id_payload(): void
    {
        $context = $this->makeUserContext();
        $otherOrganization = Organization::query()->create([
            'name' => 'Outra Org',
            'cnpj' => $this->randomCnpj(),
        ]);

        $invalidResponse = $this
            ->actingAs($context['user'])
            ->from(route('categories.create'))
            ->post(route('categories.store'), [
                'organization_id' => $otherOrganization->id,
                'name' => 'Categoria Invalida',
                'type' => 'income',
                'cost_type' => 'income',
            ]);

        $invalidResponse->assertRedirect(route('categories.create'));
        $invalidResponse->assertSessionHasErrors('organization_id');
        $this->assertDatabaseMissing('category', ['name' => 'Categoria Invalida']);

        $validResponse = $this
            ->actingAs($context['user'])
            ->post(route('categories.store'), [
                'name' => 'Categoria Valida',
                'type' => 'income',
                'cost_type' => 'income',
            ]);

        $validResponse->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('category', [
            'name' => 'Categoria Valida',
            'organization_id' => $context['organization']->id,
            'type' => 'income',
            'cost_type' => 'income',
        ]);
    }

    public function test_category_create_income_context_locks_type_and_sets_income_cost_type(): void
    {
        $context = $this->makeUserContext();

        $response = $this
            ->actingAs($context['user'])
            ->get(route('categories.create', ['type' => 'income']));

        $response->assertOk();
        $response->assertSee('name="type" value="income"', false);
        $response->assertSee('name="cost_type" value="income"', false);
        $response->assertDontSee('<select id="type"', false);
    }

    public function test_category_create_expense_fixed_context_locks_type_and_cost(): void
    {
        $context = $this->makeUserContext();

        $response = $this
            ->actingAs($context['user'])
            ->get(route('categories.create', ['type' => 'expense', 'cost_type' => 'fixed']));

        $response->assertOk();
        $response->assertSee('name="type" value="expense"', false);
        $response->assertSee('name="cost_type" value="fixed"', false);
        $response->assertDontSee('<select id="type"', false);
        $response->assertDontSee('<select id="cost_type"', false);
    }

    public function test_category_store_requires_cost_type_for_expense(): void
    {
        $context = $this->makeUserContext();

        $response = $this
            ->actingAs($context['user'])
            ->from(route('categories.create'))
            ->post(route('categories.store'), [
                'name' => 'Categoria sem custo',
                'type' => 'expense',
            ]);

        $response->assertRedirect(route('categories.create'));
        $response->assertSessionHasErrors('cost_type');
        $this->assertDatabaseMissing('category', ['name' => 'Categoria sem custo']);
    }

    public function test_category_update_income_forces_cost_type_income(): void
    {
        $context = $this->makeUserContext();

        $category = Category::query()->create([
            'organization_id' => $context['organization']->id,
            'name' => 'Categoria Despesa',
            'type' => 'expense',
            'cost_type' => 'fixed',
        ]);

        $response = $this
            ->actingAs($context['user'])
            ->put(route('categories.update', $category), [
                'name' => 'Categoria Receita',
                'type' => 'income',
                'cost_type' => 'fixed',
            ]);

        $response->assertRedirect(route('categories.index'));
        $category->refresh();
        $this->assertSame('income', $category->type);
        $this->assertSame('income', $category->cost_type);
    }

    public function test_category_crud_is_scoped_to_user_organization(): void
    {
        $contextA = $this->makeUserContext('Org A');
        $contextB = $this->makeUserContext('Org B');

        $categoryA = Category::query()->create([
            'organization_id' => $contextA['organization']->id,
            'name' => 'Categoria A',
            'type' => 'income',
            'cost_type' => 'income',
        ]);

        $categoryB = Category::query()->create([
            'organization_id' => $contextB['organization']->id,
            'name' => 'Categoria B',
            'type' => 'expense',
            'cost_type' => 'fixed',
        ]);

        $indexResponse = $this
            ->actingAs($contextA['user'])
            ->get(route('categories.index'));

        $indexResponse->assertOk();
        $indexResponse->assertSee('Categoria A');
        $indexResponse->assertDontSee('Categoria B');

        $this->actingAs($contextA['user'])
            ->get(route('categories.show', $categoryB))
            ->assertNotFound();

        $this->actingAs($contextA['user'])
            ->get(route('categories.edit', $categoryB))
            ->assertNotFound();

        $this->actingAs($contextA['user'])
            ->put(route('categories.update', $categoryB), [
                'name' => 'Tentativa',
                'type' => 'income',
                'cost_type' => 'income',
            ])
            ->assertNotFound();

        $this->actingAs($contextA['user'])
            ->delete(route('categories.destroy', $categoryB))
            ->assertNotFound();

        $this->assertDatabaseHas('category', [
            'id' => $categoryA->id,
            'organization_id' => $contextA['organization']->id,
        ]);
        $this->assertDatabaseHas('category', [
            'id' => $categoryB->id,
            'organization_id' => $contextB['organization']->id,
        ]);
    }

    private function makeUserContext(string $organizationName = 'Fluxy Org'): array
    {
        $organization = Organization::query()->create([
            'name' => $organizationName,
            'cnpj' => $this->randomCnpj(),
        ]);

        $user = User::factory()->create([
            'organization_id' => $organization->id,
        ]);

        return compact('organization', 'user');
    }

    private function randomCnpj(): string
    {
        return str_pad((string) random_int(1, 99999999999999), 14, '0', STR_PAD_LEFT);
    }
}
