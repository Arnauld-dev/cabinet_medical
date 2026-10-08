<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medecin;
use Illuminate\Http\Request;

class MedecinController extends Controller
{
    /**
     * Afficher tous les médecins
     */
    public function index()
    {
        $medecins = Medecin::orderBy('nom')->get();

        return response()->json([
            'success' => true,
            'data' => $medecins
        ]);
    }

    /**
     * Enregistrer un nouveau médecin
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'idMedecin' => 'required|integer|unique:medecins,idMedecin',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'specialite' => 'required|string|max:255',
        ]);

        $medecin = Medecin::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Médecin créé avec succès',
            'data' => $medecin
        ], 201);
    }

    /**
     * Afficher un médecin
     */
    public function show($id)
    {
        $medecin = Medecin::find($id);

        if (!$medecin) {
            return response()->json([
                'success' => false,
                'message' => 'Médecin introuvable'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $medecin
        ]);
    }

    /**
     * Modifier un médecin
     */
    public function update(Request $request, $id)
    {
        $medecin = Medecin::find($id);

        if (!$medecin) {
            return response()->json([
                'success' => false,
                'message' => 'Médecin introuvable'
            ], 404);
        }

        $validated = $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'prenom' => 'sometimes|required|string|max:255',
            'specialite' => 'sometimes|required|string|max:255',
        ]);

        $medecin->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Médecin modifié avec succès',
            'data' => $medecin
        ]);
    }

    /**
     * Supprimer un médecin
     */
    public function destroy($id)
    {
        $medecin = Medecin::find($id);

        if (!$medecin) {
            return response()->json([
                'success' => false,
                'message' => 'Médecin introuvable'
            ], 404);
        }

        $medecin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Médecin supprimé avec succès'
        ]);
    }
}
