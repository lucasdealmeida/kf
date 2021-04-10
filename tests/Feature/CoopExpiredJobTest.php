<?php

namespace Tests\Feature;

use App\Jobs\CoopExpiredJob;
use App\Jobs\RefundPurchaseJob;
use App\Mail\CoopCanceled;
use App\Mail\CoopCompletedAndGoalAchieved;
use App\Models\Coop;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CoopExpiredJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function change_the_status_to_complete_when_goal_is_achieve()
    {
        Mail::fake();

        $coop = Coop::factory()->expired()->create([
            'status' => 'active',
            'goal' => 10
        ]);

        Purchase::factory()->for($coop)->create(['amount' => 10]);

        $coopExpiredJob = new CoopExpiredJob($coop);

        $coopExpiredJob->handle();

        $this->assertDatabaseHas('coops', [
            'id' => $coop->id,
            'status' => 'completed'
        ]);
    }

    /** @test */
    public function owner_should_be_mailed_when_status_changed_to_completed_and_the_goal_is_achieved()
    {
        Mail::fake();

        $coop = Coop::factory()->expired()->create([
            'status' => 'active',
            'goal' => 10
        ]);

        Purchase::factory()->for($coop)->create(['amount' => 10]);

        $coopExpiredJob = new CoopExpiredJob($coop);

        $coopExpiredJob->handle();

        Mail::assertSent(function(CoopCompletedAndGoalAchieved $mail) use ($coop){
            return $mail->coop->id == $coop->id;
        });
    }

    /** @test */
    public function change_the_status_to_refunding_when_goal_is_not_achieve_and_coop_has_paid_purchases()
    {
        $coop = Coop::factory()->expired()->create([
            'status' => 'active',
            'goal' => 20
        ]);

        Purchase::factory()->for($coop)->create(['amount' => 10]);

        $coopExpiredJob = new CoopExpiredJob($coop);

        $coopExpiredJob->handle();

        $this->assertDatabaseHas('coops', [
            'id' => $coop->id,
            'status' => 'refunding'
        ]);
    }

    /** @test */
    public function dispatch_a_job_for_each_purchase_when_status_changed_to_refuding()
    {
        Mail::fake();

        Queue::fake();

        $coop = Coop::factory()->expired()->create([
            'status' => 'active',
            'goal' => 2000
        ]);

        $purchase1 = Purchase::factory()->for($coop)->create(['amount' => 10]);

        $purchase2 = Purchase::factory()->for($coop)->create(['amount' => 20]);

        $coopExpiredJob = new CoopExpiredJob($coop);

        $coopExpiredJob->handle();

        Queue::assertPushed(RefundPurchaseJob::class, 2);

        Queue::assertPushed(function (RefundPurchaseJob $job) use ($purchase1) {
            return $job->purchase->id === $purchase1->id;
        });

        Queue::assertPushed(function (RefundPurchaseJob $job) use ($purchase2) {
            return $job->purchase->id === $purchase2->id;
        });
    }

    /** @test */
    public function change_the_status_to_canceled_when_goal_is_not_achieve_and_coop_does_not_have_paid_purchases()
    {
        Mail::fake();

        $coop = Coop::factory()->expired()->create([
            'status' => 'active',
            'goal' => 20
        ]);

        $coopExpiredJob = new CoopExpiredJob($coop);

        $coopExpiredJob->handle();

        $this->assertDatabaseHas('coops', [
            'id' => $coop->id,
            'status' => 'canceled'
        ]);
    }

    /** @test */
    public function owner_should_be_mailed_when_status_changed_to_canceled()
    {
        Mail::fake();

        $coop = Coop::factory()->expired()->create([
            'status' => 'active',
            'goal' => 20
        ]);

        $coopExpiredJob = new CoopExpiredJob($coop);

        $coopExpiredJob->handle();

        Mail::assertSent(function(CoopCanceled $mail) use ($coop){
            return $mail->coop->id == $coop->id;
        });
    }

    /** @test */
    public function coop_without_status_active_should_be_ignored()
    {
        Mail::fake();

        $coop = Coop::factory()->expired()->create(['status' => 'completed']);
        $coopExpiredJob = new CoopExpiredJob($coop);
        $coopExpiredJob->handle();

        $coop = Coop::factory()->expired()->create(['status' => 'refunding']);
        $coopExpiredJob = new CoopExpiredJob($coop);
        $coopExpiredJob->handle();

        $coop = Coop::factory()->expired()->create(['status' => 'canceled']);
        $coopExpiredJob = new CoopExpiredJob($coop);
        $coopExpiredJob->handle();

        Mail::assertNothingSent();
    }
}
