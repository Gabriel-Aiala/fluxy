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

class TransactionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_group_and_single_transaction_when_installments_is_one(): void
    {
        $context = $this->makeExpenseContext();

        $response = $this->actingAs($context['user'])->post(route('transactions.store'), [
            'bank_account_id' => $context['bankAccount']->id,
            'payment_method_id' => $context['paymentMethod']->id,
            'counterparty_id' => $context['counterparty']->id,
            'category_id' => $context['category']->id,
            'installment_number' => 1,
            'expected_payment_date' => '2026-03-15',
            'amount' => '90.00',
            'payment_status' => 'payable',
            'expense_type' => 'professional',
            'type' => 'expense',
        ]);

        $response->assertRedirect(route('transactions.index', ['type' => 'expense']));

        $this->assertDatabaseCount('transaction_group', 1);
        $this->assertDatabaseCount('transaction', 1);

        $group = TransactionGroup::query()->firstOrFail();
        $this->assertSame($context['organization']->id, $group->organization_id);
        $this->assertSame('expense', $group->type);
        $this->assertSame('2026-03-15', $group->occurred_on);
        $this->assertSame(1, $group->customer_installments);
        $this->assertSame(1, $group->flow_installments);
        $this->assertFalse((bool) $group->anticipation);

        $this->assertDatabaseHas('transaction', [
            'organization_id' => $context['organization']->id,
            'transaction_group_id' => $group->id,
            'installment_number' => 1,
            'expected_payment_date' => '2026-03-15',
            'payment_date' => null,
            'amount' => '90.00',
            'payment_status' => 'payable',
            'type' => 'expense',
        ]);
    }

    public function test_store_generates_monthly_installments_with_status_and_amount_split(): void
    {
        $context = $this->makeExpenseContext();

        $response = $this->actingAs($context['user'])->post(route('transactions.store'), [
            'bank_account_id' => $context['bankAccount']->id,
            'payment_method_id' => $context['paymentMethod']->id,
            'counterparty_id' => $context['counterparty']->id,
            'category_id' => $context['category']->id,
            'installment_number' => 3,
            'expected_payment_date' => '2026-01-31',
            'payment_date' => '2026-01-31',
            'amount' => '100.00',
            'payment_status' => 'paid',
            'expense_type' => 'professional',
            'type' => 'expense',
        ]);

        $response->assertRedirect(route('transactions.index', ['type' => 'expense']));

        $this->assertDatabaseCount('transaction_group', 1);
        $this->assertDatabaseCount('transaction', 3);

        $transactions = Transaction::query()
            ->orderBy('installment_number')
            ->get();

        $this->assertSame([1, 2, 3], $transactions->pluck('installment_number')->all());
        $this->assertSame(['2026-01-31', '2026-02-28', '2026-03-31'], $transactions->pluck('expected_payment_date')->map(fn ($date) => $date?->toDateString())->all());
        $this->assertSame(['2026-01-31', null, null], $transactions->pluck('payment_date')->map(fn ($date) => $date?->toDateString())->all());
        $this->assertSame(['paid', 'payable', 'payable'], $transactions->pluck('payment_status')->all());
        $this->assertSame(['33.33', '33.33', '33.34'], $transactions->pluck('amount')->all());
    }

    public function test_store_rejects_paid_transaction_without_payment_date(): void
    {
        $context = $this->makeExpenseContext();

        $response = $this
            ->actingAs($context['user'])
            ->from(route('transactions.create'))
            ->post(route('transactions.store'), [
                'bank_account_id' => $context['bankAccount']->id,
                'payment_method_id' => $context['paymentMethod']->id,
                'counterparty_id' => $context['counterparty']->id,
                'category_id' => $context['category']->id,
                'installment_number' => 1,
                'expected_payment_date' => '2026-03-15',
                'amount' => '90.00',
                'payment_status' => 'paid',
                'expense_type' => 'professional',
                'type' => 'expense',
            ]);

        $response->assertRedirect(route('transactions.create'));
        $response->assertSessionHasErrors('payment_date');
        $this->assertDatabaseCount('transaction_group', 0);
        $this->assertDatabaseCount('transaction', 0);
    }

    public function test_store_rejects_client_supplied_transaction_group_id(): void
    {
        $context = $this->makeExpenseContext();

        $response = $this
            ->actingAs($context['user'])
            ->from(route('transactions.create'))
            ->post(route('transactions.store'), [
                'transaction_group_id' => 999,
                'bank_account_id' => $context['bankAccount']->id,
                'payment_method_id' => $context['paymentMethod']->id,
                'counterparty_id' => $context['counterparty']->id,
                'category_id' => $context['category']->id,
                'installment_number' => 1,
                'expected_payment_date' => '2026-03-15',
                'payment_date' => '2026-03-15',
                'amount' => '90.00',
                'payment_status' => 'payable',
                'expense_type' => 'professional',
                'type' => 'expense',
            ]);

        $response->assertRedirect(route('transactions.create'));
        $response->assertSessionHasErrors('transaction_group_id');
        $this->assertDatabaseCount('transaction_group', 0);
        $this->assertDatabaseCount('transaction', 0);
    }

    public function test_update_rejects_changes_to_group_installment_and_type(): void
    {
        $context = $this->makeExpenseContext();

        $group = TransactionGroup::query()->create([
            'organization_id' => $context['organization']->id,
            'type' => 'expense',
            'description' => null,
            'occurred_on' => '2026-01-10',
            'customer_installments' => 2,
            'flow_installments' => 2,
            'anticipation' => false,
        ]);

        $transaction = Transaction::query()->create([
            'organization_id' => $context['organization']->id,
            'transaction_group_id' => $group->id,
            'bank_account_id' => $context['bankAccount']->id,
            'payment_method_id' => $context['paymentMethod']->id,
            'counterparty_id' => $context['counterparty']->id,
            'category_id' => $context['category']->id,
            'installment_number' => 2,
            'expected_payment_date' => '2026-02-10',
            'payment_date' => '2026-02-10',
            'amount' => '40.00',
            'payment_status' => 'payable',
            'expense_type' => 'professional',
            'type' => 'expense',
        ]);

        $otherGroup = TransactionGroup::query()->create([
            'organization_id' => $context['organization']->id,
            'type' => 'expense',
            'description' => null,
            'occurred_on' => '2026-02-10',
            'customer_installments' => 1,
            'flow_installments' => 1,
            'anticipation' => false,
        ]);

        $response = $this
            ->actingAs($context['user'])
            ->from(route('transactions.edit', $transaction))
            ->put(route('transactions.update', $transaction), [
                'transaction_group_id' => $otherGroup->id,
                'bank_account_id' => $context['bankAccount']->id,
                'payment_method_id' => $context['paymentMethod']->id,
                'counterparty_id' => $context['counterparty']->id,
                'category_id' => $context['category']->id,
                'installment_number' => 9,
                'expected_payment_date' => '2026-02-10',
                'payment_date' => '2026-02-10',
                'amount' => '50.00',
                'payment_status' => 'paid',
                'expense_type' => 'professional',
                'type' => 'income',
            ]);

        $response->assertRedirect(route('transactions.edit', $transaction));
        $response->assertSessionHasErrors([
            'transaction_group_id',
            'installment_number',
            'type',
        ]);

        $transaction->refresh();
        $this->assertSame($group->id, $transaction->transaction_group_id);
        $this->assertSame(2, $transaction->installment_number);
        $this->assertSame('expense', $transaction->type);
        $this->assertSame('40.00', $transaction->amount);
        $this->assertSame('payable', $transaction->payment_status);
    }

    public function test_update_clears_payment_date_when_status_is_payable(): void
    {
        $context = $this->makeExpenseContext();

        $group = TransactionGroup::query()->create([
            'organization_id' => $context['organization']->id,
            'type' => 'expense',
            'description' => null,
            'occurred_on' => '2026-03-01',
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
            'payment_date' => '2026-03-10',
            'amount' => '40.00',
            'payment_status' => 'paid',
            'expense_type' => 'professional',
            'type' => 'expense',
        ]);

        $response = $this
            ->actingAs($context['user'])
            ->put(route('transactions.update', $transaction), [
                'bank_account_id' => $context['bankAccount']->id,
                'payment_method_id' => $context['paymentMethod']->id,
                'counterparty_id' => $context['counterparty']->id,
                'category_id' => $context['category']->id,
                'expected_payment_date' => '2026-03-10',
                'amount' => '40.00',
                'payment_status' => 'payable',
                'expense_type' => 'professional',
            ]);

        $response->assertRedirect(route('transactions.index', ['type' => 'expense']));

        $transaction->refresh();
        $this->assertSame('payable', $transaction->payment_status);
        $this->assertNull($transaction->payment_date);
    }

    public function test_transaction_group_routes_are_read_only(): void
    {
        $context = $this->makeExpenseContext();

        $group = TransactionGroup::query()->create([
            'organization_id' => $context['organization']->id,
            'type' => 'expense',
            'description' => null,
            'occurred_on' => '2026-01-10',
            'customer_installments' => 1,
            'flow_installments' => 1,
            'anticipation' => false,
        ]);

        $this->actingAs($context['user'])
            ->get(route('transaction-groups.index'))
            ->assertOk();

        $this->actingAs($context['user'])
            ->get(route('transaction-groups.show', $group))
            ->assertOk();

        $this->assertContains(
            $this->actingAs($context['user'])->post('/transaction-groups')->getStatusCode(),
            [404, 405]
        );
        $this->assertContains(
            $this->actingAs($context['user'])->put('/transaction-groups/'.$group->id)->getStatusCode(),
            [404, 405]
        );
        $this->assertContains(
            $this->actingAs($context['user'])->delete('/transaction-groups/'.$group->id)->getStatusCode(),
            [404, 405]
        );
        $this->assertContains(
            $this->actingAs($context['user'])->post('/quick-create/transaction-groups')->getStatusCode(),
            [404, 405]
        );
    }

    private function makeExpenseContext(): array
    {
        $organization = Organization::query()->create([
            'name' => 'Fluxy Org',
            'cnpj' => '12345678000199',
        ]);

        $user = User::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $bankAccount = BankAccount::query()->create([
            'organization_id' => $organization->id,
            'name' => 'Conta Principal',
        ]);

        $paymentMethod = PaymentMethod::query()->create([
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
}
