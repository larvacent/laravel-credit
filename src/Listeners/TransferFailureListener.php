<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Credit\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Larva\Credit\Models\Withdrawal;
use Larva\Transaction\Events\TransferFailure;

/**
 * Class TransferFailureListener
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class TransferFailureListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param TransferFailure $event
     * @return void
     */
    public function handle(TransferFailure $event)
    {
        if ($event->transfer->order instanceof Withdrawal) {
            $event->transfer->order->setFailed();
        }
    }
}