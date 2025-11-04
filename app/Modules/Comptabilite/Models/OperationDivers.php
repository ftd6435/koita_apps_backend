<?php

namespace App\Modules\Comptabilite\Models;

use App\Modules\Settings\Models\Devise;
use App\Modules\Comptabilite\Models\TypeOperation;
use App\Modules\Settings\Models\Divers;
use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperationDivers extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'operations_divers';

    protected $fillable = [
        'id_type_operation',
        'id_divers',
        'id_devise',
        'id_compte',
        'montant',
        'commentaire',
        'reference',
        'date_operation',
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
     * Type d’opération (ex : versement, retrait, transfert…)
     */
    public function typeOperation()
    {
        return $this->belongsTo(TypeOperation::class, 'id_type_operation');
    }

    /**
     * Élément Divers associé à l’opération
     */
    public function divers()
    {
        return $this->belongsTo(Divers::class, 'id_divers');
    }

    /**
     * Devise utilisée dans l’opération
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
