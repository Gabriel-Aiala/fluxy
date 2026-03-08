<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodCrudScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_method_create_view_hides_organization_input(): void
    {
        $context = $this->makeUserContext();

        $response = $this
            ->actingAs($context['user'])
            ->get(route('payment-methods.create'));

        $response->assertOk();
        $response->assertDontSee('name="organization_id"', false);
    }

    public function test_payment_method_store_uses_logged_user_organization_and_scopes_uniqueness(): void
    {
        $contextA = $this->makeUserContext('Org A');
        $contextB = $this->makeUserContext('Org B');

        $invalidResponse = $this
            ->actingAs($contextA['user'])
            ->from(route('payment-methods.create'))
            ->post(route('payment-methods.store'), [
                'organization_id' => $contextB['organization']->id,
                'name' => 'Pix',
            ]);

        $invalidResponse->assertRedirect(route('payment-methods.create'));
        $invalidResponse->assertSessionHasErrors('organization_id');

        $validResponseA = $this
            ->actingAs($contextA['user'])
            ->post(route('payment-methods.store'), [
                'name' => 'Pix',
            ]);

        $validResponseA->assertRedirect(route('payment-methods.index'));
        $this->assertDatabaseHas('payment_method', [
            'organization_id' => $contextA['organization']->id,
            'name' => 'Pix',
        ]);

        $duplicateSameOrgResponse = $this
            ->actingAs($contextA['user'])
            ->from(route('payment-methods.create'))
            ->post(route('payment-methods.store'), [
                'name' => 'Pix',
            ]);

        $duplicateSameOrgResponse->assertRedirect(route('payment-methods.create'));
        $duplicateSameOrgResponse->assertSessionHasErrors('name');

        $validResponseB = $this
            ->actingAs($contextB['user'])
            ->post(route('payment-methods.store'), [
                'name' => 'Pix',
            ]);

        $validResponseB->assertRedirect(route('payment-methods.index'));
        $this->assertDatabaseHas('payment_method', [
            'organization_id' => $contextB['organization']->id,
            'name' => 'Pix',
        ]);
    }

    public function test_payment_method_crud_is_scoped_to_user_organization(): void
    {
        $contextA = $this->makeUserContext('Org A');
        $contextB = $this->makeUserContext('Org B');

        $paymentMethodA = PaymentMethod::query()->create([
            'organization_id' => $contextA['organization']->id,
            'name' => 'Pix A',
        ]);

        $paymentMethodB = PaymentMethod::query()->create([
            'organization_id' => $contextB['organization']->id,
            'name' => 'Pix B',
        ]);

        $indexResponse = $this
            ->actingAs($contextA['user'])
            ->get(route('payment-methods.index'));

        $indexResponse->assertOk();
        $indexResponse->assertSee('Pix A');
        $indexResponse->assertDontSee('Pix B');

        $this->actingAs($contextA['user'])
            ->get(route('payment-methods.show', $paymentMethodB))
            ->assertNotFound();

        $this->actingAs($contextA['user'])
            ->get(route('payment-methods.edit', $paymentMethodB))
            ->assertNotFound();

        $this->actingAs($contextA['user'])
            ->put(route('payment-methods.update', $paymentMethodB), [
                'name' => 'Tentativa',
            ])
            ->assertNotFound();

        $this->actingAs($contextA['user'])
            ->delete(route('payment-methods.destroy', $paymentMethodB))
            ->assertNotFound();

        $this->assertDatabaseHas('payment_method', [
            'id' => $paymentMethodA->id,
            'organization_id' => $contextA['organization']->id,
        ]);
        $this->assertDatabaseHas('payment_method', [
            'id' => $paymentMethodB->id,
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
