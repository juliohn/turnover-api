<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->comment('Account reference');
            $table->string('description')->comment('description balance');
            $table->float('amount', 8, 2)->coment('amount enter');
            $table->char('type', 1)->comment('C (check) or E (Expense)');
            $table->char('status', 1)->comment('A accepted, P - Pending, R - Reject');
            $table->dateTime('date');
            $table->string('image_path')->nullable()->comment('path of the check');
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->comment('User Id created');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('User Id last update');
            $table->unsignedBigInteger('deleted_by')->nullable()->comment('User ID deleted logical register');
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('balances');
    }
}
