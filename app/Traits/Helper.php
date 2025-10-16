<?php


namespace App\Traits;

use App\Modules\Purchase\Models\Barre;

trait Helper
{
    public function carratMoyenne($achat_id)
    {
        $barres = Barre::where('achat_id', $achat_id)->get();

        $multplication = 0;

        foreach($barres as $barre){
            $multplication += $barre->poid_pure * $barre->carrat_pure;
        }

        $moyenne = $multplication / $barres->sum('poid_pure');

        return number_format($moyenne, 2);
    }
}
