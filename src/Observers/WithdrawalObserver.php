<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Credit\Observers;

use Larva\Credit\Models\Transaction;
use Larva\Credit\Models\Withdrawal;

/**
 * Class Withdrawal
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class WithdrawalObserver
{
    /**
     * Handle the user "created" event.
     *
     * @param Withdrawal $withdrawal
     * @return void
     */
    public function created(Withdrawal $withdrawal)
    {
        $credit = -$withdrawal->credit;
        $withdrawal->transaction()->create([
            'user_id' => $withdrawal->user_id,
            'type' => Transaction::TYPE_WITHDRAWAL,
            'description' => trans('credit::credit.withdrawal'),
            'credit' => $credit,
            'current_credit' => bcadd($withdrawal->user->credit, $credit),
            'client_ip' => $withdrawal->client_ip,
        ]);

        //根据汇率计算可得到现金额 单位元
        $amount = bcdiv($withdrawal->credit, config('services.credit.withdrawals_cny_exchange_rate', 10), 2);

        $withdrawal->transfer()->create([
            'amount' => bcmul($amount, 100),
            'currency' => 'CNY',
            'description' => trans('credit::credit.withdrawal'),
            'channel' => $withdrawal->channel,
            'metadata' => $withdrawal->metadata,
            'recipient_id' => $withdrawal->recipient,
            'extra' => $withdrawal->extra
        ]);
    }
}