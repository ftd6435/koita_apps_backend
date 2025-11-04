<?php
namespace App\Modules\Comptabilite\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Comptabilite\Models\TypeOperation;
use App\Modules\Settings\Models\Client;
use App\Modules\Settings\Models\Devise;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperationClient extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'operations_clients';

    protected $fillable = [
        'id_client',
        'reference',
        'date_operation',
        'id_compte',
        'id_type_operation',
        'id_devise',
        'montant',
        'commentaire',
        'created_by',
        'updated_by',
    ];


     protected $casts = [
        'date_operation' => 'datetime',
    ];
    // ==============================
    // 🔹 RELATIONS
    // ==============================

    /**
     * Client concerné par l’opération
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'id_client');
    }

    /**
     * Type d’opération (achat, paiement, remboursement…)
     */
    public function typeOperation()
    {
        return $this->belongsTo(TypeOperation::class, 'id_type_operation');
    }

    /**
     * Devise utilisée pour l’opération
     */
    public function devise()
    {
        return $this->belongsTo(Devise::class, 'id_devise');
    }

    /**
     * Utilisateur ayant créé l’opération
     */
    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Utilisateur ayant modifié l’opération
     */
    public function modificateur()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

     public function compte()
    {
        return $this->belongsTo(Compte::class, 'id_compte');
    }
}
