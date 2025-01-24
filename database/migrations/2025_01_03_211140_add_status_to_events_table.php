<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('description');
            $table->text('rejection_reason')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('rejection_reason');
        });
    }
};
