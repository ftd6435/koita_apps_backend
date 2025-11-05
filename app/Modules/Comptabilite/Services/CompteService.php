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
        $symboleCompte = Devise::find($id_devise)?->symbole;

        if (! $symboleCompte) {
            return 0.0;
        }

        $convertMontant = function ($operation, $id_deviseCompte) {
            // ✅ Si la devise de l’opération est différente, on convertit avec le taux du jour
            if ($operation->id_devise != $id_deviseCompte) {
                // Exemple : montant * taux_jour pour ramener dans la devise du compte
                return $operation->montant * ($operation->taux_jour ?? 1);
            }

            // ✅ Même devise, pas de conversion
            return $operation->montant;
        };

        $getTotal = function ($model, int $nature) use ($id_compte, $id_devise, $convertMontant) {
            $operations = $model::where('id_compte', $id_compte)
                ->whereHas('typeOperation', fn($q) => $q->where('nature', $nature))
                ->get();

            return $operations->sum(fn($op) => $convertMontant($op, $id_devise));
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

        // ✅ Fournisseur à part (structure légèrement différente)
        $getTotalFournisseur = function (int $nature) use ($id_compte, $id_devise, $convertMontant) {
            $operations = FournisseurOperation::where('compte_id', $id_compte)
                ->whereHas('typeOperation', fn($q) => $q->where('nature', $nature))
                ->get();

            return $operations->sum(fn($op) => $convertMontant($op, $id_devise));
        };

        $entreesF = $getTotalFournisseur(1);
        $sortiesF = $getTotalFournisseur(0);

        // ✅ Solde final dans la devise du compte
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
