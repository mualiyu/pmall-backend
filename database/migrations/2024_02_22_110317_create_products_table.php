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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string("store_id");
            $table->unsignedBigInteger("category_id");
            $table->unsignedBigInteger("sub_category_id")->nullable();
            $table->unsignedBigInteger("brand_id");
            $table->string("name");
            $table->string("image");
            $table->longText("description")->nullable();
            $table->integer("cost_price");
            $table->integer("selling_price");
            $table->integer("inStock");
            $table->integer("quantity");
            $table->string("tags")->nullable();
            $table->string("status");
            $table->longText("more_images")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
