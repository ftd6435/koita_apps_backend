<?php


namespace App\Traits;

use App\Modules\Fixing\Models\Fixing;
use App\Modules\Fixing\Models\FixingBarre;
use App\Modules\Fondation\Models\Fondation;
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

    public function poidsFixing($fixing_id)
    {
        $fixing_barres = FixingBarre::where('fixing_id', $fixing_id)->get();

        if(! $fixing_barres){
            return 0;
        }

        $poids_total = 0;

        foreach($fixing_barres as $fixing_barre){
            $barre = Barre::find($fixing_barre->barre_id);

            if($barre->status == 'non fondue' || $barre->status == 'fusionner'){
                $poids_total += $barre->poid_pure;
            }else{
                $barre_fondue = Fondation::where('ids_barres', $fixing_barre->barre_id)->first();

                // if($barre_fondue && $barre_fondue->status == 'corriger'){
                //     $poids_total += $barre_fondue->poids_dubai;
                // }else{
                //     $poids_total += $barre_fondue->poids_fondu;
                // }

                $poids_total += $barre_fondue->poids_fondu;
            }
        }

        return $poids_total;
    }

    public function carratFixing($fixing_id)
    {
        $multplication = $this->poidsTimeCarrat($fixing_id);
        $poids_total = $this->poidsFixing($fixing_id);

        $result = $multplication / $poids_total;

        return number_format($result, 2) ?? 0;
    }

    protected function poidsTimeCarrat($fixing_id)
    {
        $fixing_barres = FixingBarre::where('fixing_id', $fixing_id)->get();

        if(! $fixing_barres){
            return 0;
        }

        $multplication = 0;

        foreach($fixing_barres as $fixing_barre){
            $barre = Barre::find($fixing_barre->barre_id);

            if($barre->status == 'non fondue' || $barre->status == 'fusionner'){
                $multplication += $barre->poid_pure * $barre->carrat_pure;
            }else{
                $barre_fondue = Fondation::where('ids_barres', $fixing_barre->barre_id)->first();

                // if($barre_fondue && $barre_fondue->status == 'corriger'){
                //     $multplication += $barre_fondue->poids_dubai * $barre_fondue->poids_dubai;
                // }else{
                //     $multplication += $barre_fondue->poids_fondu * $barre_fondue->carrat_fondu;
                // }

                $multplication += $barre_fondue->poids_fondu * $barre_fondue->carrat_fondu;
            }
        }

        return $multplication;
    }

    public function barreFondue($id)
    {
        $barre_fondue = Fondation::where('ids_barres', $id)->first();

        if(! $barre_fondue){
            return null;
        }

        $barre = [
            'id' => $barre_fondue->id,
            'poid_fondu' => $barre_fondue->poids_fondu,
            'carrat_fondu' => $barre_fondue->carrat_fondu,
            'poids_dubai' => $barre_fondue->poids_dubai,
            'carrat_dubai' => $barre_fondue->carrat_dubai,
        ];

        return $barre;
    }

    public function montantBarre($id, $unit_price)
    {
        $barre = Barre::find($id);

        $montant = 0;

        if($barre->status == "fondue"){
            $barre_fondue = Fondation::where('ids_barres', $barre->id)->first();

            // if($barre_fondue->status == 'corriger'){
            //     $montant = ($unit_price / $barre->densite) * $barre_fondue->poids_dubai * $barre_fondue->carrat_dubai;
            // }else{
            //     $montant = ($unit_price / $barre->densite) * $barre_fondue->poids_fondu * $barre_fondue->carrat_fondu;
            // }

            $montant = ($unit_price / $barre->densite) * $barre_fondue->poids_fondu * $barre_fondue->carrat_fondu;
        }else{
            $montant = ($unit_price / $barre->densite) * $barre->poid_pre * $barre->carrat_pure;
        }

        return $montant;
    }

    public function montantFixing($fixing_id)
    {
        $fixing = Fixing::find($fixing_id);
        $fixing_barres = FixingBarre::where('fixing_id', $fixing->id)->get();

        $montant_total = 0;

        foreach($fixing_barres as $barre){
            $montant_total += $this->montantBarre($barre->barre_id, $fixing->unit_price);
        }

        return $montant_total;
    }
}
