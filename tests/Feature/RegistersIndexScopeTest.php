<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Category;
use App\Models\Organization;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistersIndexScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_registers_index_is_scoped_to_user_organization_for_categories_and_bank_accounts(): void
    {
        $contextA = $this->makeUserContext('Org A');
        $contextB = $this->makeUserContext('Org B');

        Category::query()->create([
            'organization_id' => $contextA['organization']->id,
            'name' => 'Categoria Receita A',
            'type' => 'income',
            'cost_type' => 'income',
        ]);
        Category::query()->create([
            'organization_id' => $contextB['organization']->id,
            'name' => 'Categoria Receita B',
            'type' => 'income',
            'cost_type' => 'income',
        ]);
        Category::query()->create([
            'organization_id' => $contextA['organization']->id,
            'name' => 'Categoria Fixa A',
            'type' => 'expense',
            'cost_type' => 'fixed',
        ]);
        Category::query()->create([
            'organization_id' => $contextB['organization']->id,
            'name' => 'Categoria Fixa B',
            'type' => 'expense',
            'cost_type' => 'fixed',
        ]);
        Category::query()->create([
            'organization_id' => $contextA['organization']->id,
            'name' => 'Categoria Variavel A',
            'type' => 'expense',
            'cost_type' => 'variable',
        ]);
        Category::query()->create([
            'organization_id' => $contextB['organization']->id,
            'name' => 'Categoria Variavel B',
            'type' => 'expense',
            'cost_type' => 'variable',
        ]);

        BankAccount::query()->create([
            'organization_id' => $contextA['organization']->id,
            'name' => 'Conta A',
        ]);
        BankAccount::query()->create([
            'organization_id' => $contextB['organization']->id,
            'name' => 'Conta B',
        ]);

        PaymentMethod::query()->create([
            'organization_id' => $contextA['organization']->id,
            'name' => 'Pix A',
        ]);
        PaymentMethod::query()->create([
            'organization_id' => $contextB['organization']->id,
            'name' => 'Pix B',
        ]);

        $response = $this
            ->actingAs($contextA['user'])
            ->get(route('registers.index'));

        $response->assertOk();
        $response->assertSee('Categoria Receita A');
        $response->assertDontSee('Categoria Receita B');
        $response->assertSee('Categoria Fixa A');
        $response->assertDontSee('Categoria Fixa B');
        $response->assertSee('Categoria Variavel A');
        $response->assertDontSee('Categoria Variavel B');
        $response->assertSee('Conta A');
        $response->assertDontSee('Conta B');
        $response->assertSee('Pix A');
        $response->assertDontSee('Pix B');
    }

    public function test_registers_index_returns_403_when_user_has_no_organization(): void
    {
        $user = User::factory()->create([
            'organization_id' => null,
        ]);

        $this->actingAs($user)
            ->get(route('registers.index'))
            ->assertForbidden();
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
