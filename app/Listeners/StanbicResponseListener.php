<?php

namespace App\Listeners;

use Akika\LaravelStanbic\Events\Pain00200103ReportReceived;

class StanbicResponseListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Pain00200103ReportReceived $event): void
    {
        $header = $event->report->groupHeader;
        $groupInfo = $event->report->originalGroupInfoAndStatus;
        $paymentInfo = $event->report->originalPaymentInfoAndStatus;
        $reasons = $event->report->getAllStatusReasons()->all();

        info(__METHOD__, compact('header', 'groupInfo', 'paymentInfo', 'reasons'));
    }
}
