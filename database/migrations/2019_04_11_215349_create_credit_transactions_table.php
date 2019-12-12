<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id')->index();//用户ID
            $table->integer('credit');//积分数
            $table->unsignedInteger('current_credit');//该笔交易发生后，用户的积分数
            $table->string('description');//描述
            $table->morphs('source');//关联对象
            $table->string('type')->comment('交易类型');
            $table->ipAddress('client_ip')->nullable();//发起支付请求客户端的 IP 地址
            $table->timestamp('created_at', 0)->nullable();//创建时间
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credit_transactions');
    }
}
