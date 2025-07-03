<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('itian_registration_requests', function (Blueprint $table) {
            //
            // This migration is redundant since we already have request_id as primary key
            // The next migration will rename request_id to id
            // $table->id();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('itian_registration_requests', function (Blueprint $table) {
            //
            // This migration is redundant since we already have request_id as primary key
            // $table->dropColumn('id');
        });
    }
};
