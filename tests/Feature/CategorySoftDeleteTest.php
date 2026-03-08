<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Category;
use App\Models\Counterparties;
use App\Models\Organization;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\TransactionGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategorySoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_destroy_soft_deletes_category_used_in_transactions(): void
    {
        $context = $this->makeExpenseContext();

        $group = TransactionGroup::query()->create([
            'organization_id' => $context['organization']->id,
            'type' => 'expense',
            'description' => null,
            'occurred_on' => '2026-03-10',
            'customer_installments' => 1,
            'flow_installments' => 1,
            'anticipation' => false,
        ]);

        $transaction = Transaction::query()->create([
            'organization_id' => $context['organization']->id,
            'transaction_group_id' => $group->id,
            'bank_account_id' => $context['bankAccount']->id,
            'payment_method_id' => $context['paymentMethod']->id,
            'counterparty_id' => $context['counterparty']->id,
            'category_id' => $context['category']->id,
            'installment_number' => 1,
            'expected_payment_date' => '2026-03-10',
            'payment_date' => null,
            'amount' => '120.00',
            'payment_status' => 'payable',
            'expense_type' => 'professional',
            'type' => 'expense',
        ]);

        $response = $this
            ->actingAs($context['user'])
            ->delete(route('categories.destroy', $context['category']));

        $response->assertRedirect(route('categories.index'));
        $this->assertSoftDeleted('category', [
            'id' => $context['category']->id,
        ]);
        $this->assertDatabaseHas('transaction', [
            'id' => $transaction->id,
            'category_id' => $context['category']->id,
        ]);
    }

    public function test_transaction_relation_keeps_deleted_category_name(): void
    {
        $context = $this->makeExpenseContext();

        $group = TransactionGroup::query()->create([
            'organization_id' => $context['organization']->id,
            'type' => 'expense',
            'description' => null,
            'occurred_on' => '2026-03-10',
            'customer_installments' => 1,
            'flow_installments' => 1,
            'anticipation' => false,
        ]);

        $transaction = Transaction::query()->create([
            'organization_id' => $context['organization']->id,
            'transaction_group_id' => $group->id,
            'bank_account_id' => $context['bankAccount']->id,
            'payment_method_id' => $context['paymentMethod']->id,
            'counterparty_id' => $context['counterparty']->id,
            'category_id' => $context['category']->id,
            'installment_number' => 1,
            'expected_payment_date' => '2026-03-10',
            'payment_date' => null,
            'amount' => '120.00',
            'payment_status' => 'payable',
            'expense_type' => 'professional',
            'type' => 'expense',
        ]);

        $context['category']->delete();

        $loadedTransaction = Transaction::query()
            ->with('category')
            ->findOrFail($transaction->id);

        $this->assertNotNull($loadedTransaction->category);
        $this->assertSame('Despesas Gerais', $loadedTransaction->category->name);
        $this->assertTrue($loadedTransaction->category->trashed());
    }

    private function makeExpenseContext(): array
    {
        $organization = Organization::query()->create([
            'name' => 'Fluxy Org',
            'cnpj' => $this->randomCnpj(),
        ]);

        $user = User::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $bankAccount = BankAccount::query()->create([
            'organization_id' => $organization->id,
            'name' => 'Conta Principal',
        ]);

        $paymentMethod = PaymentMethod::query()->create([
            'organization_id' => $organization->id,
            'name' => 'Pix',
        ]);

        $counterparty = Counterparties::query()->create([
            'organization_id' => $organization->id,
            'name' => 'Fornecedor A',
            'type' => 'supplier',
        ]);

        $category = Category::query()->create([
            'organization_id' => $organization->id,
            'name' => 'Despesas Gerais',
            'type' => 'expense',
            'cost_type' => 'fixed',
        ]);

        return compact(
            'organization',
            'user',
            'bankAccount',
            'paymentMethod',
            'counterparty',
            'category'
        );
    }

    private function randomCnpj(): string
    {
        return str_pad((string) random_int(1, 99999999999999), 14, '0', STR_PAD_LEFT);
    }
}
