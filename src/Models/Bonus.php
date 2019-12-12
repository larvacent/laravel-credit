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
 * 积分赠送模型
 * @property int $id
 * @property boolean $paid
 * @property int $user_id
 * @property int $credit
 * @property Model $source
 * @property string $description
 * @property int $transaction_id
 * @property array $metadata
 * @property \Illuminate\Support\Carbon $created_at
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Bonus extends Model
{
    /**
     * 与模型关联的数据表。
     *
     * @var string
     */
    protected $table = 'credit_bonus';

    /**
     * 可以批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'paid', 'user_id', 'credit', 'source', 'description', 'transaction_id', 'metadata'
    ];

    /**
     * 应该被调整为日期的属性
     *
     * @var array
     */
    protected $dates = [
        'created_at',
    ];

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = null;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->paid = false;
        });
        static::created(function ($model) {
            $model->transaction()->create([
                'user_id' => $this->user_id,
                'type' => Transaction::TYPE_RECEIPTS_EXTRA,
                'description' => trans('credit::credit.receipts_extra'),
                'credit' => $model->credit,
                'current_credit' => bcadd($model->user->credit, $model->credit)
            ]);
        });
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
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function transaction()
    {
        return $this->morphOne(Transaction::class, 'source');
    }

    /**
     * Get the source entity that the Transaction belongs to.
     */
    public function source()
    {
        return $this->morphTo();
    }
}