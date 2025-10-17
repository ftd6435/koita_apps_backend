<?php
namespace App\Modules\Fondation\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Fixing\Models\Expedition;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fondation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fondations';

    protected $fillable = [
        'ids_barres',
        'id_init_fondation',
        'statut',
        'poids_fondu',
        'carrat_fondu',
        'poids_dubai',
        'carrat_dubai',
        'is_fixed',
        'created_by',
        'modify_by',
    ];

    // ==============================
    // 🔹 RELATIONS
    // ==============================

    /**
     * Utilisateur ayant créé la fondation.
     */
    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
/**
 * Relation : la fondation appartient à une initialisation de fondation.
 */
    public function initFondation()
    {
        return $this->belongsTo(InitFondation::class, 'id_init_fondation');
    }

    /**
     * Utilisateur ayant modifié la fondation.
     */
    public function modificateur()
    {
        return $this->belongsTo(User::class, 'modify_by');
    }

    // ==============================
    // 🔹 ACCESSORS MODERNES (Laravel 12)
    // ==============================

    /**
     * Convertir la chaîne d'IDs des barres ("3,4,5") en tableau [3,4,5].
     */
    protected function idsBarres(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? array_map('intval', explode(',', $value)) : [],
            set: fn($value) => is_array($value) ? implode(',', $value) : $value
        );
    }
    public function expedition()
    {
        return $this->hasOne(Expedition::class, 'id_barre_fondu');
    }

    /**
     * Retourne un texte résumé de la fondation.
     */
    protected function resume(): Attribute
    {
        return Attribute::make(
            get: fn() => sprintf(
                'Poids: %.2f g | Carrat moyen: %.2f | Fixée: %s',
                $this->poids_fondu,
                $this->carrat_moyen,
                $this->is_fixed ? 'Oui' : 'Non'
            )
        );
    }
}
