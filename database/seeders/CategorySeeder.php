<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Démarrage du seeder des catégories...');
        
        // Vérifier si la table categories existe
        if (!DB::getSchemaBuilder()->hasTable('categories')) {
            $this->command->error('La table "categories" n\'existe pas !');
            $this->command->info('Exécutez d\'abord la migration : php artisan migrate');
            return;
        }
        
        // Vérifier si la table stores existe
        if (!DB::getSchemaBuilder()->hasTable('stores')) {
            $this->command->error('La table "stores" n\'existe pas !');
            return;
        }
        
        // Vérifier si une boutique existe
        $storeId = DB::table('stores')->value('id');
        
        if (!$storeId) {
            $this->command->warn('Création d\'une boutique par défaut...');
            
            $storeId = DB::table('stores')->insertGetId([
                'name' => 'Super Épicerie',
                'slug' => 'super-epicerie',
                'description' => 'Votre épicerie de quartier',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        // Liste des catégories (sans order_column)
        $categories = [
            ['Crémerie', 'fas fa-cheese'],
            ['Fromagerie', 'fas fa-cheese-wheel'],
            ['Charcuterie', 'fas fa-bacon'],
            ['Poissonnerie', 'fas fa-fish'],
            ['Sandwich', 'fas fa-bread-slice'],
            ['Plats Cuisinés', 'fas fa-utensils'],
            ['Boucherie', 'fas fa-drumstick-bite'],
            ['Volaille', 'fas fa-drumstick'],
            ['Épicerie Sèche', 'fas fa-box'],
        ];
        
        $this->command->info('Insertion des catégories...');
        
        foreach ($categories as $category) {
            // Vérifier si la catégorie existe déjà
            $exists = DB::table('categories')
                ->where('name', $category[0])
                ->where('store_id', $storeId)
                ->exists();
            
            if (!$exists) {
                DB::table('categories')->insert([
                    'name' => $category[0],
                    'icon' => $category[1],
                    'store_id' => $storeId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $this->command->info("✓ {$category[0]} ajoutée");
            } else {
                $this->command->info("➤ {$category[0]} existe déjà");
            }
        }
        
        $this->command->info('✅ Seeder des catégories terminé !');
    }
}