<?php
// À copier dans routes/api.php de votre projet Laravel (faites une copie de l'ancien fichier avant).
//
// Pourquoi ? Dans l'ancien fichier, les mêmes Route::apiResource étaient déclarées
// dans plusieurs groupes de rôles. Laravel garde seulement la DERNIÈRE déclaration :
// l'administrateur ne pouvait plus lire les patients, le médecin ne voyait pas les
// patients ni les médicaments, etc.
// Ici : chaque route est déclarée UNE seule fois, avec les rôles autorisés.

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
use App\Http\Controllers\Api\UserController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // 1) LECTURE pour tous les rôles connectés
    //    (nécessaire aussi pour remplir les listes déroulantes du frontend)
    Route::apiResource('patients', PatientController::class)->only(['index', 'show']);
    Route::apiResource('medecins', MedecinController::class)->only(['index', 'show']);
    Route::apiResource('rendez-vous', RendezVousController::class)->only(['index', 'show']);
    Route::apiResource('consultations', ConsultationController::class)->only(['index', 'show']);
    Route::apiResource('diagnostics', DiagnosticController::class)->only(['index', 'show']);
    Route::apiResource('medicaments', MedicamentController::class)->only(['index', 'show']);

    // 2) ADMINISTRATEUR seulement
    Route::middleware('role:administrateur')->group(function () {
        Route::apiResource('medecins', MedecinController::class)->except(['index', 'show']);
        Route::post('/creer-medecin', [AuthController::class, 'creerMedecin']);
        Route::post('/creer-secretaire', [AuthController::class, 'creerSecretaire']);

        // Gestion des utilisateurs (liste, modification, suppression)
        Route::get('/users', [UserController::class, 'index']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
    });

    // 3) ADMINISTRATEUR + SECRÉTAIRE : accueil et gestion administrative
    Route::middleware('role:administrateur,secretaire')->group(function () {
        Route::apiResource('patients', PatientController::class)->except(['index', 'show']);
        Route::apiResource('rendez-vous', RendezVousController::class)->except(['index', 'show']);
        Route::apiResource('medicaments', MedicamentController::class)->except(['index', 'show']);
        Route::apiResource('factures', FactureController::class);
        Route::get('factures/{id}/impression', [FactureController::class, 'impression']);
        Route::apiResource('paiements', PaiementController::class);
        Route::apiResource('stock-medicaments', StockMedicamentController::class);
        Route::apiResource('mouvements-stock', MouvementStockController::class);
    });

    // 4) ADMINISTRATEUR + MÉDECIN : consultations et soins
    Route::middleware('role:administrateur,medecin')->group(function () {
        Route::apiResource('consultations', ConsultationController::class)->except(['index', 'show']);
        Route::post('consultations/{id}/diagnostics', [ConsultationController::class, 'ajouterDiagnostics']);
        Route::apiResource('diagnostics', DiagnosticController::class)->except(['index', 'show']);
        Route::apiResource('prescriptions', PrescriptionController::class);
        Route::apiResource('analyses-laboratoire', AnalyseLaboratoireController::class);
    });
});
