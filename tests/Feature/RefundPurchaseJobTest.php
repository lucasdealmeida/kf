<?php

namespace Tests\Feature;

use App\Jobs\RefundPurchaseJob;
use App\Mail\CoopRefunded;
use App\Models\Buyer;
use App\Models\Coop;
use App\Models\Purchase;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RefundPurchaseJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function refund_a_purchased_paid_with_credits()
    {
        $coop = Coop::factory()->create();

        Purchase::factory()->for($coop)->create();

        $purchase = Purchase::factory()->for($coop)->create();

        Transaction::factory()->for($purchase)->create([
            'buyer_id'    => $purchase->buyer_id,
            'coop_id'     => $purchase->coop_id,
            'source'      => 'KickfurtherCredits',
            'type'        => 'purchase',
            'amount'      => 20,
            'is_canceled' => false,
            'is_pending'  => false,
        ]);

        $refundPurchaseJob = new RefundPurchaseJob($purchase);

        $refundPurchaseJob->handle();

        $this->assertDatabaseHas('purchases', [
            'id'            => $purchase->id,
            'coop_canceled' => true,
        ]);

        $this->assertDatabaseCount('transactions', 2);

        $this->assertDatabaseHas('transactions', [
            'amount'      => 20,
            'source'      => 'KickfurtherCredits',
            'type'        => 'refund',
            'purchase_id' => $purchase->id,
            'buyer_id'    => $purchase->buyer_id,
            'coop_id'     => $purchase->coop_id,
            'is_canceled' => false,
            'is_pending'  => false,
        ]);
    }

    /** @test */
    public function refund_a_purchased_paid_with_funds()
    {
        $coop = Coop::factory()->create();

        Purchase::factory()->for($coop)->create();

        $purchase = Purchase::factory()->for($coop)->create();

        Transaction::factory()->for($purchase)->create([
            'buyer_id'    => $purchase->buyer_id,
            'coop_id'     => $purchase->coop_id,
            'source'      => 'KickfurtherFunds',
            'type'        => 'purchase',
            'amount'      => 20,
            'is_canceled' => false,
            'is_pending'  => false,
        ]);

        $refundPurchaseJob = new RefundPurchaseJob($purchase);

        $refundPurchaseJob->handle();

        $this->assertDatabaseHas('purchases', [
            'id'            => $purchase->id,
            'coop_canceled' => true,
        ]);

        $this->assertDatabaseCount('transactions', 2);

        $this->assertDatabaseHas('transactions', [
            'amount'      => 20,
            'source'      => 'KickfurtherFunds',
            'type'        => 'refund',
            'purchase_id' => $purchase->id,
            'buyer_id'    => $purchase->buyer_id,
            'coop_id'     => $purchase->coop_id,
            'is_canceled' => false,
            'is_pending'  => false,
        ]);
    }

    /** @test */
    public function refund_a_purchased_as_credit_paid_with_credit_card_and_is_charged()
    {
        $buyer = Buyer::factory()->create(['refund_pref' => 'credit']);

        $coop = Coop::factory()->create();

        Purchase::factory()->for($coop)->create();

        $purchase = Purchase::factory()->for($coop)->for($buyer)->create();

        Transaction::factory()->for($purchase)->create([
            'buyer_id'    => $purchase->buyer_id,
            'coop_id'     => $purchase->coop_id,
            'source'      => 'CreditCard',
            'type'        => 'purchase',
            'amount'      => 20,
            'is_canceled' => false,
            'is_pending'  => false,
        ]);

        $refundPurchaseJob = new RefundPurchaseJob($purchase);

        $refundPurchaseJob->handle();

        $this->assertDatabaseHas('purchases', [
            'id'            => $purchase->id,
            'coop_canceled' => true,
        ]);

        $this->assertDatabaseCount('transactions', 2);

        $this->assertDatabaseHas('transactions', [
            'amount'      => 20,
            'source'      => 'KickfurtherCredits',
            'type'        => 'refund',
            'purchase_id' => $purchase->id,
            'buyer_id'    => $purchase->buyer_id,
            'coop_id'     => $purchase->coop_id,
            'is_canceled' => false,
            'is_pending'  => false,
        ]);
    }

    /** @test */
    public function refund_a_purchased_as_cc_paid_with_credit_card_charged()
    {
        $buyer = Buyer::factory()->create(['refund_pref' => 'cc']);

        $coop = Coop::factory()->create();

        Purchase::factory()->for($coop)->create();

        $purchase = Purchase::factory()->for($coop)->for($buyer)->create();

        Transaction::factory()->for($purchase)->create([
            'buyer_id'    => $purchase->buyer_id,
            'coop_id'     => $purchase->coop_id,
            'source'      => 'CreditCard',
            'type'        => 'purchase',
            'amount'      => 20,
            'is_canceled' => false,
            'is_pending'  => false,
        ]);

        $refundPurchaseJob = new RefundPurchaseJob($purchase);

        $refundPurchaseJob->handle();

        $this->assertDatabaseHas('purchases', [
            'id'            => $purchase->id,
            'coop_canceled' => true,
        ]);

        $this->assertDatabaseCount('transactions', 2);

        $this->assertDatabaseHas('transactions', [
            'amount'      => 20,
            'source'      => 'CreditCard',
            'type'        => 'refund',
            'purchase_id' => $purchase->id,
            'buyer_id'    => $purchase->buyer_id,
            'coop_id'     => $purchase->coop_id,
            'is_canceled' => false,
            'is_pending'  => false,
        ]);
    }

    /** @test */
    public function cancel_the_pending_transaction()
    {
        $coop = Coop::factory()->create();

        Purchase::factory()->for($coop)->create();

        $purchase = Purchase::factory()->for($coop)->create();

        $transaction = Transaction::factory()->for($purchase)->create([
            'coop_id'     => $purchase->coop_id,
            'buyer_id'    => $purchase->buyer_id,
            'source'      => 'CreditCard',
            'type'        => 'purchase',
            'amount'      => 20,
            'is_canceled' => false,
            'is_pending'  => true,
        ]);

        $refundPurchaseJob = new RefundPurchaseJob($purchase);

        $refundPurchaseJob->handle();

        $this->assertDatabaseHas('purchases', [
            'id'            => $purchase->id,
            'coop_canceled' => true,
        ]);

        $this->assertDatabaseCount('transactions', 1);

        $this->assertDatabaseHas('transactions', [
            'id'          => $transaction->id,
            'amount'      => 20,
            'source'      => 'CreditCard',
            'type'        => 'purchase',
            'purchase_id' => $purchase->id,
            'buyer_id'    => $purchase->buyer_id,
            'coop_id'     => $purchase->coop_id,
            'is_canceled' => true,
            'is_pending'  => false,
        ]);
    }

    /** @test */
    public function mailed_coop_owner_when_all_purchases_are_canceled()
    {
        Mail::fake();

        $coop = Coop::factory()->create();

        Purchase::factory()->for($coop)->create(['coop_canceled' => true]);

        $purchase = Purchase::factory()->for($coop)->create();

        Transaction::factory()->for($purchase)->create([
            'buyer_id'    => $purchase->buyer_id,
            'coop_id'     => $coop->id,
            'source'      => 'KickfurtherCredits',
            'type'        => 'purchase',
            'amount'      => 20,
            'is_canceled' => false,
            'is_pending'  => false,
        ]);

        $refundPurchaseJob = new RefundPurchaseJob($purchase);

        $refundPurchaseJob->handle();

        $this->assertDatabaseHas('coops', [
            'id' => $coop->id,
            'status' => 'canceled'
        ]);

        Mail::assertSent(function(CoopRefunded $mail) use ($coop){
            return $mail->coop->id == $coop->id;
        });
    }
}
