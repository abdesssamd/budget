<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Créer un compte Administrateur spécifique (pour vous connecter)
        // Vérifie si l'email existe déjà pour éviter les erreurs de doublons
        $admin = User::where('email', 'admin@example.com')->first();

        if (!$admin) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'), // Changez le mot de passe ici si vous voulez
                'remember_token' => null,
            ]);
        }

        // 2. (Optionnel) Créer 10 utilisateurs fictifs aléatoires
        // User::factory(10)->create();
    }
}