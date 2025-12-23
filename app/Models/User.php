<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens; // <--- Ligne commentée ou supprimée car le package manque

class User extends Authenticatable
{
    // On retire HasApiTokens de la liste des traits utilisés
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // =========================================================
    // CONFIGURATION ADMINLTE
    // =========================================================

    /**
     * Image de profil (Menu haut droit).
     * Utilise un service gratuit (ui-avatars) pour générer une image avec les initiales.
     */
    public function adminlte_image()
    {
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Description sous le nom (ex: Rôle).
     */
    public function adminlte_desc()
    {
        return 'Administrateur'; // Vous pourrez mettre $this->role plus tard
    }

    /**
     * Lien vers le profil utilisateur.
     */
    public function adminlte_profile_url()
    {
        // Retourne vers la page paramètres pour l'instant, ou une route 'profile' si vous en avez une
        return 'admin/settings'; 
    }
}