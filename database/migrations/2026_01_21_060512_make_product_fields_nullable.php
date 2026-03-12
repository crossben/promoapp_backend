<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeProductFieldsNullable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Rendre tous les champs nullable sauf les vraiment obligatoires
            $table->string('name')->nullable()->change();
            $table->text('description')->nullable()->change();
            $table->decimal('original_price', 10, 2)->nullable()->change();
            $table->decimal('promo_price', 10, 2)->nullable()->change();
            $table->string('unit')->nullable()->change();
            $table->dateTime('promo_start')->nullable()->change();
            $table->dateTime('promo_end')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->text('description')->nullable(false)->change();
            $table->decimal('original_price', 10, 2)->nullable(false)->change();
            $table->decimal('promo_price', 10, 2)->nullable(false)->change();
            $table->string('unit')->nullable(false)->change();
            $table->dateTime('promo_start')->nullable(false)->change();
            $table->dateTime('promo_end')->nullable(false)->change();
        });
    }
}