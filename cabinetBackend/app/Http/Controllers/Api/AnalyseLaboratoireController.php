<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnalyseLaboratoire;
use Illuminate\Http\Request;

class AnalyseLaboratoireController extends Controller
{
    /**
     * Afficher toutes les analyses
     */
    public function index()
    {
        $analyses = AnalyseLaboratoire::with([
            'patient',
            'consultation'
        ])
        ->orderBy('datePrescription', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $analyses
        ]);
    }

    /**
     * Créer une analyse de laboratoire
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'idAnalyse' => [
                'required',
                'integer',
                'unique:analyse_laboratoire,idAnalyse'
            ],

            'idPatient' => [
                'required',
                'integer',
                'exists:patients,idPatient'
            ],

            'idConsultation' => [
                'required',
                'integer',
                'exists:consultations,idConsultation'
            ],

            'typeAnalyse' => [
                'required',
                'string',
                'max:255'
            ],

            'datePrescription' => [
                'required',
                'date'
            ],

            'laboratoire' => [
                'required',
                'string',
                'max:255'
            ],

            'resultat' => [
                'nullable',
                'string'
            ],

            'dateResultat' => [
                'nullable',
                'date'
            ],
        ]);

        $analyse = AnalyseLaboratoire::create($validated);

        $analyse->load([
            'patient',
            'consultation'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Analyse de laboratoire créée avec succès',
            'data' => $analyse
        ], 201);
    }

    /**
     * Afficher une analyse
     */
    public function show($id)
    {
        $analyse = AnalyseLaboratoire::with([
            'patient',
            'consultation.rendezVous.medecin'
        ])->find($id);

        if (!$analyse) {
            return response()->json([
                'success' => false,
                'message' => 'Analyse de laboratoire introuvable'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $analyse
        ]);
    }

    /**
     * Modifier une analyse
     */
    public function update(Request $request, $id)
    {
        $analyse = AnalyseLaboratoire::find($id);

        if (!$analyse) {
            return response()->json([
                'success' => false,
                'message' => 'Analyse de laboratoire introuvable'
            ], 404);
        }

        $validated = $request->validate([
            'idPatient' => [
                'sometimes',
                'required',
                'integer',
                'exists:patients,idPatient'
            ],

            'idConsultation' => [
                'sometimes',
                'required',
                'integer',
                'exists:consultations,idConsultation'
            ],

            'typeAnalyse' => [
                'sometimes',
                'required',
                'string',
                'max:255'
            ],

            'datePrescription' => [
                'sometimes',
                'required',
                'date'
            ],

            'laboratoire' => [
                'sometimes',
                'required',
                'string',
                'max:255'
            ],

            'resultat' => [
                'nullable',
                'string'
            ],

            'dateResultat' => [
                'nullable',
                'date'
            ],
        ]);

        $analyse->update($validated);

        $analyse->load([
            'patient',
            'consultation'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Analyse de laboratoire modifiée avec succès',
            'data' => $analyse
        ]);
    }

    /**
     * Supprimer une analyse
     */
    public function destroy($id)
    {
        $analyse = AnalyseLaboratoire::find($id);

        if (!$analyse) {
            return response()->json([
                'success' => false,
                'message' => 'Analyse de laboratoire introuvable'
            ], 404);
        }

        $analyse->delete();

        return response()->json([
            'success' => true,
            'message' => 'Analyse de laboratoire supprimée avec succès'
        ]);
    }
}
