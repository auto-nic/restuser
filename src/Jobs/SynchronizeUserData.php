<?php

namespace Autonic\Restuser\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SynchronizeUserData implements ShouldQueue
{

    use Queueable;

    /**
     * Create a new job instance.
     */
    public $customerId;
    public function __construct($customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        debug_logging('');
        debug_logging('synchronizing user data for customer: ' . $this->customerId);

        \App\Models\User::synchronizeUsers($this->customerId);

        debug_logging('finished synchronizing user data for customer: ' . $this->customerId);
        debug_logging('');

    }

}
