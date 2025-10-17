<?php

namespace App\Modules\Fixing\Models;

use App\Modules\Administration\Models\Fournisseur;
use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Devise;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fixing extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'fournisseur_id',
        'poids_pro',
        'carrat_moyenne',
        'discount',
        'bourse',
        'unit_price',
        'devise_id',
        'status',
        'created_by',
        'updated_by',
    ];

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class)->whereNull('fournisseurs.deleted_at');
    }

    public function devise(): BelongsTo
    {
        return $this->belongsTo(Devise::class)->whereNull('devises.deleted_at');
    }

    public function fixingBarres() : HasMany
    {
        return $this->hasMany(FixingBarre::class)->whereNull('fixing_barre.deleted_at');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
