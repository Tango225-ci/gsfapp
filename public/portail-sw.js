// Service worker minimal pour le portail client GSF (MVP).
//
// Portée volontairement limitée : met seulement en cache le logo (asset
// statique, sans risque) pour l'installabilité de la PWA. Aucune page HTML
// n'est mise en cache : les pages du portail contiennent un jeton CSRF
// propre à chaque requête (login, formulaires) — les mettre en cache
// servirait un jeton périmé et casserait les soumissions de formulaire.
// Les requêtes de navigation (chargement de page) ne sont jamais
// interceptées : passent toujours par le réseau. Aucune donnée dynamique
// (contrats, paiements, documents) n'est mise en cache. Pas de
// synchronisation hors-ligne, pas de push dans cette itération.

const CACHE_NAME = 'gsf-portail-shell-v2';
const SHELL_ASSETS = ['/images/logo/gsf-logo.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(SHELL_ASSETS))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET' || event.request.mode === 'navigate') {
        return; // jamais de cache pour les pages HTML/navigations
    }

    const isShellAsset = SHELL_ASSETS.some((path) => event.request.url.endsWith(path));
    if (!isShellAsset) {
        return; // tout le reste (données, assets buildés) passe par le réseau
    }

    event.respondWith(
        caches.match(event.request).then((cached) => cached || fetch(event.request))
    );
});
