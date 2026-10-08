<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RendezVous;
use Illuminate\Http\Request;

class RendezVousController extends Controller
{
    /**
     * Afficher tous les rendez-vous
     */
    public function index()
    {
        $rendezVous = RendezVous::with([
            'patient',
            'medecin'
        ])
        ->orderBy('dateHeure', 'asc')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $rendezVous
        ]);
    }

    /**
     * Créer un nouveau rendez-vous
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'idPatient' => 'required|integer|exists:patients,idPatient',
            'idMedecin' => 'required|integer|exists:medecins,idMedecin',
            'dateHeure' => 'required|date',
        ]);

        $rendezVous = RendezVous::create($validated);

        // Charger le patient et le médecin
        $rendezVous->load([
            'patient',
            'medecin'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rendez-vous créé avec succès',
            'data' => $rendezVous
        ], 201);
    }

    /**
     * Afficher un rendez-vous
     */
    public function show($id)
    {
        $rendezVous = RendezVous::with([
            'patient',
            'medecin',
            'consultation'
        ])->find($id);

        if (!$rendezVous) {
            return response()->json([
                'success' => false,
                'message' => 'Rendez-vous introuvable'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $rendezVous
        ]);
    }

    /**
     * Modifier un rendez-vous
     */
    public function update(Request $request, $id)
    {
        $rendezVous = RendezVous::find($id);

        if (!$rendezVous) {
            return response()->json([
                'success' => false,
                'message' => 'Rendez-vous introuvable'
            ], 404);
        }

        $validated = $request->validate([
            'idPatient' => 'sometimes|required|integer|exists:patients,idPatient',
            'idMedecin' => 'sometimes|required|integer|exists:medecins,idMedecin',
            'dateHeure' => 'sometimes|required|date',
        ]);

        $rendezVous->update($validated);

        $rendezVous->load([
            'patient',
            'medecin'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rendez-vous modifié avec succès',
            'data' => $rendezVous
        ]);
    }

    /**
     * Supprimer un rendez-vous
     */
    public function destroy($id)
    {
        $rendezVous = RendezVous::find($id);

        if (!$rendezVous) {
            return response()->json([
                'success' => false,
                'message' => 'Rendez-vous introuvable'
            ], 404);
        }

        $rendezVous->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rendez-vous supprimé avec succès'
        ]);
    }
}
