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

class DashboardCalendarModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_marks_day_with_paid_transaction_using_payment_date(): void
    {
        $context = $this->makeContext('Pago');

        $this->createTransaction($context, [
            'type' => 'expense',
            'payment_status' => 'paid',
            'payment_date' => '2026-03-05',
            'expected_payment_date' => '2026-03-18',
            'amount' => '120.00',
        ]);

        $response = $this
            ->actingAs($context['user'])
            ->get(route('dashboard', ['month' => '2026-03']));

        $response->assertOk();
        $response->assertSee('data-calendar-day="2026-03-05"', false);
        $response->assertSee("openDayDetails('2026-03-05')", false);
        $response->assertDontSee("openDayDetails('2026-03-18')", false);
    }

    public function test_dashboard_marks_day_with_payable_transaction_using_expected_payment_date(): void
    {
        $context = $this->makeContext('Planejado');

        $this->createTransaction($context, [
            'type' => 'expense',
            'payment_status' => 'payable',
            'payment_date' => '2026-03-02',
            'expected_payment_date' => '2026-03-12',
            'amount' => '85.00',
        ]);

        $response = $this
            ->actingAs($context['user'])
            ->get(route('dashboard', ['month' => '2026-03']));

        $response->assertOk();
        $response->assertSee("openDayDetails('2026-03-12')", false);
        $response->assertDontSee("openDayDetails('2026-03-02')", false);
    }

    public function test_dashboard_day_payload_contains_full_transaction_details(): void
    {
        $context = $this->makeContext('Detalhe');

        $transaction = $this->createTransaction($context, [
            'type' => 'income',
            'payment_status' => 'paid',
            'payment_date' => '2026-03-09',
            'expected_payment_date' => '2026-03-09',
            'amount' => '321.45',
        ]);

        $response = $this
            ->actingAs($context['user'])
            ->get(route('dashboard', ['month' => '2026-03']));

        $response->assertOk();
        $response->assertSee('Recebido', false);
        $response->assertSee($context['incomeCategory']->name, false);
        $response->assertSee($context['clientCounterparty']->name, false);
        $response->assertSee(route('transactions.edit', $transaction), false);
    }

    public function test_dashboard_day_with_both_statuses_shows_both_badges(): void
    {
        $context = $this->makeContext('Badge');

        $this->createTransaction($context, [
            'type' => 'income',
            'payment_status' => 'paid',
            'payment_date' => '2026-03-08',
            'expected_payment_date' => '2026-03-08',
            'amount' => '70.00',
        ]);

        $this->createTransaction($context, [
            'type' => 'expense',
            'payment_status' => 'payable',
            'payment_date' => null,
            'expected_payment_date' => '2026-03-08',
            'amount' => '40.00',
        ]);

        $response = $this
            ->actingAs($context['user'])
            ->get(route('dashboard', ['month' => '2026-03']));

        $response->assertOk();
        $response->assertSee('data-calendar-day="2026-03-08"', false);
        $response->assertSee('data-day-badge="done"', false);
        $response->assertSee('data-day-badge="planned"', false);
    }

    public function test_dashboard_ignores_transactions_from_other_organization(): void
    {
        $contextA = $this->makeContext('OrgA');
        $contextB = $this->makeContext('OrgB');

        $this->createTransaction($contextB, [
            'type' => 'expense',
            'payment_status' => 'payable',
            'payment_date' => null,
            'expected_payment_date' => '2026-03-14',
            'amount' => '210.00',
        ]);

        $response = $this
            ->actingAs($contextA['user'])
            ->get(route('dashboard', ['month' => '2026-03']));

        $response->assertOk();
        $response->assertDontSee("openDayDetails('2026-03-14')", false);
        $response->assertDontSee($contextB['expenseCategory']->name, false);
        $response->assertDontSee($contextB['supplierCounterparty']->name, false);
    }

    public function test_day_without_transactions_is_not_clickable(): void
    {
        $context = $this->makeContext('Vazio');

        $this->createTransaction($context, [
            'type' => 'expense',
            'payment_status' => 'payable',
            'payment_date' => null,
            'expected_payment_date' => '2026-03-05',
            'amount' => '55.00',
        ]);

        $response = $this
            ->actingAs($context['user'])
            ->get(route('dashboard', ['month' => '2026-03']));

        $response->assertOk();
        $this->assertMatchesRegularExpression(
            '/data-calendar-day="2026-03-06"[^>]*data-has-transactions="0"/',
            $response->getContent()
        );
        $response->assertDontSee("openDayDetails('2026-03-06')", false);
    }

    private function makeContext(string $prefix): array
    {
        $organization = Organization::query()->create([
            'name' => 'Org '.$prefix,
            'cnpj' => str_pad((string) random_int(1, 99999999999999), 14, '0', STR_PAD_LEFT),
        ]);

        $user = User::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $bankAccount = BankAccount::query()->create([
            'organization_id' => $organization->id,
            'name' => 'Conta '.$prefix,
        ]);

        $paymentMethod = PaymentMethod::query()->create([
            'name' => 'Metodo '.$prefix,
        ]);

        $supplierCounterparty = Counterparties::query()->create([
            'organization_id' => $organization->id,
            'name' => 'Fornecedor '.$prefix,
            'type' => 'supplier',
        ]);

        $clientCounterparty = Counterparties::query()->create([
            'organization_id' => $organization->id,
            'name' => 'Cliente '.$prefix,
            'type' => 'client',
        ]);

        $expenseCategory = Category::query()->create([
            'organization_id' => $organization->id,
            'name' => 'Categoria Despesa '.$prefix,
            'type' => 'expense',
            'cost_type' => 'fixed',
        ]);

        $incomeCategory = Category::query()->create([
            'organization_id' => $organization->id,
            'name' => 'Categoria Receita '.$prefix,
            'type' => 'income',
            'cost_type' => 'income',
        ]);

        return compact(
            'organization',
            'user',
            'bankAccount',
            'paymentMethod',
            'supplierCounterparty',
            'clientCounterparty',
            'expenseCategory',
            'incomeCategory',
        );
    }

    private function createTransaction(array $context, array $overrides = []): Transaction
    {
        $type = $overrides['type'] ?? 'expense';
        $counterparty = $type === 'income'
            ? $context['clientCounterparty']
            : $context['supplierCounterparty'];
        $category = $type === 'income'
            ? $context['incomeCategory']
            : $context['expenseCategory'];
        $expectedPaymentDate = $overrides['expected_payment_date'] ?? '2026-03-10';

        $transactionGroup = TransactionGroup::query()->create([
            'organization_id' => $context['organization']->id,
            'type' => $type,
            'description' => null,
            'occurred_on' => $expectedPaymentDate,
            'customer_installments' => 1,
            'flow_installments' => 1,
            'anticipation' => false,
        ]);

        return Transaction::query()->create(array_merge([
            'organization_id' => $context['organization']->id,
            'transaction_group_id' => $transactionGroup->id,
            'bank_account_id' => $context['bankAccount']->id,
            'payment_method_id' => $context['paymentMethod']->id,
            'counterparty_id' => $counterparty->id,
            'category_id' => $category->id,
            'installment_number' => 1,
            'expected_payment_date' => $expectedPaymentDate,
            'payment_date' => null,
            'amount' => '50.00',
            'payment_status' => 'payable',
            'expense_type' => 'professional',
            'type' => $type,
        ], $overrides));
    }
}
