<?php
namespace App\Modules\Comptabilite\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Comptabilite\Models\TypeOperation;
use App\Modules\Settings\Models\Devise;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caisse extends Model
{
    use HasFactory;

    protected $table = 'caisses';

    protected $fillable = [
        'id_type_operation',
        'id_devise',
        'montant',
        'id_compte',
        'commentaire',
        'taux_jour',
        'reference',
        'date_operation',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date_operation' => 'datetime',
    ];
    // 🔹 Relations
    public function devise()
    {
        return $this->belongsTo(Devise::class, 'id_devise');
    }

    public function typeOperation()
    {
        return $this->belongsTo(TypeOperation::class, 'id_type_operation');
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modificateur()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function compte()
    {
        return $this->belongsTo(Compte::class, 'id_compte');
    }
}
