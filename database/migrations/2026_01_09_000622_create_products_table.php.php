// database/migrations/create_products_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image');
            $table->decimal('original_price', 10, 2);
            $table->decimal('promo_price', 10, 2);
            $table->decimal('quantity', 10, 2); // kg or units
            $table->string('unit'); // kg, unit, liter, etc.
            $table->dateTime('promo_start');
            $table->dateTime('promo_end');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }
}