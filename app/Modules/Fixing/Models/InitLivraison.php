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
        'statut',
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
}
