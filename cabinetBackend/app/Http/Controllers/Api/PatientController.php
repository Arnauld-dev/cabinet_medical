<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    /**
     * Afficher tous les patients
     */
    public function index()
    {
        $patients = Patient::orderBy('nom')->get();

        return response()->json([
            'success' => true,
            'data' => $patients
        ]);
    }

    /**
     * Enregistrer un nouveau patient
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'idPatient' => 'required|integer|unique:patients,idPatient',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'dateNaissance' => 'required|date',
            'adresse' => 'nullable|string|max:255',
            'telephone' => 'required|string|max:30',
        ]);

        $patient = Patient::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Patient créé avec succès',
            'data' => $patient
        ], 201);
    }

    /**
     * Afficher un patient
     */
    public function show($id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient introuvable'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $patient
        ]);
    }

    /**
     * Modifier un patient
     */
    public function update(Request $request, $id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient introuvable'
            ], 404);
        }

        $validated = $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'prenom' => 'sometimes|required|string|max:255',
            'dateNaissance' => 'sometimes|required|date',
            'adresse' => 'nullable|string|max:255',
            'telephone' => 'sometimes|required|string|max:30',
        ]);

        $patient->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Patient modifié avec succès',
            'data' => $patient
        ]);
    }

    /**
     * Supprimer un patient
     */
    public function destroy($id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient introuvable'
            ], 404);
        }

        $patient->delete();

        return response()->json([
            'success' => true,
            'message' => 'Patient supprimé avec succès'
        ]);
    }
}
