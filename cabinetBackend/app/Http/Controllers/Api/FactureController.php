<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facture;
use Illuminate\Http\Request;

class FactureController extends Controller
{
    /**
     * Afficher toutes les factures
     */
    public function index()
    {
        $factures = Facture::with([
            'patient',
            'consultation',
            'paiements'
        ])
            ->orderBy('dateFacturation', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $factures
        ]);
    }

    /**
     * Créer une facture
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'numeroFacture' => [
                'required',
                'string',
                'max:255',
                'unique:facture,numeroFacture'
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

            'dateFacturation' => [
                'required',
                'date'
            ],

            'montant' => [
                'required',
                'numeric',
                'min:0'
            ],

            'statut' => [
                'nullable',
                'string',
                'max:255'
            ],
        ]);

        // Si aucun statut n'est fourni
        $validated['statut'] = $validated['statut'] ?? 'en attente';

        $facture = Facture::create($validated);

        $facture->load([
            'patient',
            'consultation',
            'paiements'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Facture créée avec succès',
            'data' => $facture
        ], 201);
    }

    /**
     * Afficher une facture
     */
    public function show($id)
    {
        $facture = Facture::with([
            'patient',
            'consultation.rendezVous.medecin',
            'paiements'
        ])->find($id);

        if (!$facture) {
            return response()->json([
                'success' => false,
                'message' => 'Facture introuvable'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $facture
        ]);
    }

    /**
     * Modifier une facture
     */
    public function update(Request $request, $id)
    {
        $facture = Facture::find($id);

        if (!$facture) {
            return response()->json([
                'success' => false,
                'message' => 'Facture introuvable'
            ], 404);
        }

        $validated = $request->validate([
            'numeroFacture' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'unique:facture,numeroFacture,' . $id . ',idFacture'
            ],

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

            'dateFacturation' => [
                'sometimes',
                'required',
                'date'
            ],

            'montant' => [
                'sometimes',
                'required',
                'numeric',
                'min:0'
            ],

            'statut' => [
                'sometimes',
                'required',
                'string',
                'max:255'
            ],
        ]);

        $facture->update($validated);

        $facture->load([
            'patient',
            'consultation',
            'paiements'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Facture modifiée avec succès',
            'data' => $facture
        ]);
    }

    /**
     * Supprimer une facture
     */
    public function destroy($id)
    {
        $facture = Facture::find($id);

        if (!$facture) {
            return response()->json([
                'success' => false,
                'message' => 'Facture introuvable'
            ], 404);
        }

        $facture->delete();

        return response()->json([
            'success' => true,
            'message' => 'Facture supprimée avec succès'
        ]);
    }

    public function impression(int $id)
    {
        $facture = Facture::with([
            'patient',
            'consultation.rendezVous.medecin',
            'paiements'
        ])->findOrFail($id);

        $totalPaye = $facture->paiements->sum('montant');

        $resteAPayer = $facture->montant - $totalPaye;

        return response()->json([
            'facture' => [
                'idFacture' => $facture->idFacture,
                'numeroFacture' => $facture->numeroFacture,
                'dateFacturation' => $facture->dateFacturation,
                'montant' => $facture->montant,
                'statut' => $facture->statut,
            ],

            'patient' => [
                'idPatient' => $facture->patient->idPatient,
                'nom' => $facture->patient->nom,
                'prenom' => $facture->patient->prenom,
                'telephone' => $facture->patient->telephone,
                'adresse' => $facture->patient->adresse,
            ],

            'consultation' => $facture->consultation,

            'medecin' => $facture->consultation?->rendezVous?->medecin,

            'paiements' => $facture->paiements,

            'totalPaye' => $totalPaye,

            'resteAPayer' => max(0, $resteAPayer),
        ]);
    }
}
