<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Diagnostic;
use Illuminate\Http\Request;

class DiagnosticController extends Controller
{
    /**
     * Afficher tous les diagnostics
     */
    public function index()
    {
        $diagnostics = Diagnostic::with('consultations')
                                    ->orderBy('nomDiagnostic')
                                    ->get();

        return response()->json([
            'success' => true,
            'data' => $diagnostics
        ]);
    }

    /**
     * Créer un diagnostic
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codeDiagnostic' => 'required|string|max:255|unique:diagnostics,codeDiagnostic',
            'nomDiagnostic' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $diagnostic = Diagnostic::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Diagnostic créé avec succès',
            'data' => $diagnostic
        ], 201);
    }

    /**
     * Afficher un diagnostic
     */
    public function show(string $code)
    {
        $diagnostic = Diagnostic::with([
            'consultations.rendezVous.patient',
            'consultations.rendezVous.medecin'
        ])->find($code);

        if (!$diagnostic) {
            return response()->json([
                'success' => false,
                'message' => 'Diagnostic introuvable'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $diagnostic
        ]);
    }

    /**
     * Modifier un diagnostic
     */
    public function update(Request $request,string $code)
    {
        $diagnostic = Diagnostic::find($code);

        if (!$diagnostic) {
            return response()->json([
                'success' => false,
                'message' => 'Diagnostic introuvable'
            ], 404);
        }

        $validated = $request->validate([
            'nomDiagnostic' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
        ]);

        $diagnostic->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Diagnostic modifié avec succès',
            'data' => $diagnostic
        ]);
    }

    /**
     * Supprimer un diagnostic
     */
    public function destroy(string $code)
    {
        $diagnostic = Diagnostic::find($code);

        if (!$diagnostic) {
            return response()->json([
                'success' => false,
                'message' => 'Diagnostic introuvable'
            ], 404);
        }

        $diagnostic->delete();

        return response()->json([
            'success' => true,
            'message' => 'Diagnostic supprimé avec succès'
        ]);
    }
}
