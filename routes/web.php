<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Toutes les routes sont publiques (pas d'authentification requise)
|--------------------------------------------------------------------------
*/

// Dashboard
Route::get('/', function () {
    return view('pages.dashboard.ecommerce', ['title' => 'GSF APP']);
})->name('dashboard');

// Calendrier
Route::get('/calendar', function () {
    return view('pages.calender', ['title' => 'Calendrier']);
})->name('calendar');

// Profil
Route::get('/profile', function () {
    return view('pages.profile', ['title' => 'Profile']);
})->name('profile');

// Formulaires
Route::get('/form-elements', function () {
    return view('pages.form.form-elements', ['title' => 'Form Elements']);
})->name('form-elements');

// Tableaux de base
Route::get('/basic-tables', function () {
    return view('pages.tables.basic-tables', ['title' => 'Basic Tables']);
})->name('basic-tables');

// Page Vide
Route::get('/blank', function () {
    return view('pages.blank', ['title' => 'Blank']);
})->name('blank');

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

// Erreurs
Route::get('/error-404', function () {
    return view('pages.errors.error-404', ['title' => 'Error 404']);
})->name('error-404');

// Graphiques
Route::get('/line-chart', function () {
    return view('pages.chart.line-chart', ['title' => 'Line Chart']);
})->name('line-chart');

Route::get('/bar-chart', function () {
    return view('pages.chart.bar-chart', ['title' => 'Bar Chart']);
})->name('bar-chart');

// Éléments UI
Route::get('/alerts', function () {
    return view('pages.ui-elements.alerts', ['title' => 'Alerts']);
})->name('alerts');

Route::get('/avatars', function () {
    return view('pages.ui-elements.avatars', ['title' => 'Avatars']);
})->name('avatars');

Route::get('/badge', function () {
    return view('pages.ui-elements.badges', ['title' => 'Badges']);
})->name('badges');

Route::get('/buttons', function () {
    return view('pages.ui-elements.buttons', ['title' => 'Buttons']);
})->name('buttons');

Route::get('/image', function () {
    return view('pages.ui-elements.images', ['title' => 'Images']);
})->name('images');

Route::get('/videos', function () {
    return view('pages.ui-elements.videos', ['title' => 'Videos']);
})->name('videos');

// Page de configuration des documents ACD

Route::get('/documents', function () {
    return view('pages.documents.document', ['title' => 'Gestion des documents']);
})->name('documents.index');
