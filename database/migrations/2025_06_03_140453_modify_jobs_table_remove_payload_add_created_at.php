<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('jobs', function (Blueprint $table) {
            // These columns were already removed in earlier migration
            // $table->dropColumn('payload');
            // $table->dropColumn('reserved_at');
            // $table->dropColumn('queue');
            // $table->dropColumn('updated_at');
            // timestamps already added in previous migration
        });
    }

    public function down()
    {
        Schema::table('jobs', function (Blueprint $table) {
            // Reverse the changes
            $table->longText('payload');
            $table->dropTimestamps();
        });
    }
};