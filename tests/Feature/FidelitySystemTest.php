<?php

declare(strict_types=1);

namespace Cartino\Tests\Feature;

use Cartino\Models\Customer;
use Cartino\Models\FidelityCard;
use Cartino\Models\FidelityTransaction;
use Cartino\Models\Order;
use Cartino\Services\FidelityService;
use Cartino\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FidelitySystemTest extends TestCase
{
    use RefreshDatabase;

    protected FidelityService $fidelityService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fidelityService = app(FidelityService::class);

        // Abilita il sistema di fedeltà per i test
        config(['cartino.fidelity.enabled' => true]);
        config(['cartino.fidelity.points.enabled' => true]);
    }

    public function test_can_create_fidelity_card_for_customer()
    {
        $customer = Customer::factory()->create();

        $card = $this->fidelityService->createFidelityCard($customer);

        $this->assertInstanceOf(FidelityCard::class, $card);
        $this->assertEquals($customer->id, $card->customer_id);
        $this->assertTrue($card->is_active);
        $this->assertNotNull($card->card_number);
        $this->assertStringStartsWith('FID-', $card->card_number);
    }

    public function test_fidelity_card_generates_unique_number()
    {
        $customer1 = Customer::factory()->create();
        $customer2 = Customer::factory()->create();

        $card1 = $this->fidelityService->createFidelityCard($customer1);
        $card2 = $this->fidelityService->createFidelityCard($customer2);

        $this->assertNotEquals($card1->card_number, $card2->card_number);
    }

    public function test_can_add_points_to_fidelity_card()
    {
        $customer = Customer::factory()->create();
        $card = $this->fidelityService->createFidelityCard($customer);

        $points = 100;
        $reason = 'Test points';

        $transaction = $card->addPoints($points, $reason);

        $this->assertInstanceOf(FidelityTransaction::class, $transaction);
        $this->assertEquals($points, $transaction->points);
        $this->assertEquals($reason, $transaction->description);
        $this->assertEquals('earned', $transaction->type);

        $card->refresh();
        $this->assertEquals($points, $card->total_points);
        $this->assertEquals($points, $card->available_points);
        $this->assertEquals($points, $card->total_earned);
    }

    public function test_can_redeem_points_from_fidelity_card()
    {
        $customer = Customer::factory()->create();
        $card = $this->fidelityService->createFidelityCard($customer);

        // Aggiungi prima alcuni punti
        $card->addPoints(200, 'Initial points');

        $pointsToRedeem = 100;
        $reason = 'Test redemption';

        $transaction = $card->redeemPoints($pointsToRedeem, $reason);

        $this->assertInstanceOf(FidelityTransaction::class, $transaction);
        $this->assertEquals(-$pointsToRedeem, $transaction->points);
        $this->assertEquals($reason, $transaction->description);
        $this->assertEquals('redeemed', $transaction->type);

        $card->refresh();
        $this->assertEquals(200, $card->total_points); // Non cambia
        $this->assertEquals(100, $card->available_points); // Diminuisce
        $this->assertEquals($pointsToRedeem, $card->total_redeemed);
    }

    public function test_cannot_redeem_more_points_than_available()
    {
        $customer = Customer::factory()->create();
        $card = $this->fidelityService->createFidelityCard($customer);

        $card->addPoints(50, 'Small amount');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Insufficient points for redemption.');

        $card->redeemPoints(100, 'Too many points');
    }

    public function test_calculates_points_for_amount_correctly()
    {
        $customer = Customer::factory()->create();
        $card = $this->fidelityService->createFidelityCard($customer);

        // Test con tier base (1 punto per euro)
        $amount = 100.00;
        $points = $card->calculatePointsForAmount($amount, 'EUR');

        $this->assertEquals(100, $points);

        // Simula spesa per raggiungere tier superiore
        $card->update(['total_spent_amount' => 500]);
        $points = $card->calculatePointsForAmount($amount, 'EUR');

        // Con tier da 500€+ dovrebbe essere 2 punti per euro
        $this->assertEquals(200, $points);
    }

    public function test_processes_order_for_fidelity_points()
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'total' => 100.00,
            'currency' => 'EUR',
        ]);

        $transaction = $this->fidelityService->processOrderForPoints($order);

        $this->assertInstanceOf(FidelityTransaction::class, $transaction);
        $this->assertEquals('earned', $transaction->type);
        $this->assertEquals($order->id, $transaction->order_id);

        $customer->refresh();
        $card = $customer->fidelityCard;
        $this->assertNotNull($card);
        $this->assertEquals(100, $card->available_points);
        $this->assertEquals(100.00, $card->total_spent_amount);
    }

    public function test_can_find_card_by_number()
    {
        $customer = Customer::factory()->create();
        $card = $this->fidelityService->createFidelityCard($customer);

        $foundCard = $this->fidelityService->findCardByNumber($card->card_number);

        $this->assertNotNull($foundCard);
        $this->assertEquals($card->id, $foundCard->id);
    }

    public function test_returns_null_for_invalid_card_number()
    {
        $foundCard = $this->fidelityService->findCardByNumber('INVALID-123');

        $this->assertNull($foundCard);
    }

    public function test_calculates_tier_correctly()
    {
        $customer = Customer::factory()->create();
        $card = $this->fidelityService->createFidelityCard($customer);

        // Test tier base
        $tier = $card->getCurrentTier();
        $this->assertEquals(0, $tier['threshold']);
        $this->assertEquals(1, $tier['rate']);

        // Simula spesa per tier Silver (100€+)
        $card->update(['total_spent_amount' => 150]);
        $tier = $card->getCurrentTier();
        $this->assertEquals(100, $tier['threshold']);
        $this->assertEquals(1.5, $tier['rate']);

        // Test next tier
        $nextTier = $card->getNextTier();
        $this->assertEquals(500, $nextTier['threshold']);
        $this->assertEquals(2, $nextTier['rate']);
        $this->assertEquals(350, $nextTier['amount_needed']);
    }

    public function test_can_expire_points()
    {
        $customer = Customer::factory()->create();
        $card = $this->fidelityService->createFidelityCard($customer);

        // Crea una transazione con punti scaduti
        $expiredTransaction = FidelityTransaction::factory()->create([
            'fidelity_card_id' => $card->id,
            'type' => 'earned',
            'points' => 100,
            'expires_at' => now()->subDay(),
            'expired' => false,
        ]);

        $card->update([
            'total_points' => 100,
            'available_points' => 100,
            'total_earned' => 100,
        ]);

        $card->expirePoints();

        $expiredTransaction->refresh();
        $this->assertTrue($expiredTransaction->expired);

        $card->refresh();
        $this->assertEquals(0, $card->available_points);

        // Verifica che sia stata creata una transazione di scadenza
        $expirationTransaction = $card
            ->transactions()
            ->where('type', 'expired')
            ->where('reference_transaction_id', $expiredTransaction->id)
            ->first();

        $this->assertNotNull($expirationTransaction);
        $this->assertEquals(-100, $expirationTransaction->points);
    }

    public function test_gets_card_statistics()
    {
        $customer = Customer::factory()->create();
        $card = FidelityCard::factory()->create([
            'customer_id' => $customer->id,
            'total_points' => 500,
            'available_points' => 300,
            'total_earned' => 600,
            'total_redeemed' => 200,
            'total_spent_amount' => 750.00,
        ]);

        $statistics = $this->fidelityService->getCardStatistics($card);

        $this->assertEquals($card->card_number, $statistics['card_number']);
        $this->assertEquals(500, $statistics['total_points']);
        $this->assertEquals(300, $statistics['available_points']);
        $this->assertEquals(600, $statistics['total_earned']);
        $this->assertEquals(200, $statistics['total_redeemed']);
        $this->assertEquals(750.00, $statistics['total_spent']);
        $this->assertArrayHasKey('current_tier', $statistics);
        $this->assertArrayHasKey('next_tier', $statistics);
    }
}
