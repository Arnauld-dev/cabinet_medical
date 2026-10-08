<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MouvementStock;
use App\Models\StockMedicament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MouvementStockController extends Controller
{
    /**
     * Afficher tous les mouvements
     */
    public function index()
    {
        $mouvements = MouvementStock::with([
            'stock.medicament'
        ])
        ->orderBy('dateMouvement', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $mouvements
        ]);
    }

    /**
     * Enregistrer une entrée ou une sortie de stock
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'idStock' => [
                'required',
                'integer',
                'exists:stock_medicament,idStock'
            ],

            'typeMouvement' => [
                'required',
                'string',
                'in:entrée,sortie'
            ],

            'quantite' => [
                'required',
                'integer',
                'min:1'
            ],

            'dateMouvement' => [
                'required',
                'date'
            ],
        ]);

        try {
            $result = DB::transaction(function () use ($validated) {

                // Récupérer le stock
                $stock = StockMedicament::find($validated['idStock']);

                if (!$stock) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Stock introuvable'
                    ], 404);
                }

                /*
                 * Vérification pour une sortie.
                 */
                if (
                    $validated['typeMouvement'] === 'sortie'
                    && $validated['quantite'] > $stock->quantite
                ) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Stock insuffisant',
                        'quantite_disponible' => $stock->quantite,
                        'quantite_demandee' => $validated['quantite']
                    ], 422);
                }

                // Créer le mouvement
                $mouvement = MouvementStock::create($validated);

                // Mettre à jour la quantité du stock
                if ($validated['typeMouvement'] === 'entrée') {

                    $stock->quantite += $validated['quantite'];

                } else {

                    $stock->quantite -= $validated['quantite'];
                }

                $stock->save();

                $mouvement->load([
                    'stock.medicament'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Mouvement de stock enregistré avec succès',
                    'data' => $mouvement,
                    'nouvelle_quantite' => $stock->quantite
                ], 201);
            });

            return $result;

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l’enregistrement du mouvement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un mouvement
     */
    public function show($id)
    {
        $mouvement = MouvementStock::with([
            'stock.medicament'
        ])->find($id);

        if (!$mouvement) {
            return response()->json([
                'success' => false,
                'message' => 'Mouvement de stock introuvable'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $mouvement
        ]);
    }

    /**
     * Modifier un mouvement
     */
    public function update(Request $request, $id)
    {
        $mouvement = MouvementStock::find($id);

        if (!$mouvement) {
            return response()->json([
                'success' => false,
                'message' => 'Mouvement de stock introuvable'
            ], 404);
        }

        /*
         * Pour garder la cohérence du stock, il est préférable
         * de ne pas modifier directement un ancien mouvement.
         *
         * On peut toutefois modifier les informations
         * descriptives comme la date.
         */

        $validated = $request->validate([
            'dateMouvement' => [
                'sometimes',
                'required',
                'date'
            ],
        ]);

        $mouvement->update($validated);

        $mouvement->load([
            'stock.medicament'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mouvement modifié avec succès',
            'data' => $mouvement
        ]);
    }

    /**
     * Supprimer un mouvement
     */
    public function destroy($id)
    {
        $mouvement = MouvementStock::find($id);

        if (!$mouvement) {
            return response()->json([
                'success' => false,
                'message' => 'Mouvement de stock introuvable'
            ], 404);
        }

        /*
         * Avant de supprimer le mouvement, il faut annuler
         * son impact sur le stock.
         */
        $stock = $mouvement->stock;

        if ($stock) {

            if ($mouvement->typeMouvement === 'entrée') {

                // Une entrée supprimée doit être retirée du stock.
                if ($stock->quantite < $mouvement->quantite) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Impossible de supprimer ce mouvement : le stock actuel est insuffisant pour annuler cette entrée'
                    ], 422);
                }

                $stock->quantite -= $mouvement->quantite;

            } else {

                // Une sortie supprimée doit être remise dans le stock.
                $stock->quantite += $mouvement->quantite;
            }

            $stock->save();
        }

        $mouvement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mouvement supprimé et stock mis à jour avec succès'
        ]);
    }
}
