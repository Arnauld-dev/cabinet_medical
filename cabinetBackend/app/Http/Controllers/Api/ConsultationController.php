<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    /**
     * Afficher toutes les consultations
     */
    public function index()
    {
        $consultations = Consultation::with(['rendezVous.patient', 'rendezVous.medecin', 'diagnostics', 'prescriptions.medicament','analysesLaboratoire'])
                                       ->orderBy('dateConsultation', 'desc')
                                       ->get();

        return response()->json([
            'success' => true,
            'data' => $consultations
            ]);
    }

    /**
     * Créer une consultation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'idConsultation' => 'required|integer|unique:consultations,idConsultation',
            'idRendeVous' => 'required|integer|exists:rendevous,idRendeVous',
            'dateConsultation' => 'required|date',
        ]);

        // Vérifier si ce rendez-vous possède déjà une consultation
        $consultationExistante = Consultation::where('idRendeVous', $validated['idRendeVous'])->first();

        if ($consultationExistante) {
            return response()->json([
                'success' => false,
                'message' => 'Ce rendez-vous possède déjà une consultation.'
            ], 422);
        }


        $consultation = Consultation::create($validated);

        $consultation->load(['rendezVous.patient', 'rendezVous.medecin' ]);

        return response()->json([
            'success' => true,
            'message' => 'Consultation créée avec succès',
            'data' => $consultation
        ], 201);
    }

    /**
     * Afficher une consultation
     */
    public function show(int $id)
    {
        $consultation = Consultation::with(['rendezVous.patient','rendezVous.medecin','diagnostics','prescriptions.medicament', 'analysesLaboratoire'])
                                       ->find($id);

        if (!$consultation) {
            return response()->json([
                'success' => false,
                'message' => 'Consultation introuvable'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $consultation
        ]);
    }

    /**
     * Modifier une consultation
     */
    public function update(Request $request, int $id)
    {
        $consultation = Consultation::find($id);

        if (!$consultation) {
            return response()->json([
                'success' => false,
                'message' => 'Consultation introuvable'
            ], 404);
        }

        $validated = $request->validate([
            'idRendeVous' => 'sometimes|required|integer|exists:rendevous,idRendeVous',
            'dateConsultation' => 'sometimes|required|date',
        ]);

        // Si on change le rendez-vous, vérifier qu'il n'est pas
        // déjà associé à une autre consultation
        if (isset($validated['idRendeVous']) &&$validated['idRendeVous'] != $consultation->idRendeVous) {

            $consultationExistante = Consultation::where('idRendeVous',$validated['idRendeVous'])
                                                    ->where('idConsultation','!=',$consultation->idConsultation)
                                                    ->first();

            if ($consultationExistante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce rendez-vous possède déjà une consultation.'
                ], 422);
            }
        }

        $consultation->update($validated);

        $consultation->load([
            'rendezVous.patient',
            'rendezVous.medecin',
            'diagnostics',
            'prescriptions.medicament',
            'analysesLaboratoire'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Consultation modifiée avec succès',
            'data' => $consultation
        ]);
    }

    /**
     * Supprimer une consultation
     */
    public function destroy(int $id)
    {
        $consultation = Consultation::find($id);

        if (!$consultation) {
            return response()->json([
                'success' => false,
                'message' => 'Consultation introuvable'
            ], 404);
        }

        $consultation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Consultation supprimée avec succès'
        ]);
    }

    /**
 * Associer des diagnostics à une consultation
 */
public function ajouterDiagnostics(Request $request,int $id)
{
    $consultation = Consultation::find($id);

    if (!$consultation) {
        return response()->json([
            'success' => false,
            'message' => 'Consultation introuvable'
        ], 404);
    }

    $validated = $request->validate([
        'diagnostics' => 'required|array',
        'diagnostics.*' => 'required|string|exists:diagnostics,codeDiagnostic',
    ]);

    $consultation->diagnostics()->sync($validated['diagnostics']);
    $consultation->load('diagnostics');

    return response()->json([
        'success' => true,
        'message' => 'Diagnostics associés à la consultation avec succès',
        'data' => $consultation
    ]);
}
}
