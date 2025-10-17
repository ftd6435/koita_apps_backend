<?php

namespace App\Modules\Settings\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Fixing\Models\InitLivraison;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'clients';

    protected $fillable = [
        'nom_complet',
        'raison_sociale',
        'pays',
        'ville',
        'adresse',
        'telephone',
        'email',
        'created_by',
        'modify_by',
    ];

    // ==============================
    // 🔹 RELATIONS
    // ==============================

    /**
     * Utilisateur ayant créé le client
     */
    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Utilisateur ayant modifié le client
     */
    public function modificateur()
    {
        return $this->belongsTo(User::class, 'modify_by');
    }
    public function initLivraisons()
{
    return $this->hasMany(InitLivraison::class, 'id_client');
}


    // ==============================
    // 🔹 ACCESSORS (si besoin)
    // ==============================

    /**
     * 🔹 Retourne le nom affichable du client :
     * - S’il a une raison sociale (entreprise), on la montre
     * - Sinon, on montre le nom complet
     */
    public function getNomAffichageAttribute(): string
    {
        return $this->raison_sociale 
            ? strtoupper($this->raison_sociale)
            : ucfirst($this->nom_complet);
    }
}
