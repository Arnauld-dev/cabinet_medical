<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use App\Models\Facture;
use Illuminate\Http\Request;

class PaiementController extends Controller
{
    /**
     * Afficher tous les paiements
     */
    public function index()
    {
        $paiements = Paiement::with('facture')
            ->orderBy('datePaiement', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $paiements
        ]);
    }

    /**
     * Enregistrer un paiement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'idFacture' => [
                'required',
                'integer',
                'exists:facture,idFacture'
            ],

            'datePaiement' => [
                'required',
                'date'
            ],

            'montant' => [
                'required',
                'numeric',
                'min:0.01'
            ],

            'modePaiement' => [
                'required',
                'string',
                'max:255'
            ],
        ]);

        // Vérifier que la facture existe
        $facture = Facture::find($validated['idFacture']);

        if (!$facture) {
            return response()->json([
                'success' => false,
                'message' => 'Facture introuvable'
            ], 404);
        }

        // Calculer le montant déjà payé
        $totalPaye = $facture->paiements()->sum('montant');

        // Calculer le reste à payer
        $reste = $facture->montant - $totalPaye;

        // Vérifier que le paiement ne dépasse pas le montant restant
        if ($validated['montant'] > $reste) {
            return response()->json([
                'success' => false,
                'message' => 'Le montant du paiement dépasse le montant restant à payer',
                'montant_facture' => $facture->montant,
                'total_deja_paye' => $totalPaye,
                'reste_a_payer' => $reste
            ], 422);
        }

        $paiement = Paiement::create($validated);

        // Recalculer le total après paiement
        $nouveauTotalPaye = $facture->paiements()->sum('montant');

        $nouveauReste = $facture->montant - $nouveauTotalPaye;

        // Mettre automatiquement à jour le statut de la facture
        if ($nouveauReste <= 0) {
            $facture->update([
                'statut' => 'payée'
            ]);
        } elseif ($nouveauTotalPaye > 0) {
            $facture->update([
                'statut' => 'partiellement payée'
            ]);
        } else {
            $facture->update([
                'statut' => 'en attente'
            ]);
        }

        $paiement->load('facture');

        return response()->json([
            'success' => true,
            'message' => 'Paiement enregistré avec succès',
            'data' => $paiement,
            'montant_facture' => $facture->montant,
            'total_paye' => $nouveauTotalPaye,
            'reste_a_payer' => $nouveauReste,
            'statut' => $facture->statut
        ], 201);
    }

    /**
     * Afficher un paiement
     */
    public function show($id)
    {
        $paiement = Paiement::with([
            'facture.patient',
            'facture.consultation'
        ])->find($id);

        if (!$paiement) {
            return response()->json([
                'success' => false,
                'message' => 'Paiement introuvable'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $paiement
        ]);
    }

    /**
     * Modifier un paiement
     */
    public function update(Request $request, $id)
    {
        $paiement = Paiement::find($id);

        if (!$paiement) {
            return response()->json([
                'success' => false,
                'message' => 'Paiement introuvable'
            ], 404);
        }

        $validated = $request->validate([
            'idFacture' => [
                'sometimes',
                'required',
                'integer',
                'exists:facture,idFacture'
            ],

            'datePaiement' => [
                'sometimes',
                'required',
                'date'
            ],

            'montant' => [
                'sometimes',
                'required',
                'numeric',
                'min:0.01'
            ],

            'modePaiement' => [
                'sometimes',
                'required',
                'string',
                'max:255'
            ],
        ]);

        /*
         * Pour éviter les incohérences, on vérifie le montant
         * si celui-ci est modifié.
         */
        if (isset($validated['montant'])) {

            $factureId = $validated['idFacture']
                ?? $paiement->idFacture;

            $facture = Facture::find($factureId);

            if (!$facture) {
                return response()->json([
                    'success' => false,
                    'message' => 'Facture introuvable'
                ], 404);
            }

            $totalAutresPaiements = $facture->paiements()
                ->where('idPaiement', '!=', $paiement->idPaiement)
                ->sum('montant');

            if ($totalAutresPaiements + $validated['montant'] > $facture->montant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le montant total des paiements dépasse le montant de la facture'
                ], 422);
            }
        }

        $paiement->update($validated);

        // Mettre à jour le statut de la facture
        $facture = $paiement->facture;

        if ($facture) {
            $totalPaye = $facture->paiements()->sum('montant');

            if ($totalPaye >= $facture->montant) {
                $facture->update([
                    'statut' => 'payée'
                ]);
            } elseif ($totalPaye > 0) {
                $facture->update([
                    'statut' => 'partiellement payée'
                ]);
            } else {
                $facture->update([
                    'statut' => 'en attente'
                ]);
            }
        }

        $paiement->load('facture');

        return response()->json([
            'success' => true,
            'message' => 'Paiement modifié avec succès',
            'data' => $paiement
        ]);
    }

    /**
     * Supprimer un paiement
     */
    public function destroy($id)
    {
        $paiement = Paiement::find($id);

        if (!$paiement) {
            return response()->json([
                'success' => false,
                'message' => 'Paiement introuvable'
            ], 404);
        }

        $facture = $paiement->facture;

        $paiement->delete();

        // Recalculer le statut après suppression
        if ($facture) {

            $totalPaye = $facture->paiements()->sum('montant');

            if ($totalPaye >= $facture->montant) {
                $facture->update([
                    'statut' => 'payée'
                ]);
            } elseif ($totalPaye > 0) {
                $facture->update([
                    'statut' => 'partiellement payée'
                ]);
            } else {
                $facture->update([
                    'statut' => 'en attente'
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Paiement supprimé avec succès'
        ]);
    }
}
