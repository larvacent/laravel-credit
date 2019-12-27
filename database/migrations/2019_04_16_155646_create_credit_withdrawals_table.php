<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditWithdrawalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_withdrawals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('credit')->default(0);
            $table->string('status', 10)->default('created');
            $table->string('channel', 30);
            $table->string('recipient');
            $table->ipAddress('client_ip')->nullable();//发起支付请求客户端的 IP 地址
            $table->text('metadata')->nullable();
            $table->timestamps();
            $table->timestamp('canceled_at', 0)->nullable();//成功时间
            $table->timestamp('succeeded_at', 0)->nullable();//成功时间

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credit_withdrawals');
    }
}
