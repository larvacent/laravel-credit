<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Credit;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Class CreditServiceProvider
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class CreditServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'credit-migrations');

            $this->publishes([
                __DIR__ . '/../resources/lang' => resource_path('lang/vendor/credit'),
            ], 'credit-resources');
        }

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'credit');
        //$this->loadViewsFrom(__DIR__.'/../resources/views', 'credit');

        // Transaction
        Event::listen(\Larva\Transaction\Events\ChargeClosed::class, \Larva\Credit\Listeners\ChargeClosedListener::class);//支付关闭
        Event::listen(\Larva\Transaction\Events\ChargeFailure::class, \Larva\Credit\Listeners\ChargeFailureListener::class);//支付失败
        Event::listen(\Larva\Transaction\Events\ChargeShipped::class, \Larva\Credit\Listeners\ChargeShippedListener::class);//支付成功

        Event::listen(\Larva\Transaction\Events\TransferFailure::class, \Larva\Credit\Listeners\TransferFailureListener::class);//提现失败
        Event::listen(\Larva\Transaction\Events\TransferShipped::class, \Larva\Credit\Listeners\TransferShippedListener::class);//提现成功

        // Observers
        \Larva\Credit\Models\Recharge::observe(\Larva\Credit\Observers\RechargeObserver::class);
        \Larva\Credit\Models\Transaction::observe(\Larva\Credit\Observers\TransactionObserver::class);
        \Larva\Credit\Models\Withdrawal::observe(\Larva\Credit\Observers\WithdrawalObserver::class);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

    }

}