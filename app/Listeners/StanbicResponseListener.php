<?php

namespace App\Listeners;

use Akika\LaravelStanbic\Events\Pain00200103ReportReceived;
use Illuminate\Contracts\Queue\ShouldQueue;

class StanbicResponseListener implements ShouldQueue
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
        $paymentInfo = $event->report->originalPaymentInfoAndStatuses?->toJson() ?? null;
        $reasons = $event->report->getAllStatusReasons()->all();

        $reason = $event->report->originalGroupInfoAndStatus->statusReasonInfos->additionalInfos->first();

        info(__METHOD__, compact('header', 'groupInfo', 'paymentInfo', 'reason'));
    }
}
