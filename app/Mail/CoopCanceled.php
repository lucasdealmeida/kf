<?php

namespace App\Mail;

use App\Models\Coop;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CoopCanceled extends Mailable
{
    use Queueable, SerializesModels;

    public $coop;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Coop $coop)
    {
        $this->coop = $coop;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('view.name');
    }
}
