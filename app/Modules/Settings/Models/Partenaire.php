<?php

namespace App\Modules\Settings\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Partenaire extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'partenaires';

    protected $fillable = [
        'nom',
        'prenom',
        'raison_sociale',
        'telephone',
        'adresse',
        'email',
        'created_by',
        'modify_by',
    ];

    // ==============================
    // 🔹 RELATIONS
    // ==============================

    /**
     * Utilisateur ayant créé le partenaire.
     */
    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Utilisateur ayant modifié le partenaire.
     */
    public function modificateur()
    {
        return $this->belongsTo(User::class, 'modify_by');
    }

    // ==============================
    // 🔹 ACCESSORS MODERNES (Laravel 12)
    // ==============================

    /**
     * 🔹 Obtenir le nom complet du partenaire.
     */
    protected function nomComplet(): Attribute
    {
        return Attribute::make(
            get: fn () => ucfirst($this->prenom) . ' ' . strtoupper($this->nom)
        );
    }
}
