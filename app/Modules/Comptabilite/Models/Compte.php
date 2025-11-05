<?php

namespace App\Modules\Comptabilite\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Devise;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Compte extends Model
{
    protected $fillable = [
        'banque_id',
        'devise_id',
        'solde_initial',
        'numero_compte',
        'created_by',
        'updated_by'
    ];

    public function banque(): BelongsTo
    {
        return $this->belongsTo(Banque::class);
    }

    public function devise(): BelongsTo
    {
        return $this->belongsTo(Devise::class);
    }

    public function createdBy() : BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy() : BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
