<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Credit\Models;

use Illuminate\Database\Eloquent\Model;
use Larva\Credit\Events\RechargeFailure;
use Larva\Credit\Events\RechargeShipped;
use Larva\Credit\Notifications\RechargeSucceeded;
use Larva\Transaction\Models\Charge;

/**
 * 积分充值
 * @property int $id
 * @property int $user_id
 * @property int $amount
 * @property string $channel
 * @property string $type
 * @property string $status
 * @property string $client_ip
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $succeeded_at
 *
 * @property Charge $charge
 * @property User $user
 * @property Transaction $transaction
 * @property Bonus $bonus
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Recharge extends Model
{

    const STATUS_PENDING = 'pending';//处理中： pending
    const STATUS_SUCCEEDED = 'succeeded';//完成： succeeded
    const STATUS_FAILED = 'failed';//失败： failed

    /**
     * 与模型关联的数据表。
     *
     * @var string
     */
    protected $table = 'credit_recharges';

    /**
     * 可以批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'amount', 'channel', 'type', 'status', 'client_ip', 'succeeded_at'
    ];

    /**
     * 应该被调整为日期的属性
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'succeeded_at'
    ];

    /**
     * 模型的默认属性值。
     *
     * @var array
     */
    protected $attributes = [
        'status' => 'pending',

    ];

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

    /**
     * Get the entity's transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function transaction()
    {
        return $this->morphOne(Transaction::class, 'source');
    }

    /**
     * Get the entity's bonus.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function bonus()
    {
        return $this->morphOne(Bonus::class, 'source');
    }

    /**
     * Get the entity's charge.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function charge()
    {
        return $this->morphOne(Charge::class, 'order');
    }

    /**
     * 设置交易成功
     */
    public function setSucceeded()
    {
        $this->update(['channel' => $this->charge->channel, 'type' => $this->charge->type, 'status' => static::STATUS_SUCCEEDED, 'succeeded_at' => $this->freshTimestamp()]);
        $credit = bcdiv($this->amount, config('services.credit.cny_exchange_rate', 10));

        $this->transaction()->create([
            'user_id' => $this->user_id,
            'type' => Transaction::TYPE_RECHARGE,
            'description' => trans('credit::credit.credit_recharge'),
            'credit' => $credit,
            'current_credit' => bcadd($this->user->credit, $credit)
        ]);

        if ($credit >= config('services.credit.recharge_gift_mix', 1000000)) {//赠送
            $gift = bcmul(config('services.credit.recharge_gift', 0), $credit);
            if ($gift > 0) {
                $this->bonus()->create(['user_id' => $this->user_id, 'credit' => $gift, 'description' => trans('credit::credit.recharge_gift')]);
            }
        }
        event(new RechargeShipped($this));
        $this->user->notify(new RechargeSucceeded($this->user, $this));
    }

    /**
     * 设置交易失败
     */
    public function setPayFailure()
    {
        $this->update(['status' => static::STATUS_FAILED]);
        event(new RechargeFailure($this));
    }
}