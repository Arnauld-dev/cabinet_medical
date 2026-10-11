
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\MedecinController;
use App\Http\Controllers\Api\RendezVousController;
use App\Http\Controllers\Api\ConsultationController;
use App\Http\Controllers\Api\DiagnosticController;
use App\Http\Controllers\Api\MedicamentController;
use App\Http\Controllers\Api\PrescriptionController;
use App\Http\Controllers\Api\FactureController;
use App\Http\Controllers\Api\PaiementController;
use App\Http\Controllers\Api\AnalyseLaboratoireController;
use App\Http\Controllers\Api\StockMedicamentController;
use App\Http\Controllers\Api\MouvementStockController;

// Routes publiques
//Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Toutes les routes suivantes exigent un token valide
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Administrateur : accès complet aux fonctionnalités médicales
    // et administratives
    Route::middleware('role:administrateur')->group(function () {

        Route::apiResource('patients', PatientController::class);
        Route::apiResource('medecins', MedecinController::class);
        Route::apiResource('rendez-vous', RendezVousController::class);
        Route::apiResource('consultations', ConsultationController::class);

        Route::post(
            'consultations/{id}/diagnostics',
            [ConsultationController::class, 'ajouterDiagnostics']
        );

        Route::apiResource('diagnostics', DiagnosticController::class);
        Route::apiResource('medicaments', MedicamentController::class);
        Route::apiResource('prescriptions', PrescriptionController::class);
        Route::apiResource('factures', FactureController::class);
        Route::get('factures/{id}/impression', [FactureController::class, 'impression']);
        Route::apiResource('paiements', PaiementController::class);
        Route::apiResource('analyses-laboratoire', AnalyseLaboratoireController::class);
        Route::apiResource('stock-medicaments', StockMedicamentController::class);
        Route::apiResource('mouvements-stock', MouvementStockController::class);

        Route::post('/creer-medecin',[AuthController::class, 'creerMedecin'] );
        Route::post('/creer-secretaire',[AuthController::class, 'creerSecretaire'] );
    });

    // Médecin : consultations et soins
    Route::middleware('role:medecin')->group(function () {

        Route::apiResource('consultations', ConsultationController::class);
        Route::post(
            'consultations/{id}/diagnostics',
            [ConsultationController::class, 'ajouterDiagnostics']
        );

        Route::apiResource('diagnostics', DiagnosticController::class);
        Route::apiResource('prescriptions', PrescriptionController::class);
        Route::apiResource('analyses-laboratoire', AnalyseLaboratoireController::class);
    });

    // Secrétaire : accueil, rendez-vous et gestion administrative
    Route::middleware('role:secretaire')->group(function () {

        Route::apiResource('patients', PatientController::class);
        Route::apiResource('rendez-vous', RendezVousController::class);
        Route::apiResource('factures', FactureController::class);
        Route::get('factures/{id}/impression', [FactureController::class, 'impression']);
        Route::apiResource('paiements', PaiementController::class);
        Route::apiResource('medicaments', MedicamentController::class);
        Route::apiResource('stock-medicaments', StockMedicamentController::class);
        Route::apiResource('mouvements-stock', MouvementStockController::class);
    });
});
