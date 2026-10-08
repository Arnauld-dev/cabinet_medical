<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockMedicament;
use Illuminate\Http\Request;

class StockMedicamentController extends Controller
{
    /**
     * Afficher tout le stock
     */
    public function index()
    {
        $stocks = StockMedicament::with('medicament')
            ->orderBy('dateExpiration', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $stocks
        ]);
    }

    /**
     * Ajouter un médicament au stock
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'idStock' => [
                'required',
                'integer',
                'unique:stock_medicament,idStock'
            ],

            'codeMedicament' => [
                'required',
                'string',
                'exists:medicament,codeMedicament',
                'unique:stock_medicament,codeMedicament'
            ],

            'quantite' => [
                'required',
                'integer',
                'min:0'
            ],

            'quantiteMin' => [
                'required',
                'integer',
                'min:0'
            ],

            'dateExpiration' => [
                'required',
                'date'
            ],

            'fournisseur' => [
                'required',
                'string',
                'max:255'
            ],

            'emplacement' => [
                'required',
                'string',
                'max:255'
            ],
        ]);

        $stock = StockMedicament::create($validated);

        $stock->load('medicament');

        return response()->json([
            'success' => true,
            'message' => 'Stock du médicament créé avec succès',
            'data' => $stock
        ], 201);
    }

    /**
     * Afficher un stock
     */
    public function show($id)
    {
        $stock = StockMedicament::with([
            'medicament',
            'mouvements'
        ])->find($id);

        if (!$stock) {
            return response()->json([
                'success' => false,
                'message' => 'Stock introuvable'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $stock
        ]);
    }

    /**
     * Modifier un stock
     */
    public function update(Request $request, $id)
    {
        $stock = StockMedicament::find($id);

        if (!$stock) {
            return response()->json([
                'success' => false,
                'message' => 'Stock introuvable'
            ], 404);
        }

        $validated = $request->validate([
            'codeMedicament' => [
                'sometimes',
                'required',
                'string',
                'exists:medicament,codeMedicament',
                'unique:stock_medicament,codeMedicament,' . $id . ',idStock'
            ],

            'quantite' => [
                'sometimes',
                'required',
                'integer',
                'min:0'
            ],

            'quantiteMin' => [
                'sometimes',
                'required',
                'integer',
                'min:0'
            ],

            'dateExpiration' => [
                'sometimes',
                'required',
                'date'
            ],

            'fournisseur' => [
                'sometimes',
                'required',
                'string',
                'max:255'
            ],

            'emplacement' => [
                'sometimes',
                'required',
                'string',
                'max:255'
            ],
        ]);

        $stock->update($validated);

        $stock->load('medicament');

        return response()->json([
            'success' => true,
            'message' => 'Stock modifié avec succès',
            'data' => $stock
        ]);
    }

    /**
     * Supprimer un stock
     */
    public function destroy($id)
    {
        $stock = StockMedicament::find($id);

        if (!$stock) {
            return response()->json([
                'success' => false,
                'message' => 'Stock introuvable'
            ], 404);
        }

        $stock->delete();

        return response()->json([
            'success' => true,
            'message' => 'Stock supprimé avec succès'
        ]);
    }
}
