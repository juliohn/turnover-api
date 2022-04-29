<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class InsertDefaultUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $password = bcrypt('123456');

        DB::table('users')->insert([
            'name' => 'System',
            'email' => 'system@system.com',
            'role' => 'A',
            'password'=> $password,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => 1,
            'updated_by' => 1,

        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        User::truncate();
        User::query()->delete();
    }
}
