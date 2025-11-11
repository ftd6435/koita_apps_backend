<?php
namespace App\Modules\Fixing\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Client;
use App\Modules\Settings\Models\Devise;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FixingClient extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fixing_clients';

    protected $fillable = [
        'id_client',
        'id_devise',
        'reference',
        'poids_pro',
        'carrat_moyen',
        'discompte',
        'bourse',
        'prix_unitaire',
        'status',
        'created_by',
        'updated_by',
    ];

    // ===============================
    // 🔹 RELATIONS
    // ===============================

    public function client()
    {
        return $this->belongsTo(Client::class, 'id_client');
    }

    public function devise()
    {
        return $this->belongsTo(Devise::class, 'id_devise');
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modificateur()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ===============================
    // 🔹 SCOPES
    // ===============================

    public function scopeVendus($query)
    {
        return $query->where('status', 'vendu');
    }

    public function scopeProvisoires($query)
    {
        return $query->where('status', 'provisoire');
    }

    // ===============================
    // 🔹 LOGIQUE AUTOMATIQUE : Référence + Statut
    // ===============================

    protected static function booted()
    {
        // 🔸 Génération automatique de la référence à la création
        static::creating(function ($fixing) {
            $lastId            = self::withTrashed()->max('id') ?? 0;
            $fixing->reference = 'FIX-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);

            // 🔹 Déterminer le statut automatiquement
            if (is_null($fixing->prix_unitaire) || is_null($fixing->discompte)) {
                $fixing->status = 'provisoire';
            } else {
                $fixing->status = 'vendu';
            }
        });

        // 🔸 Mise à jour du statut lors des modifications
        static::saving(function ($fixing) {
            if (is_null($fixing->prix_unitaire) || is_null($fixing->discompte)) {
                $fixing->status = 'provisoire';
            } else {
                $fixing->status = 'vendu';
            }
        });
    }
}
