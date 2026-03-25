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
        Schema::table('devotionals', function (Blueprint $table) {
            $table->string('reference_old_testament')->after('day');
            $table->text('content_old_testament')->after('reference_old_testament');
            $table->string('reference_new_testament')->after('content_old_testament');
            $table->text('content_new_testament')->after('reference_new_testament');

            $table->dropColumn(['reference', 'content']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devotionals', function (Blueprint $table) {
            $table->dropColumn(['reference_old_testament', 'content_old_testament', 'reference_new_testament', 'content_new_testament']);

            $table->string('reference');
            $table->text('content');
        });
    }
};
