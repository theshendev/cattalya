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
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedTinyInteger('karat')->default(18)->after('description');
            $table->string('gold_color')->nullable()->after('karat');
            $table->string('shine')->nullable()->after('gold_color');
            $table->string('dimensions')->nullable()->after('shine');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['karat', 'gold_color', 'shine', 'dimensions']);
        });
    }
};
