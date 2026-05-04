<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\VisiteController;
use App\Http\Controllers\LotissementController;
use App\Http\Controllers\ContratController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\LotController;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\EcheanceController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Routes pour les Clients (API)
|--------------------------------------------------------------------------
*/
Route::prefix('clients')->group(function () {
    Route::get('/', [ClientController::class, 'index']);
    Route::post('/', [ClientController::class, 'store']);
    Route::get('/{id}', [ClientController::class, 'show']);
    Route::put('/{id}', [ClientController::class, 'update']);
    Route::delete('/{id}', [ClientController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Routes pour les Visites (API)
|--------------------------------------------------------------------------
*/
Route::prefix('visites')->group(function () {
    Route::get('/', [VisiteController::class, 'index']);
    Route::get('/statistics', [VisiteController::class, 'getStatistics']);
    Route::get('/date/{date}', [VisiteController::class, 'getByDate']);
    Route::get('/visitor/{nom}', [VisiteController::class, 'getByVisitor']);
    Route::post('/date-range', [VisiteController::class, 'getByDateRange']);
    Route::get('/{id}', [VisiteController::class, 'show']);
    Route::post('/', [VisiteController::class, 'store']);
    Route::put('/{id}', [VisiteController::class, 'update']);
    Route::delete('/{id}', [VisiteController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Routes pour les Lotissements (API)
|--------------------------------------------------------------------------
*/
Route::prefix('lotissements')->group(function () {
    Route::get('/', [LotissementController::class, 'index']);
    Route::post('/', [LotissementController::class, 'store']);
    Route::get('/villes', [LotissementController::class, 'getVilles']);
    Route::get('/statistics', [LotissementController::class, 'getStatistics']);
    Route::post('/search', [LotissementController::class, 'search']);
    Route::get('/ville/{ville}', [LotissementController::class, 'getByVille']);
    Route::get('/statut/{statut}', [LotissementController::class, 'getByStatut']);
    Route::get('/{id}', [LotissementController::class, 'show']);
    Route::put('/{id}', [LotissementController::class, 'update']);
    Route::delete('/{id}', [LotissementController::class, 'destroy']);
    Route::put('/{id}/statut', [LotissementController::class, 'updateStatut']);
    Route::get('/{id}/lots', [LotissementController::class, 'getLots']);
});

/*
|--------------------------------------------------------------------------
| Routes pour les Contrats (API)
|--------------------------------------------------------------------------
*/
Route::prefix('contrats')->group(function () {
    Route::get('/', [ContratController::class, 'index']);
    Route::post('/', [ContratController::class, 'store']);
    Route::get('/clients/liste', [ContratController::class, 'getClients']);
    Route::get('/lots/disponibles', [ContratController::class, 'getLotsDisponibles']);
    Route::get('/lots/{idLot}/prix', [ContratController::class, 'getPrixLot']);
    Route::get('/{id}/situation-paiement', [ContratController::class, 'getSituationPaiement']);
    Route::get('/{id}/imprimer', [ContratController::class, 'imprimerRecap']);
    Route::get('/{id}/echeances', [ContratController::class, 'getEcheances']);
    Route::post('/{id}/regenerer-echeances', [ContratController::class, 'regenererEcheances']);
    Route::get('/{id}', [ContratController::class, 'show']);
    Route::put('/{id}', [ContratController::class, 'update']);

    // ⚠️ IMPORTANT : La route admin-delete doit être AVANT la route delete générique
    Route::delete('/{id}/admin-delete', [ContratController::class, 'adminDelete']);

    // Route delete générique (pour les contrats sans paiements et non soldés)
    Route::delete('/{id}', [ContratController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Routes pour les Paiements (API)
|--------------------------------------------------------------------------
*/
Route::prefix('paiements')->group(function () {
    Route::get('/', [PaiementController::class, 'index']);
    Route::post('/', [PaiementController::class, 'store']);
    Route::get('/next-sequence/{yearMonth}', [PaiementController::class, 'getNextSequence']);
    Route::get('/montant-suggere/{idContrat}', [PaiementController::class, 'getMontantSuggere']);
    Route::get('/contrat/{idContrat}', [PaiementController::class, 'getByContrat']);
    Route::get('/{id}', [PaiementController::class, 'show']);
    Route::put('/{id}', [PaiementController::class, 'update']);
    Route::delete('/{id}', [PaiementController::class, 'destroy']);
    Route::post('/{id}/valider', [PaiementController::class, 'valider']);
    Route::post('/{id}/rejeter', [PaiementController::class, 'rejeter']);

    // Routes admin pour les paiements validés
    Route::delete('/{id}/admin-delete', [PaiementController::class, 'adminDelete']);
    Route::post('/{id}/verify-admin', [PaiementController::class, 'verifyAdmin']);
});

/*
|--------------------------------------------------------------------------
| Routes pour les Lots (API)
|--------------------------------------------------------------------------
*/
Route::prefix('lots')->group(function () {
    Route::get('/', [LotController::class, 'index']);
    Route::get('/all', [LotController::class, 'indexAll']);
    Route::get('/disponibles', [LotController::class, 'getDisponibles']);
    Route::get('/statut/{statut}', [LotController::class, 'getByStatut']);
    Route::get('/lotissement/{idLotissement}', [LotController::class, 'getByLotissement']);
    Route::get('/lotissements/liste', [LotController::class, 'getLotissements']);
    Route::get('/{id}', [LotController::class, 'show']);
    Route::post('/', [LotController::class, 'store']);
    Route::put('/{id}', [LotController::class, 'update']);
    Route::delete('/{id}', [LotController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Routes pour les Échéances (API)
|--------------------------------------------------------------------------
*/
Route::prefix('echeances')->group(function () {
    Route::get('/', [EcheanceController::class, 'getAll']);
    Route::get('/contrat/{idContrat}', [EcheanceController::class, 'index']);
    Route::get('/en-retard', [EcheanceController::class, 'getEnRetard']);
    Route::get('/mois-en-cours', [EcheanceController::class, 'getMoisEnCours']);
    Route::get('/{id}', [EcheanceController::class, 'show']);
    Route::post('/{id}/marquer-payee', [EcheanceController::class, 'marquerPayee']);
});

/*
|--------------------------------------------------------------------------
| Routes pour les documents ACD
|--------------------------------------------------------------------------
*/
Route::prefix('contrat-documents')->group(function () {
    Route::get('/contrat/{idContrat}', [App\Http\Controllers\ContratDocumentController::class, 'index']);
    Route::get('/{id}', [App\Http\Controllers\ContratDocumentController::class, 'show']);
    Route::put('/{id}/disponible', [App\Http\Controllers\ContratDocumentController::class, 'marquerDisponible']);
    Route::put('/{id}/remis', [App\Http\Controllers\ContratDocumentController::class, 'marquerRemis']);
    Route::post('/{id}/upload', [App\Http\Controllers\ContratDocumentController::class, 'uploadFichier']);
    Route::delete('/{id}', [App\Http\Controllers\ContratDocumentController::class, 'destroy']);
    Route::post('/contrat/{idContrat}/generer-manquants', [App\Http\Controllers\ContratDocumentController::class, 'genererManquants']);
});

/*
|--------------------------------------------------------------------------
| Routes pour la configuration des documents ACD
|--------------------------------------------------------------------------
*/
Route::prefix('admin/documents')->group(function () {
    Route::get('/types', [App\Http\Controllers\DocumentConfigurationController::class, 'getDocumentTypes']);
    Route::post('/types', [App\Http\Controllers\DocumentConfigurationController::class, 'storeDocumentType']);
    Route::put('/types/{id}', [App\Http\Controllers\DocumentConfigurationController::class, 'updateDocumentType']);
    Route::delete('/types/{id}', [App\Http\Controllers\DocumentConfigurationController::class, 'deleteDocumentType']);

    Route::get('/frais', [App\Http\Controllers\DocumentConfigurationController::class, 'getFraisParVille']);
    Route::post('/frais', [App\Http\Controllers\DocumentConfigurationController::class, 'storeFrais']);
    Route::delete('/frais/{id}', [App\Http\Controllers\DocumentConfigurationController::class, 'deleteFrais']);
    Route::get('/villes', [App\Http\Controllers\DocumentConfigurationController::class, 'getVilles']);
    Route::get('/frais/document-type/{idDocumentType}', [App\Http\Controllers\DocumentConfigurationController::class, 'getFraisByDocumentType']);
});

/*
|--------------------------------------------------------------------------
| Routes supplémentaires pour les paiements de documents
|--------------------------------------------------------------------------
*/
Route::post('/paiements/frais-document', [App\Http\Controllers\PaiementController::class, 'storeFraisDocument']);
Route::get('/paiements/document/{idContratDocument}', [App\Http\Controllers\PaiementController::class, 'getPaiementsByDocument']);
Route::post('/paiements/document/{idContratDocument}/valider', [App\Http\Controllers\PaiementController::class, 'validerPaiementDocument']);
Route::post('/paiements/document/{idContratDocument}/rejeter', [App\Http\Controllers\PaiementController::class, 'rejeterPaiementDocument']);
/*
|--------------------------------------------------------------------------
| Route contrat avec documents
|--------------------------------------------------------------------------
*/
Route::get('/contrats/{id}/with-documents', [App\Http\Controllers\ContratController::class, 'showWithDocuments']);

