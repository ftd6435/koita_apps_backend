<?php


namespace App\Traits;

use App\Modules\Comptabilite\Models\FournisseurOperation;
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

                if($barre_fondue && $barre_fondue->statut == 'corriger'){
                    $poids_total += $barre_fondue->poids_dubai;
                }else{
                    $poids_total += $barre->poid_pure;
                }
            }
        }

        return number_format($poids_total, 2);
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

                if($barre_fondue && $barre_fondue->statut == 'corriger'){
                    $multplication += $barre_fondue->poids_dubai * $barre_fondue->poids_dubai;
                }else{
                    $multplication += $barre->poid_pure * $barre->carrat_pure;
                }
            }
        }

        return number_format($multplication, 2);
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

            if($barre_fondue->statut == 'corriger'){
                $montant = ($unit_price / $barre->densite) * $barre_fondue->poids_dubai * $barre_fondue->carrat_dubai;
            }else{
                $montant = ($unit_price / $barre->densite) * $barre->poid_pure * $barre->carrat_pure;
            }
        }else{
            $montant = ($unit_price / $barre->densite) * $barre->poid_pure * $barre->carrat_pure;
        }

        return number_format($montant, 2);
    }

    public function montantFixing($fixing_id)
    {
        $fixing = Fixing::find($fixing_id);
        $fixing_barres = FixingBarre::where('fixing_id', $fixing->id)->get();

        $montant_total = 0;

        foreach($fixing_barres as $barre){
            $montant_total += $this->montantBarre($barre->barre_id, $fixing->unit_price);
        }

        return number_format($montant_total, 2);
    }

    /**
     * Cette methode retourne la pureter de l'or
     */
    public function pureter($poid, $carrat)
    {
        $result = ($poid * $carrat) / 24;

        return number_format($result, 2);
    }

    /**
     * Cette method est pour retourner le solde d'un compte donner
     */
    public function soldeCompte($compte_id, $solde_initial = 0)
    {
        $entreeFournisseur = FournisseurOperation::where('compte_id', $compte_id)->whereHas('typeOperation', function ($query) {
                                $query->where('nature', 1);
                             })->sum('montant');

        $sortieFournisseur = FournisseurOperation::where('compte_id', $compte_id)->whereHas('typeOperation', function ($query) {
                                $query->where('nature', 0);
                             })->sum('montant');

        $solde = ($solde_initial + $entreeFournisseur) - $sortieFournisseur;

        return $solde ?? 0;
    }

    /**
     * Cette method est pour retourner le solde d'un fournisseur donner
     */
    public function soldeFournisseur($fournisseur_id)
    {
        $fixing = Fixing::where('fournisseur_id', $fournisseur_id)->first();

        $entreeFournisseur = FournisseurOperation::where('fournisseur_id', $fournisseur_id)->whereHas('typeOperation', function ($query) {
                                $query->where('nature', 1);
                             })->sum('montant');

        $sortieFournisseur = FournisseurOperation::where('fournisseur_id', $fournisseur_id)->whereHas('typeOperation', function ($query) {
                                $query->where('nature', 0);
                             })->sum('montant');

        $montantFixing = $this->montantFixing($fixing->id);

        $solde = ($montantFixing + $entreeFournisseur) - $sortieFournisseur;

        return number_format($solde, 2) ?? 0;
    }
}
