<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicament;
use Illuminate\Http\Request;

class MedicamentController extends Controller
{
    /**
     * Afficher tous les médicaments
     */
    public function index()
    {
        $medicaments = Medicament::orderBy('nomMedicament')->get();

        return response()->json([
            'success' => true,
            'data' => $medicaments
        ]);
    }

    /**
     * Ajouter un médicament
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codeMedicament' => 'required|string|max:255|unique:medicament,codeMedicament',
            'nomMedicament' => 'required|string|max:255',
            'prix' => 'required|numeric|min:0',
        ]);

        $medicament = Medicament::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Médicament créé avec succès',
            'data' => $medicament
        ], 201);
    }

    /**
     * Afficher un médicament
     */
    public function show($code)
    {
        $medicament = Medicament::with([
            'stock',
            'prescriptions'
        ])->find($code);

        if (!$medicament) {
            return response()->json([
                'success' => false,
                'message' => 'Médicament introuvable'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $medicament
        ]);
    }

    /**
     * Modifier un médicament
     */
    public function update(Request $request, $code)
    {
        $medicament = Medicament::find($code);

        if (!$medicament) {
            return response()->json([
                'success' => false,
                'message' => 'Médicament introuvable'
            ], 404);
        }

        $validated = $request->validate([
            'nomMedicament' => 'sometimes|required|string|max:255',
            'prix' => 'sometimes|required|numeric|min:0',
        ]);

        $medicament->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Médicament modifié avec succès',
            'data' => $medicament
        ]);
    }

    /**
     * Supprimer un médicament
     */
    public function destroy(string $code)
    {
        $medicament = Medicament::find($code);

        if (!$medicament) {
            return response()->json([
                'success' => false,
                'message' => 'Médicament introuvable'
            ], 404);
        }

        $medicament->delete();

        return response()->json([
            'success' => true,
            'message' => 'Médicament supprimé avec succès'
        ]);
    }
}
