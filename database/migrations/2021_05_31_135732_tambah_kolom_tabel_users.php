<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TambahKolomTabelUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        schema::table('users', function(Blueprint $table) {
            $table->string('username',20)->after('id')->nullable()->unique();
            $table->string('role',20)->nullable()->after('password');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        schema::table('users', function(Blueprint $table){
            $table->dropColumn('username');
            $table->dropColumn('role');
        }); 
    }
}
