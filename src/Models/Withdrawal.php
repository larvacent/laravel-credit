<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Credit\Models;

use Illuminate\Database\Eloquent\Model;
use Larva\Credit\Events\WithdrawalCanceled;
use Larva\Credit\Events\WithdrawalFailure;
use Larva\Credit\Events\WithdrawalSuccess;
use Larva\Transaction\Models\Transfer;

/**
 * 积分提现
 *
 * @property int $user_id
 * @property int $credit
 * @property string $status
 * @property string $channel
 * @property string $recipient
 * @property array $metadata
 * @property-read array $extra
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $canceled_at
 * @property \Illuminate\Support\Carbon|null $succeeded_at
 *
 * @property User $user
 * @property Transaction $transaction
 * @property Transfer $transfer
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Withdrawal extends Model
{
    /**
     * 与模型关联的数据表。
     *
     * @var string
     */
    protected $table = 'credit_withdrawals';

    const STATUS_PENDING = 'pending';//处理中： pending
    const STATUS_SUCCEEDED = 'succeeded';//完成： succeeded
    const STATUS_FAILED = 'failed';//失败： failed
    const STATUS_CANCELED = 'canceled';//取消： canceled

    /**
     * 可以批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'credit', 'status', 'channel', 'recipient', 'metadata', 'canceled_at', 'succeeded_at'
    ];

    /**
     * 应该被调整为日期的属性
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'canceled_at',
        'succeeded_at'
    ];

    /**
     * 属性类型转换
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
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
     * 获取提现附加参数
     * @return array
     */
    public function getExtraAttribute()
    {
        return [
            //微信
            'type' => $this->metadata['type'] ?? '',
            'user_name' => $this->metadata['name'] ?? '',
            //支付宝
            'recipient_name' => $this->metadata['name'] ?? '',
            'recipient_account_type' => $this->metadata['account_type'] ?? ''
        ];
    }

    /**
     * 设置提现成功
     */
    public function setSucceeded()
    {
        $this->update(['status' => static::STATUS_SUCCEEDED, 'succeeded_at' => $this->freshTimestamp()]);
        event(new WithdrawalSuccess($this));
    }

    /**
     * 取消提现
     * @return bool
     */
    public function setCanceled()
    {
        $this->transaction()->create([
            'user_id' => $this->user_id,
            'type' => Transaction::TYPE_WITHDRAWAL_REVOKED,
            'description' => trans('credit::credit.withdrawal_revoked'),
            'credit' => $this->credit,
            'current_credit' => bcadd($this->user->credit, $this->credit)
        ]);
        $this->update(['status' => static::STATUS_CANCELED, 'canceled_at' => $this->freshTimestamp()]);
        event(new WithdrawalCanceled($this));
        return true;
    }

    /**
     * 提现失败平账
     * @return bool
     */
    public function setFailed()
    {
        $this->transaction()->create([
            'user_id' => $this->user_id,
            'type' => Transaction::TYPE_WITHDRAWAL_FAILED,
            'description' => trans('credit::credit.withdrawal_failed'),
            'credit' => $this->credit,
            'current_credit' => bcadd($this->user->credit, $this->credit)
        ]);
        $this->update(['status' => static::STATUS_FAILED, 'canceled_at' => $this->freshTimestamp()]);
        event(new WithdrawalFailure($this));
        return true;
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

    /**
     * Get the entity's transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\morphOne
     */
    public function transaction()
    {
        return $this->morphOne(Transaction::class, 'source');
    }

    /**
     * Get the entity's transfer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\morphOne
     */
    public function transfer()
    {
        return $this->morphOne(Transfer::class, 'order');
    }
}