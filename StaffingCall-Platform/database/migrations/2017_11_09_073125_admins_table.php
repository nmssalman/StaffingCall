<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staffing_admins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('userName',55);
            $table->string('firstName',50);
            $table->string('lastName',50);
            $table->string('email',100)->unique();
            $table->string('password',500);
            $table->string('phone',16)->nullable();
            $table->longText('address')->nullable();
            $table->string('profilePic',100)->nullable();
            $table->tinyInteger('role')->default(0)->comment('1=>GodAdmin');
            $table->tinyInteger('status')->default(1);
            $table->string('token',100)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
