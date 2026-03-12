<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakePriceFieldsNullableInProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('original_price', 8, 2)->nullable()->change();
            $table->decimal('promo_price', 8, 2)->nullable()->change();
            $table->string('name')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('original_price', 8, 2)->nullable(false)->change();
            $table->decimal('promo_price', 8, 2)->nullable(false)->change();
            $table->string('name')->nullable(false)->change();
        });
    }
}