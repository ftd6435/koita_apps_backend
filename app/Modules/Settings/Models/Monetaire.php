<?php

namespace App\Modules\Settings\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Monetaire extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'monetaires';

    protected $fillable = [
        'nom',
        'prenom',
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
     * Utilisateur ayant créé le monétaire.
     */
    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Utilisateur ayant modifié le monétaire.
     */
    public function modificateur()
    {
        return $this->belongsTo(User::class, 'modify_by');
    }

    // ==============================
    // 🔹 ACCESSORS MODERNES (Laravel 12)
    // ==============================

    /**
     * 🔹 Obtenir le nom complet du monétaire.
     */
    protected function nomComplet(): Attribute
    {
        return Attribute::make(
            get: fn () => ucfirst($this->prenom) . ' ' . strtoupper($this->nom)
        );
    }
}
