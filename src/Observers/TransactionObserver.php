<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Credit\Observers;

use Larva\Credit\Models\Transaction;

/**
 * Class Transaction
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class TransactionObserver
{
    /**
     * Handle the user "created" event.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function created(Transaction $transaction)
    {
        $transaction->user->update(['credit' => $transaction->current_credit]);//更新用户积分
    }
}