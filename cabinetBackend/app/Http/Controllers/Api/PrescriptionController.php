<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    /**
     * Afficher toutes les prescriptions
     */
    public function index()
    {
        $prescriptions = Prescription::with([
            'consultation',
            'medicament'
        ])
        ->orderBy('idPrescription', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $prescriptions
        ]);
    }

    /**
     * Ajouter une prescription
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'idPrescription' => 'required|integer|unique:prescriptions,idPrescription',

            'idConsultation' => [
                'required',
                'integer',
                'exists:consultations,idConsultation'
            ],

            'codeMedicament' => [
                'required',
                'string',
                'exists:medicament,codeMedicament'
            ],

            'posologie' => 'required|string|max:255',

            'dureeTraitement' => 'nullable|integer|min:1',
        ]);

        $prescription = Prescription::create($validated);

        $prescription->load([
            'consultation',
            'medicament'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Prescription créée avec succès',
            'data' => $prescription
        ], 201);
    }

    /**
     * Afficher une prescription
     */
    public function show($id)
    {
        $prescription = Prescription::with([
            'consultation.rendezVous.patient',
            'consultation.rendezVous.medecin',
            'medicament'
        ])->find($id);

        if (!$prescription) {
            return response()->json([
                'success' => false,
                'message' => 'Prescription introuvable'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $prescription
        ]);
    }

    /**
     * Modifier une prescription
     */
    public function update(Request $request, $id)
    {
        $prescription = Prescription::find($id);

        if (!$prescription) {
            return response()->json([
                'success' => false,
                'message' => 'Prescription introuvable'
            ], 404);
        }

        $validated = $request->validate([
            'idConsultation' => [
                'sometimes',
                'required',
                'integer',
                'exists:consultations,idConsultation'
            ],

            'codeMedicament' => [
                'sometimes',
                'required',
                'string',
                'exists:medicament,codeMedicament'
            ],

            'posologie' => 'sometimes|required|string|max:255',

            'dureeTraitement' => 'nullable|integer|min:1',
        ]);

        $prescription->update($validated);

        $prescription->load([
            'consultation',
            'medicament'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Prescription modifiée avec succès',
            'data' => $prescription
        ]);
    }

    /**
     * Supprimer une prescription
     */
    public function destroy($id)
    {
        $prescription = Prescription::find($id);

        if (!$prescription) {
            return response()->json([
                'success' => false,
                'message' => 'Prescription introuvable'
            ], 404);
        }

        $prescription->delete();

        return response()->json([
            'success' => true,
            'message' => 'Prescription supprimée avec succès'
        ]);
    }
}
