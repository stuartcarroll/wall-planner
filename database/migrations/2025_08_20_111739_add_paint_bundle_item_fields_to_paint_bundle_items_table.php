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
        Schema::table('paint_bundle_items', function (Blueprint $table) {
            $table->unsignedBigInteger('paint_bundle_id')->after('id');
            $table->integer('paint_id')->after('paint_bundle_id');
            $table->integer('quantity')->after('paint_id');
            $table->decimal('price_per_unit', 10, 2)->after('quantity');
            $table->decimal('subtotal', 10, 2)->nullable()->after('price_per_unit');
            $table->integer('volume_ml')->nullable()->after('subtotal');
            $table->text('notes')->nullable()->after('volume_ml');
            
            $table->foreign('paint_bundle_id')->references('id')->on('paint_bundles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paint_bundle_items', function (Blueprint $table) {
            $table->dropForeign(['paint_bundle_id']);
            $table->dropColumn(['paint_bundle_id', 'paint_id', 'quantity', 'price_per_unit', 'subtotal', 'volume_ml', 'notes']);
        });
    }
};
