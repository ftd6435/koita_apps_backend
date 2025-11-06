<?php

namespace App\Traits;

use App\Modules\Comptabilite\Models\Caisse;
use App\Modules\Comptabilite\Models\FournisseurOperation;
use App\Modules\Comptabilite\Models\OperationClient;
use App\Modules\Comptabilite\Models\OperationDivers;

trait CalculOperations
{
    public function operationsHistorique()
    {
        // Step 1: Fetch all operations from different sources
        $allOperations = $this->fetchAllOperations();

        // Step 2: Sort operations chronologically (oldest first)
        $sortedOperations = $allOperations->sortBy('date_operation')->values();

        // Step 3: Group operations by currency and calculate running balances
        $historiqueByCurrency = $this->calculateRunningBalancesByCurrency($sortedOperations);

        return $historiqueByCurrency;
    }

    /**
     * Fetch all operations from all sources (Fournisseur, Client, Divers, Caisse)
     * 
     * @return Collection
     */
    private function fetchAllOperations()
    {
        $fournisseurOps = $this->getFournisseurOperations();
        $clientOps = $this->getClientOperations();
        $diversOps = $this->getDiversOperations();
        $caisseOps = $this->getCaisseOperations();

        // Combine all operations into a single collection
        return $fournisseurOps
            ->concat($clientOps)
            ->concat($diversOps)
            ->concat($caisseOps);
    }

    /**
     * Get formatted Fournisseur operations
     * 
     * @return Collection
     */
    private function getFournisseurOperations()
    {
        return FournisseurOperation::with(['fournisseur', 'typeOperation', 'devise', 'compte'])
            ->get()
            ->map(fn($op) => $this->formatOperation($op, [
                'fullname' => $op->fournisseur->name,
                'type' => 'Opération du fournisseur: ' . $op->fournisseur->name,
            ]));
    }

    /**
     * Get formatted Client operations
     * 
     * @return Collection
     */
    private function getClientOperations()
    {
        return OperationClient::with(['client', 'typeOperation', 'devise', 'compte'])
            ->get()
            ->map(fn($op) => $this->formatOperation($op, [
                'fullname' => $op->client->nom_complet ?? null,
                'type' => 'Opération du client (' . $op->client->type_client . ') ' . $op->client->nom_complet,
            ]));
    }

    /**
     * Get formatted Divers operations
     * 
     * @return Collection
     */
    private function getDiversOperations()
    {
        return OperationDivers::with(['divers', 'typeOperation', 'devise', 'compte'])
            ->get()
            ->map(fn($op) => $this->formatOperation($op, [
                'fullname' => $op->divers->name ?? null,
                'type' => 'Opération divers (' .$op->divers->type . ') ' . $op->divers->name,
            ]));
    }

    /**
     * Get formatted Caisse operations
     * 
     * @return Collection
     */
    private function getCaisseOperations()
    {
        return Caisse::with(['typeOperation', 'devise', 'compte'])
            ->get()
            ->map(fn($op) => $this->formatOperation($op, [
                'fullname' => "",
                'type' => 'Opération de la caisse',
            ]));
    }

    /**
     * Format an operation into a standardized array structure
     * 
     * @param mixed $operation The operation model instance
     * @param array $additionalFields Additional fields specific to the operation type
     * @return array
     */
    private function formatOperation($operation, array $additionalFields = [])
    {
        return array_merge([
            'reference' => $operation->reference,
            'date_operation' => $operation->date_operation,
            'montant' => $operation->montant,
            'credit' => $operation->typeOperation->nature == 1 ? $operation->montant : '',
            'debit' => $operation->typeOperation->nature == 0 ? $operation->montant : '',
            'devise' => $operation->devise->symbole ?? null,
            'devise_id' => $operation->devise_id ?? null,
            'banque' => $operation->compte->banque->libelle ?? null,
            'numero_compte' => $operation->compte->numero_compte ?? null,
            'type_operation' => $operation->typeOperation->libelle ?? null,
            'nature' => $operation->typeOperation->nature ?? null,
            'commentaire' => $operation->commentaire ?? null,
        ], $additionalFields);
    }

    /**
     * Group operations by currency and calculate running balances
     * 
     * Nature = 1 means CREDIT (adds to balance)
     * Nature = 0 means DEBIT (subtracts from balance)
     * 
     * @param Collection $operations Sorted operations
     * @return array
     */
    private function calculateRunningBalancesByCurrency($operations)
    {
        $historiqueByCurrency = [];

        foreach ($operations as $operation) {
            $currencySymbol = $operation['devise'] ?? 'Unknown';

            // Initialize currency group if it doesn't exist
            if (!isset($historiqueByCurrency[$currencySymbol])) {
                $historiqueByCurrency[$currencySymbol] = [
                    'devise' => $currencySymbol,
                    'operations' => [],
                    'solde_final' => 0,
                ];
            }

            // Calculate signed amount based on operation nature
            $signedAmount = $this->calculateSignedAmount(
                $operation['montant'], 
                $operation['nature']
            );

            // Calculate new running balance
            $previousBalance = $historiqueByCurrency[$currencySymbol]['solde_final'];
            $newBalance = $previousBalance + $signedAmount;

            // Add running balance to operation
            $operation['solde'] = $newBalance;

            // Store operation and update final balance
            $historiqueByCurrency[$currencySymbol]['operations'][] = $operation;
            $historiqueByCurrency[$currencySymbol]['solde_final'] = $newBalance;
        }

        return $historiqueByCurrency;
    }

    /**
     * Calculate signed amount based on operation nature
     */
    private function calculateSignedAmount($amount, $nature)
    {
        // Nature 1 = Credit (positive) | Nature 0 = Debit (negative)
        return $nature == 1 ? $amount : -$amount;
    }
}