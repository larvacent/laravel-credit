<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Credit\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 积分交易
 *
 * @property string $id
 * @property int $user_id
 * @property int $credit
 * @property int $current_credit
 * @property string $description
 * @property string $type
 * @property string $client_ip
 * @property \Illuminate\Support\Carbon $created_at
 *
 * @property User $user
 * @property Bonus|Recharge|Withdrawal $source
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Transaction extends Model
{
    /**
     * 与模型关联的数据表。
     *
     * @var string
     */
    protected $table = 'credit_transactions';

    /**
     * 可以批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'credit', 'current_credit', 'description', 'source', 'type','client_ip'
    ];

    /**
     * 应该被调整为日期的属性
     *
     * @var array
     */
    protected $dates = [
        'created_at'
    ];

    const UPDATED_AT = null;

    const TYPE_RECHARGE = 'recharge';//充值
    const TYPE_RECHARGE_REFUND = 'recharge_refund';//充值退款
    const TYPE_RECHARGE_REFUND_FAILED = 'recharge_refund_failed';//充值退款失败
    const TYPE_WITHDRAWAL = 'withdrawal';//提现申请
    const TYPE__WITHDRAWAL_FAILED = 'withdrawal_failed';//提现失败
    const TYPE__WITHDRAWAL_REVOKED = 'withdrawal_revoked';//提现撤销
    const TYPE_PAYMENT = 'payment';//支付/收款
    const TYPE_PAYMENT_REFUND = 'payment_refund';//退款/收到退款
    const TYPE_TRANSFER = 'transfer';//转账/收到转账
    const TYPE_RECEIPTS_EXTRA = 'receipts_extra';//赠送
    const TYPE_ROYALTY = 'royalty';//分润/收到分润
    const TYPE_REWARD = 'reward';//奖励/收到奖励

    /**
     * Get the source entity that the Transaction belongs to.
     */
    public function source()
    {
        return $this->morphTo();
    }

    /**
     * Get the user that the charge belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(
            config('auth.providers.' . config('auth.guards.api.provider') . '.model')
        );
    }

}