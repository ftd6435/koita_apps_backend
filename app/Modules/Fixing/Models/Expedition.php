<?php

namespace App\Modules\Fixing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Fondation\Models\Fondation;
use App\Modules\Fixing\Models\InitLivraison;
use App\Modules\Administration\Models\User;

class Expedition extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'expeditions';

    protected $fillable = [
        'id_barre_fondu',
        'id_init_livraison',
        'created_by',
        'modify_by',
    ];

    // ==============================
    // 🔹 RELATIONS
    // ==============================

    /**
     * Fondation (barre fondue) liée à cette expédition
     */
    public function fondation()
{
    return $this->belongsTo(Fondation::class, 'id_barre_fondu');
}

    /**
     * Initialisation de livraison liée à cette expédition
     */
    public function initLivraison()
    {
        return $this->belongsTo(InitLivraison::class, 'id_init_livraison');
    }

    /**
     * Utilisateur ayant créé l'expédition
     */
    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Utilisateur ayant modifié l'expédition
     */
    public function modificateur()
    {
        return $this->belongsTo(User::class, 'modify_by');
    }
}
