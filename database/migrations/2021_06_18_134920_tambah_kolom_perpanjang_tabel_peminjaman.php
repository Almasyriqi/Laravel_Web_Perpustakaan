<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TambahKolomPerpanjangTabelPeminjaman extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        schema::table('peminjaman', function(Blueprint $table) {
            $table->tinyInteger('perpanjang')->after('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        schema::table('peminjaman', function(Blueprint $table){
            $table->dropColumn('perpanjang');
        }); 
    }
}
