<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait FiltersByActiveParents
{
    protected static function bootFiltersByActiveParents()
    {
        static::addGlobalScope('activeParents', function (Builder $builder) {
            foreach ((new static)->parentRelations() as $relation) {
                $builder->whereHas($relation, fn ($q) => $q->whereNull('deleted_at'));
            }
        });
    }

    /**
     * Each model defines which belongsTo relationships are "parents".
     */
    protected function parentRelations(): array
    {
        return [];
    }
}

// Example d'utilisation dans un model: ex dans Fixing
// use SoftDeletes, FiltersByActiveParents;

// protected function parentRelations(): array
// {
//     return ['fournisseur', 'devise', 'createdBy', 'updatedBy'];
// }


// TO DISABLE THE GLOBAL SCOPE ON THE QUERY, include this method in the model
// public static function withInactiveParents()
// {
//     return static::withoutGlobalScope('activeParents');
// }

// Then use it like this on the query
// $fixings = Fixing::withInactiveParents()->get();
// $deleted = Fixing::withInactiveParents()->onlyTrashed()->get();
