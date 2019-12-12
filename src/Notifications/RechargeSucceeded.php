<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Credit\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use Larva\Credit\Models\Recharge;

/**
 * 充值成功通知
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class RechargeSucceeded extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The user.
     *
     * @var User
     */
    public $user;

    /**
     * @var Recharge
     */
    public $recharge;

    /**
     * Create a new notification instance.
     *
     * @param $user
     * @param Recharge $recharge
     */
    public function __construct($user,Recharge $recharge)
    {
        $this->user= $user;
        $this->recharge = $recharge;
    }

    /**
     * Get the notification's channels.
     *
     * @param mixed $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(Lang::get('Credit recharge succeeded'))
            ->line(Lang::get('Your recharge credit is :credit', ['credit' => $this->recharge->transaction->credit]))
            ->line(Lang::get('Thank you for choosing, we will be happy to help you in the process of your subsequent use of the service.'));
    }
}