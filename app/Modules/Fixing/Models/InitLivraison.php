<?php

namespace App\Modules\Fixing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Settings\Models\Client;
use App\Modules\Administration\Models\User;

class InitLivraison extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'init_livraisons';

    protected $fillable = [
        'reference',
        'id_client',
        'commentaire',
        'status',
        'created_by',
        'modify_by',
    ];

    // ==============================
    // 🔹 RELATIONS
    // ==============================

    /**
     * Client concerné par la livraison
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'id_client');
    }

    /**
     * Utilisateur ayant créé la livraison
     */
    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Utilisateur ayant modifié la livraison
     */
    public function modificateur()
    {
        return $this->belongsTo(User::class, 'modify_by');
    }

    /**
     * Expéditions liées à cette livraison
     */
    public function expeditions()
    {
        return $this->hasMany(Expedition::class, 'id_init_livraison');
    }

    // ==============================
    // 🔹 GÉNÉRATION AUTO DE LA RÉFÉRENCE UNIQUE
    // ==============================

    protected static function booted()
    {
        /**
         * Après création, on génère une référence unique à partir de l'ID réel.
         */
        static::created(function ($initLivraison) {
            if (empty($initLivraison->reference)) {
                $reference = 'LIV-' . now()->format('Ymd') . '-' . str_pad($initLivraison->id, 4, '0', STR_PAD_LEFT);
                $initLivraison->updateQuietly(['reference' => $reference]);
            }
        });
    }
}
