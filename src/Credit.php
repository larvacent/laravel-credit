<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Credit;

use Larva\Credit\Models\Bonus;
use Larva\Credit\Models\Recharge;
use Larva\Credit\Models\Withdrawal;

/**
 * 积分快捷操作
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Credit
{
    /**
     * 创建积分充值请求
     * @param int $userId
     * @param string $channel
     * @param int $amount
     * @param string $type
     * @param string $clientIP 客户端IP
     * @return Recharge
     */
    public function recharge($userId, $channel, $amount, $type, $clientIP = null)
    {
        return Recharge::create([
            'user_id' => $userId,
            'channel' => $channel,
            'amount' => $amount,
            'type' => $type,
            'client_ip' => $clientIP
        ]);
    }

    /**
     * 获取充值订单
     * @param string $id
     * @return Recharge|null
     */
    public function findRecharge($id)
    {
        return Recharge::where('id', $id)->first();
    }

    /**
     * 送积分 配合其他交易使用，单独使用就是直接送
     * @param int $userId 用户ID
     * @param int $credit 赠送的积分
     * @param string $description
     * @return Bonus
     */
    public function bonus($userId, $credit, $description)
    {
        return Bonus::create(['user_id' => $userId, 'credit' => $credit, 'description' => $description]);
    }

    /**
     * 提现到微信
     * @param User $user
     * @param int $credit
     * @param string $recipient
     * @param array $metaData
     * @return false|Withdrawal
     */
    public function withdrawalByWechat($user, $credit, $recipient, $metaData = [])
    {
        return $this->withdrawal($user, $credit, \Larva\Transaction\Transaction::CHANNEL_WECHAT, $recipient, $metaData);
    }

    /**
     * 提现到支付宝账户
     * @param User $user
     * @param int $credit
     * @param string $account 支付宝账号
     * @param array $metaData
     * @return false|Withdrawal
     */
    public function withdrawalByAlipay($user, $credit, $account, $metaData = [])
    {
        return $this->withdrawal($user, $credit, \Larva\Transaction\Transaction::CHANNEL_ALIPAY, $account, $metaData);
    }

    /**
     * 积分提现
     * @param User $user
     * @param int $credit
     * @param string $channel
     * @param string $recipient 收款账户
     * @param array $metaData 附加信息
     * @param string $clientIP 客户端IP
     * @return Withdrawal|false
     */
    public function withdrawal($user, $credit, $channel, $recipient, $metaData = [], $clientIP = null)
    {
        if ($credit < config('services.credit.withdrawals_mix', 100)) {//提现金额小于最小提现金额不合法
            return false;
        }
        $currentCredit = bcsub($user->credit, $credit);
        if ($currentCredit < 0) {//计算后如果余额小于0，那么结果不合法。
            return false;
        }
        return Withdrawal::create([
            'user_id' => $user->getKey(),
            'credit' => $credit,
            'channel' => $channel,
            'status' => Withdrawal::STATUS_PENDING,
            'recipient' => $recipient,
            'metadata' => $metaData,
            'client_ip' => $clientIP,
        ]);
    }

    /**
     * 创建自己
     * @return static
     */
    public static function make()
    {
        return new static();
    }
}