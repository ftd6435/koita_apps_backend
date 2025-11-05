<?php
namespace App\Modules\Comptabilite\Services;

use App\Modules\Comptabilite\Models\Caisse;
use App\Modules\Comptabilite\Models\FournisseurOperation;
use App\Modules\Comptabilite\Models\OperationClient;
use App\Modules\Comptabilite\Models\OperationDivers;
use App\Modules\Settings\Models\Devise;

class CompteService
{
    /**
     * 🔹 Calcule le solde d’un compte dans une devise donnée
     */
    public static function calculerSoldeParDevise(int $id_compte, int $id_devise): float
    {
        $symbole = Devise::find($id_devise)?->symbole;
        if (! $symbole) {
            return 0.0;
        }

        $getTotal = function ($model, int $nature) use ($id_compte, $id_devise) {
            return $model::where('id_compte', $id_compte)
                ->whereHas('typeOperation', fn($q) => $q->where('nature', $nature))
                ->where('id_devise', $id_devise)
                ->sum('montant');
        };

        // ✅ Somme des opérations pour client, divers et caisse
        $entrees =
            $getTotal(OperationClient::class, 1) +
            $getTotal(OperationDivers::class, 1) +
            $getTotal(Caisse::class, 1);

        $sorties =
            $getTotal(OperationClient::class, 0) +
            $getTotal(OperationDivers::class, 0) +
            $getTotal(Caisse::class, 0);

        // ✅ Fournisseur à part (nommage différent)
        $entreesF = FournisseurOperation::where('compte_id', $id_compte)
            ->whereHas('typeOperation', fn($q) => $q->where('nature', 1))
            ->where('devise_id', $id_devise)
            ->sum('montant');

        $sortiesF = FournisseurOperation::where('compte_id', $id_compte)
            ->whereHas('typeOperation', fn($q) => $q->where('nature', 0))
            ->where('devise_id', $id_devise)
            ->sum('montant');

        $solde = ($entrees + $entreesF) - ($sorties + $sortiesF);

        return round($solde, 2);
    }

    /**
     * 🔹 Vérifie le solde avant une opération donnée
     */
    public static function verifierSoldeAvantOperation(int $id_compte, int $id_devise, float $montant): array
    {
        $solde = self::calculerSoldeParDevise($id_compte, $id_devise);

        if ($solde < $montant) {
            return [
                'status'  => false,
                'message' => "Solde insuffisant pour effectuer cette opération.
                              Solde disponible : {$solde}",
                'solde' => $solde,
            ];
        }

        return [
            'status'  => true,
            'message' => "Solde suffisant. Opération autorisée.",
            'solde'   => $solde,
        ];
    }
}
