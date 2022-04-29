<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('User reference');
            $table->unsignedBigInteger('number');
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->comment('User Id created');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('User Id last update');
            $table->unsignedBigInteger('deleted_by')->nullable()->comment('User ID deleted logical register');
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('accounts');
    }
}
