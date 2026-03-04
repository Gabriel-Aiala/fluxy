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

class TransactionEditFormViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_view_uses_flux_layout_and_primary_actions(): void
    {
        $context = $this->makeContext('expense', 'payable');

        $response = $this
            ->actingAs($context['user'])
            ->get(route('transactions.edit', $context['transaction']));

        $response->assertOk();
        $response->assertSee('flux-shell', false);
        $response->assertSee('flux-card', false);
        $response->assertSee('flux-primary-btn', false);
        $response->assertSee('flux-secondary-btn', false);
        $response->assertSee('Atualizar transacao', false);
    }

    public function test_edit_view_keeps_immutable_fields_as_read_only_text(): void
    {
        $context = $this->makeContext('expense', 'payable');
        $transaction = $context['transaction'];

        $response = $this
            ->actingAs($context['user'])
            ->get(route('transactions.edit', $transaction));

        $response->assertOk();
        $response->assertDontSee('name="transaction_group_id"', false);
        $response->assertDontSee('name="installment_number"', false);
        $response->assertDontSee('id="type" name="type"', false);
        $response->assertDontSee('Grupo de transacao', false);
        $response->assertSee((string) $transaction->installment_number, false);
        $response->assertSee('Despesa', false);
    }

    public function test_edit_view_renders_payment_date_readonly_when_payable(): void
    {
        $context = $this->makeContext('expense', 'payable');

        $response = $this
            ->actingAs($context['user'])
            ->get(route('transactions.edit', $context['transaction']));

        $response->assertOk();
        $html = $response->getContent();
        $this->assertMatchesRegularExpression('/<input id="payment_date"[^>]*readonly[^>]*>/', $html);
        $this->assertDoesNotMatchRegularExpression('/<input id="payment_date"[^>]*required[^>]*>/', $html);
    }

    public function test_edit_view_renders_payment_date_required_when_paid(): void
    {
        $context = $this->makeContext('expense', 'paid');

        $response = $this
            ->actingAs($context['user'])
            ->get(route('transactions.edit', $context['transaction']));

        $response->assertOk();
        $html = $response->getContent();
        $this->assertMatchesRegularExpression('/<input id="payment_date"[^>]*required[^>]*>/', $html);
        $this->assertDoesNotMatchRegularExpression('/<input id="payment_date"[^>]*readonly[^>]*>/', $html);
    }

    public function test_edit_view_shows_ptbr_labels_for_expense_type_and_counterparty_context(): void
    {
        $expenseContext = $this->makeContext('expense', 'payable');
        $expenseResponse = $this
            ->actingAs($expenseContext['user'])
            ->get(route('transactions.edit', $expenseContext['transaction']));

        $expenseResponse->assertOk();
        $expenseResponse->assertSee('Tipo de despesa pessoal/profissional', false);
        $expenseResponse->assertSee('Fornecedor', false);
        $expenseResponse->assertSee('Selecione o fornecedor', false);
        $expenseResponse->assertSee('+ Criar fornecedor', false);

        $incomeContext = $this->makeContext('income', 'payable');
        $incomeResponse = $this
            ->actingAs($incomeContext['user'])
            ->get(route('transactions.edit', $incomeContext['transaction']));

        $incomeResponse->assertOk();
        $incomeResponse->assertSee('Tipo de receita pessoal/profissional', false);
        $incomeResponse->assertSee('Cliente', false);
        $incomeResponse->assertSee('Selecione o cliente', false);
        $incomeResponse->assertSee('+ Criar cliente', false);
    }

    private function makeContext(string $type, string $paymentStatus): array
    {
        $organization = Organization::query()->create([
            'name' => 'Org '.strtoupper($type).' '.strtoupper($paymentStatus),
            'cnpj' => str_pad((string) random_int(1, 99999999999999), 14, '0', STR_PAD_LEFT),
        ]);

        $user = User::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $bankAccount = BankAccount::query()->create([
            'organization_id' => $organization->id,
            'name' => 'Conta '.$type,
        ]);

        $paymentMethod = PaymentMethod::query()->create([
            'name' => 'Metodo '.$type.' '.strtoupper($paymentStatus),
        ]);

        $counterparty = Counterparties::query()->create([
            'organization_id' => $organization->id,
            'name' => $type === 'income' ? 'Cliente Teste' : 'Fornecedor Teste',
            'type' => $type === 'income' ? 'client' : 'supplier',
        ]);

        $category = Category::query()->create([
            'organization_id' => $organization->id,
            'name' => $type === 'income' ? 'Categoria Receita' : 'Categoria Despesa',
            'type' => $type,
            'cost_type' => $type === 'income' ? 'income' : 'fixed',
        ]);

        $group = TransactionGroup::query()->create([
            'organization_id' => $organization->id,
            'type' => $type,
            'description' => null,
            'occurred_on' => '2026-03-10',
            'customer_installments' => 3,
            'flow_installments' => 3,
            'anticipation' => false,
        ]);

        $transaction = Transaction::query()->create([
            'organization_id' => $organization->id,
            'transaction_group_id' => $group->id,
            'bank_account_id' => $bankAccount->id,
            'payment_method_id' => $paymentMethod->id,
            'counterparty_id' => $counterparty->id,
            'category_id' => $category->id,
            'installment_number' => 2,
            'expected_payment_date' => '2026-03-10',
            'payment_date' => $paymentStatus === 'paid' ? '2026-03-10' : null,
            'amount' => '150.00',
            'payment_status' => $paymentStatus,
            'expense_type' => 'professional',
            'type' => $type,
        ]);

        return compact('organization', 'user', 'transaction');
    }
}
