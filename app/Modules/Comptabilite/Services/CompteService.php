<?php
namespace App\Modules\Comptabilite\Services;

use App\Modules\Comptabilite\Models\Caisse;
use App\Modules\Comptabilite\Models\Compte;
use App\Modules\Comptabilite\Models\FournisseurOperation;
use App\Modules\Comptabilite\Models\OperationClient;
use App\Modules\Comptabilite\Models\OperationDivers;
use App\Modules\Settings\Models\Devise;

class CompteService
{
    /**
     * 🔹 Calcule le solde d’un compte dans une devise donnée
     */
    public static function calculerSolde(int $id_compte): float
    {
        $compte = Compte::with('devise')->find($id_compte);

        // 🔸 Vérifie que le compte existe et a une devise
        if (! $compte || ! $compte->devise) {
            return 0.0;
        }

        $id_deviseCompte = $compte->devise_id;

        // 🔹 Fonction pour récupérer le total des montants dans la devise du compte
        $getTotal = function ($model, int $nature) use ($id_compte, $id_deviseCompte) {
            return $model::where('id_compte', $id_compte)
                ->where('id_devise', $id_deviseCompte)
                ->whereHas('typeOperation', fn($q) => $q->where('nature', $nature))
                ->sum('montant');
        };

        // ✅ Somme des opérations
        $entrees =
            $getTotal(OperationClient::class, 1) +
            $getTotal(OperationDivers::class, 1) +
            $getTotal(Caisse::class, 1);

        $sorties =
            $getTotal(OperationClient::class, 0) +
            $getTotal(OperationDivers::class, 0) +
            $getTotal(Caisse::class, 0);

        // 🔹 Fournisseur à part
        $getTotalFournisseur = function (int $nature) use ($id_compte, $id_deviseCompte) {
            return FournisseurOperation::where('compte_id', $id_compte)
                ->where('devise_id', $id_deviseCompte)
                ->whereHas('typeOperation', fn($q) => $q->where('nature', $nature))
                ->sum('montant');
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
    public static function verifierSoldeAvantOperation(int $id_compte, int $id_deviseOperation, float $montant): array
    {
        $compte = Compte::with('devise')->find($id_compte);

        if (! $compte) {
            return [
                'status'  => false,
                'message' => "Compte introuvable.",
            ];
        }

        // 🔸 Vérifie si la devise de l’opération correspond à celle du compte
        if ($compte->devise_id !== $id_deviseOperation) {
            return [
                'status'  => false,
                'message' => "Opération refusée : la devise de l’opération ({$id_deviseOperation}) ne correspond pas à celle du compte ({$compte->devise->symbole}).",
            ];
        }

        // 🔸 Calcule le solde actuel du compte
        $solde = self::calculerSolde($id_compte);

        // 🔸 Vérifie si le solde est suffisant
        if ($solde < $montant) {
            return [
                'status'  => false,
                'message' => "Solde insuffisant. Solde disponible : {$solde} {$compte->devise->symbole}",
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
