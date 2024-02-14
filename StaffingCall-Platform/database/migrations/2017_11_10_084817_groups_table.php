<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staffing_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('groupCode',50)->unique();
            $table->string('groupName',100)->unique();
            $table->integer('maximumUnits')->length(11)->default(0)->comment('0=>Unlimited');
            $table->integer('maximumEmployee')->length(11)->default(0)->comment('0=>Unlimited');
            $table->string('logo',100)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('deleteStatus')->default(1);
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
