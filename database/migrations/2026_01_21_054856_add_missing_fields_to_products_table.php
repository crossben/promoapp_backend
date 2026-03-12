<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingFieldsToProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Rendre les champs existants nullable
            $table->string('name')->nullable()->change();
            $table->decimal('original_price', 8, 2)->nullable()->change();
            $table->decimal('promo_price', 8, 2)->nullable()->change();
            
            // Ajouter le champ unit s'il n'existe pas
            if (!Schema::hasColumn('products', 'unit')) {
                $table->string('unit')->nullable()->default('unité');
            }
            
            // Ajouter promo_end s'il n'existe pas
            if (!Schema::hasColumn('products', 'promo_end')) {
                $table->timestamp('promo_end')->nullable();
            }
            
            // Ajouter promo_start s'il n'existe pas
            if (!Schema::hasColumn('products', 'promo_start')) {
                $table->timestamp('promo_start')->nullable();
            }
            
            // Ajouter description s'il n'existe pas
            if (!Schema::hasColumn('products', 'description')) {
                $table->text('description')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            // Inverser les changements si nécessaire
            $table->string('name')->nullable(false)->change();
            $table->decimal('original_price', 8, 2)->nullable(false)->change();
            $table->decimal('promo_price', 8, 2)->nullable(false)->change();
            
            // Ne pas supprimer les colonnes dans la méthode down pour sécurité
        });
    }
}