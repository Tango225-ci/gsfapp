<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProprietaireTerrienController;
use App\Http\Controllers\BanqueController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Portail\PortailAuthController;
use App\Http\Controllers\Portail\PortailDashboardController;
use App\Http\Controllers\Portail\PortailContratController;
use App\Http\Controllers\Portail\PortailDocumentController;
use App\Http\Controllers\Portail\PortailPaiementController;
use App\Http\Controllers\Portail\PortailNotificationController;
use App\Http\Controllers\Portail\PortailPasswordResetController;
use App\Http\Controllers\Client\SignedDocumentController;
use Illuminate\Support\Facades\Route;

// ── Authentification ─────────────────────────────────────────────────────────
Route::middleware('guest:utilisateurs')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Mot de passe oublié
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');

    // Réinitialisation du mot de passe
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:utilisateurs')
    ->name('logout');

// Changement de mot de passe forcé + profil (auth requis, sans force.password.change pour éviter la boucle)
Route::middleware('auth:utilisateurs')->group(function () {
    Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('password.change.update');
});

// Profil (dans l'app principale)
Route::middleware(['auth:utilisateurs', 'force.password.change'])->group(function () {
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
});

// ── Pages protégées ──────────────────────────────────────────────────────────
Route::middleware(['auth:utilisateurs', 'force.password.change'])->group(function () {

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Calendrier
Route::get('/calendar', function () {
    return view('pages.calender', ['title' => 'Calendrier']);
})->name('calendar');

// Clients
Route::get('/clients', function () {
    return view('pages.clients.client', ['title' => 'Gestion des Clients']);
})->name('clients.index');

// Lots
Route::get('/lots', function () {
    return view('pages.lots.lot', ['title' => 'Gestion des lots']);
})->name('lots.index');

// Lotissements
Route::get('/lotissements', function () {
    return view('pages.lotissements.lotissement', ['title' => 'Gestion des lotissements']);
})->name('lotissements.index');

// Contrats
Route::get('/contrats', function () {
    return view('pages.contrats.contrat', ['title' => 'Gestion des contrats']);
})->name('contrats.index');

// Paiements
Route::get('/paiements', function () {
    return view('pages.paiements.paiement', ['title' => 'Gestion des paiements']);
})->name('paiements.index');

// Visites
Route::get('/visites', function () {
    return view('pages.visites.visite', ['title' => 'Gestion des Visites']);
})->name('visites.index');

// Echeances
Route::get('/echeances', function () {
    return view('pages.echeances.echeance', ['title' => 'Gestion des échéances']);
})->name('echeances.index');

// Utilisateurs (plus de restriction ADMIN pour le moment)
Route::get('/utilisateurs', function () {
    return view('pages.utilisateurs.utilisateur', ['title' => 'Gestion des utilisateurs']);
})->name('utilisateurs.index');

// Page de configuration des documents ACD
Route::get('/documents', function () {
    return view('pages.documents.document', ['title' => 'Gestion des documents']);
})->name('documents.index');

// Import CSV (ADMIN uniquement — contrôlé dans le contrôleur)
Route::get('/import', [ImportController::class, 'index'])->name('import.index');
Route::get('/import/template/{entity}', [ImportController::class, 'template'])->name('import.template');

// Export Excel (permission par entité contrôlée dans le contrôleur)
Route::get('/export/{entity}', [ExportController::class, 'export'])->name('export');

// Banque GSF (comptes bancaires de l'entreprise)
Route::get('/banque', [BanqueController::class, 'index'])->name('banque.index');

// Propriétaires Terriens (ADMIN uniquement)
Route::get('/proprietaires', [ProprietaireTerrienController::class, 'index'])->name('proprietaires.index');

}); // fin du groupe auth:utilisateurs + force.password.change

// ── Portail client (PWA) ─────────────────────────────────────────────────────
Route::get('/portail/manifest.webmanifest', [PortailAuthController::class, 'manifest'])->name('portail.manifest');

Route::prefix('portail')->name('portail.')->group(function () {

    Route::middleware('guest:clients')->group(function () {
        Route::get('/connexion', [PortailAuthController::class, 'showLogin'])->name('login');
        Route::post('/connexion', [PortailAuthController::class, 'login']);
    });

    Route::post('/deconnexion', [PortailAuthController::class, 'logout'])
        ->middleware('auth:clients')
        ->name('logout');

    Route::middleware('auth:clients')->group(function () {
        Route::get('/changer-mot-de-passe', [PortailAuthController::class, 'showChangePassword'])->name('password.change');
        Route::post('/changer-mot-de-passe', [PortailAuthController::class, 'changePassword'])->name('password.change.update');
    });

    // Réinitialisation par code OTP SMS — accessible connecté ou non
    Route::get('/mot-de-passe-oublie', [PortailPasswordResetController::class, 'showRequest'])->name('otp.request');
    Route::post('/mot-de-passe-oublie', [PortailPasswordResetController::class, 'sendCode'])->name('otp.send');
    Route::get('/mot-de-passe-oublie/verification', [PortailPasswordResetController::class, 'showVerify'])->name('otp.verify.show');
    Route::post('/mot-de-passe-oublie/verification', [PortailPasswordResetController::class, 'confirmReset'])->name('otp.verify');

    Route::middleware(['auth:clients', 'force.client.password.change'])->group(function () {
        Route::get('/', [PortailDashboardController::class, 'index'])->name('dashboard');
        Route::get('/contrats', [PortailContratController::class, 'index'])->name('contrats.index');
        Route::get('/contrats/{id}', [PortailContratController::class, 'show'])->name('contrats.show');
        Route::get('/contrats/{id}/etat', [PortailContratController::class, 'etat'])->name('contrats.etat');
        Route::get('/documents/{id}/telecharger', [PortailDocumentController::class, 'download'])->name('documents.download');
        Route::get('/paiements/{id}/recu', [PortailPaiementController::class, 'recu'])->name('paiements.recu');
        Route::get('/notifications', [PortailNotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/{id}/ouvrir', [PortailNotificationController::class, 'ouvrir'])->name('notifications.ouvrir');
        Route::post('/notifications/tout-lire', [PortailNotificationController::class, 'toutLire'])->name('notifications.tout-lire');
        Route::get('/profil', [PortailDashboardController::class, 'profile'])->name('profile');
    });
});

// ── Documents pour l'app mobile (liens signés, sans session) ─────────────────
Route::middleware('signed')->group(function () {
    Route::get('/documents/contrats/{id}/etat', [SignedDocumentController::class, 'etatContrat'])->name('client.contrats.etat.signed');
    Route::get('/documents/paiements/{id}/recu', [SignedDocumentController::class, 'recuPaiement'])->name('client.paiements.recu.signed');
    Route::get('/documents/{id}/telecharger', [SignedDocumentController::class, 'telechargerDocument'])->name('client.documents.telecharger.signed');
});
