<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankAccountCrudScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_bank_account_create_view_uses_flux_layout_and_hides_organization_input(): void
    {
        $context = $this->makeUserContext();

        $response = $this
            ->actingAs($context['user'])
            ->get(route('bank-accounts.create'));

        $response->assertOk();
        $response->assertSee('flux-shell', false);
        $response->assertSee('flux-card', false);
        $response->assertDontSee('name="organization_id"', false);
    }

    public function test_bank_account_store_uses_logged_user_organization_and_rejects_organization_id_payload(): void
    {
        $context = $this->makeUserContext();
        $otherOrganization = Organization::query()->create([
            'name' => 'Outra Org',
            'cnpj' => $this->randomCnpj(),
        ]);

        $invalidResponse = $this
            ->actingAs($context['user'])
            ->from(route('bank-accounts.create'))
            ->post(route('bank-accounts.store'), [
                'organization_id' => $otherOrganization->id,
                'name' => 'Conta Invalida',
            ]);

        $invalidResponse->assertRedirect(route('bank-accounts.create'));
        $invalidResponse->assertSessionHasErrors('organization_id');
        $this->assertDatabaseMissing('bank_account', ['name' => 'Conta Invalida']);

        $validResponse = $this
            ->actingAs($context['user'])
            ->post(route('bank-accounts.store'), [
                'name' => 'Conta Valida',
            ]);

        $validResponse->assertRedirect(route('bank-accounts.index'));
        $this->assertDatabaseHas('bank_account', [
            'name' => 'Conta Valida',
            'organization_id' => $context['organization']->id,
        ]);
    }

    public function test_bank_account_crud_is_scoped_to_user_organization(): void
    {
        $contextA = $this->makeUserContext('Org A');
        $contextB = $this->makeUserContext('Org B');

        $bankAccountA = BankAccount::query()->create([
            'organization_id' => $contextA['organization']->id,
            'name' => 'Conta A',
        ]);

        $bankAccountB = BankAccount::query()->create([
            'organization_id' => $contextB['organization']->id,
            'name' => 'Conta B',
        ]);

        $indexResponse = $this
            ->actingAs($contextA['user'])
            ->get(route('bank-accounts.index'));

        $indexResponse->assertOk();
        $indexResponse->assertSee('Conta A');
        $indexResponse->assertDontSee('Conta B');

        $this->actingAs($contextA['user'])
            ->get(route('bank-accounts.show', $bankAccountB))
            ->assertNotFound();

        $this->actingAs($contextA['user'])
            ->get(route('bank-accounts.edit', $bankAccountB))
            ->assertNotFound();

        $this->actingAs($contextA['user'])
            ->put(route('bank-accounts.update', $bankAccountB), [
                'name' => 'Tentativa',
            ])
            ->assertNotFound();

        $this->actingAs($contextA['user'])
            ->delete(route('bank-accounts.destroy', $bankAccountB))
            ->assertNotFound();

        $this->assertDatabaseHas('bank_account', [
            'id' => $bankAccountA->id,
            'organization_id' => $contextA['organization']->id,
        ]);
        $this->assertDatabaseHas('bank_account', [
            'id' => $bankAccountB->id,
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
