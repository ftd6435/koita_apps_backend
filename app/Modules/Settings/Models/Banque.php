<?php

namespace App\Modules\Settings\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Banque extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'banques';

    protected $fillable = [
        'nom_banque',
        'code_banque',
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
     * Utilisateur ayant créé la banque.
     */
    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Utilisateur ayant modifié la banque.
     */
    public function modificateur()
    {
        return $this->belongsTo(User::class, 'modify_by');
    }

    // ==============================
    // 🔹 ACCESSORS MODERNES (Laravel 12)
    // ==============================

    /**
     * 🔹 Retourne le nom formaté de la banque (première lettre en majuscule).
     */
    protected function nomComplet(): Attribute
    {
        return Attribute::make(
            get: fn () => ucfirst($this->nom_banque)
        );
    }
}
