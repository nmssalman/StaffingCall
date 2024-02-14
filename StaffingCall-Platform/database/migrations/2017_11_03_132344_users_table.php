<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staffing_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('userID',55);
            $table->string('userName', 55);
            $table->string('firstName',50);
            $table->string('lastName',50);
            $table->string('email',100)->unique();
            $table->string('password',500);
            $table->string('phone',16)->nullable();
            $table->longText('address')->nullable();
            $table->string('profilePic',100)->nullable();
            $table->longText('skills')->nullable();
            $table->integer('businessGroupID')->length(11)->default(0);
            $table->tinyInteger('role')->default(0)->comment('1=>GodAdmin,2=>GroupManager,3=>SuperAdmin,4=>Admin,0=>EndUser');
            $table->tinyInteger('active')->default(1);
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('deleteStatus')->default(0);
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
