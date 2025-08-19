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
        Schema::create('paints', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->string('product_code');
            $table->string('maker');
            $table->string('cmyk_c'); // Cyan
            $table->string('cmyk_m'); // Magenta
            $table->string('cmyk_y'); // Yellow
            $table->string('cmyk_k'); // Key (Black)
            $table->string('rgb_r'); // Red
            $table->string('rgb_g'); // Green
            $table->string('rgb_b'); // Blue
            $table->string('hex_color', 7); // #FFFFFF format
            $table->string('form'); // spray paint, emulsion, etc.
            $table->integer('volume_ml'); // stored in millilitres
            $table->decimal('price_gbp', 10, 2);
            $table->text('color_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paints');
    }
};
