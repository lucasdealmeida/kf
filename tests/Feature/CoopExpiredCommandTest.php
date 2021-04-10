<?php

namespace Tests\Feature;

use App\Jobs\CoopExpiredJob;
use App\Models\Coop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CoopExpiredCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function create_a_job_for_each_coop_expired()
    {
        Queue::fake();

        $coop1 = Coop::factory()->expired()->create();

        $coop2 = Coop::factory()->expired()->create();

        Coop::factory()->count(10)->create();

        $this->artisan('coop-expired');

        Queue::assertPushed(CoopExpiredJob::class, 2);

        Queue::assertPushed(function(CoopExpiredJob $job) use ($coop1){
            return $job->coop->id === $coop1->id;
        });

        Queue::assertPushed(function(CoopExpiredJob $job) use ($coop2){
            return $job->coop->id === $coop2->id;
        });
    }

    /** @test */
    public function only_coop_with_status_active_should_have_a_job_pushed()
    {
        Queue::fake();

        $coop = Coop::factory()->expired()->create(['status' => 'active']);

        Coop::factory()->expired()->create(['status' => 'canceled']);

        Coop::factory()->expired()->create(['status' => 'refunding']);

        Coop::factory()->expired()->create(['status' => 'draft']);

        $this->artisan('coop-expired');

        Queue::assertPushed(CoopExpiredJob::class, 1);

        Queue::assertPushed(function(CoopExpiredJob $job) use ($coop){
            return $job->coop->id === $coop->id;
        });
    }
}
