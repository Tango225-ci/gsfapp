<div x-data="dataTable" x-init="init()">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
        <!-- Header avec recherche et boutons -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Tableau Récapitulatif des Échéances</h2>
                <div class="flex items-center gap-3 flex-wrap">
                    <select x-model="filtreStatut" @change="currentPage = 1" class="w-40 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Tous les statuts</option>
                        <option value="A_PAYER">À payer</option>
                        <option value="PAYE">Payées</option>
                        <option value="EN_RETARD">En retard</option>
                    </select>
                    <select x-model="filtrePeriode" @change="currentPage = 1" class="w-40 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Toutes les périodes</option>
                        <option value="CE_MOIS">Ce mois</option>
                        <option value="MOIS_PROCHAIN">Mois prochain</option>
                        <option value="RETARD">En retard</option>
                        <option value="30_JOURS">30 prochains jours</option>
                    </select>
                    <div class="relative w-56">
                        <input type="text" x-model="search" placeholder="Rechercher client, contrat..." class="w-full pl-4 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
                        <svg class="absolute right-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <button @click="exportCSV" class="flex items-center gap-2 border border-gray-300 dark:border-gray-600 px-4 py-2 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">CSV</button>
                    <button @click="exportExcel" class="flex items-center gap-2 border border-gray-300 dark:border-gray-600 px-4 py-2 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Excel</button>
                </div>
            </div>
        </div>

        <!-- Cartes statistiques -->
        <div class="grid grid-cols-1 sm:grid-cols-5 gap-4 p-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm"><p class="text-sm text-gray-500 dark:text-gray-400">Total contrats</p><p class="text-2xl font-bold text-gray-800 dark:text-white" x-text="stats.total_contrats"></p></div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm"><p class="text-sm text-gray-500 dark:text-gray-400">Total échéances</p><p class="text-2xl font-bold text-gray-800 dark:text-white" x-text="stats.total"></p></div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm"><p class="text-sm text-gray-500 dark:text-gray-400">Montant total</p><p class="text-2xl font-bold text-gray-800 dark:text-white" x-text="formatPrice(stats.montant_total)"></p></div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm"><p class="text-sm text-gray-500 dark:text-gray-400">Montant payé</p><p class="text-2xl font-bold text-green-600 dark:text-green-400" x-text="formatPrice(stats.montant_paye)"></p></div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm"><p class="text-sm text-gray-500 dark:text-gray-400">En retard</p><p class="text-2xl font-bold text-red-600 dark:text-red-400" x-text="stats.en_retard"></p></div>
        </div>

        <!-- Tableau avec accordéon -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-3 py-3 text-center w-10"></th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">CONTRAT / N°</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">CLIENT / DATE</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">MONTANT</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">ÉCHÉANCES / STATUT</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">PROGRESSION</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">STATUT / ACTIONS</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800">
                    <template x-for="ligne in lignesVisibles" :key="ligne.key">
                        <tr :class="ligne.isFirst ? 'border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30' : 'border-b border-gray-100 dark:border-gray-700'">
                            <!-- BOUTON + -->
                            <td class="px-3 py-3 text-center align-middle">
                                <button x-show="ligne.isFirst" @click="ligne.contrat.expanded = !ligne.contrat.expanded" class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-800 flex items-center justify-center transition-transform duration-200" :class="ligne.contrat.expanded ? 'rotate-45' : ''">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                </button>
                            </td>
                            <!-- CONTRAT / N° -->
                            <td class="px-4 py-3 whitespace-nowrap align-middle">
                                <span x-show="ligne.isFirst" class="text-sm font-semibold text-gray-900 dark:text-white" x-text="ligne.contrat.contrat_reference"></span>
                                <span x-show="!ligne.isFirst" class="text-xs text-gray-500 dark:text-gray-400" x-text="'N° '+ligne.echeance.numero"></span>
                            </td>
                            <!-- CLIENT / DATE -->
                            <td class="px-4 py-3 whitespace-nowrap align-middle">
                                <div x-show="ligne.isFirst" class="flex items-center gap-2">
                                    <div class="flex-shrink-0 h-7 w-7 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center"><span class="text-xs font-medium text-white" x-text="ligne.contrat.client_nom.charAt(0).toUpperCase()"></span></div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white" x-text="ligne.contrat.client_nom"></span>
                                </div>
                                <span x-show="!ligne.isFirst" class="text-xs" :class="ligne.echeance.est_en_retard ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-500 dark:text-gray-400'" x-text="ligne.echeance.date_echeance"></span>
                            </td>
                            <!-- MONTANT -->
                            <td class="px-4 py-3 whitespace-nowrap align-middle">
                                <span x-show="ligne.isFirst" class="text-sm font-medium text-gray-800 dark:text-white" x-text="ligne.contrat.montant_total_formatted"></span>
                                <span x-show="!ligne.isFirst" class="text-xs text-gray-500 dark:text-gray-400" x-text="ligne.echeance.montant_prevu_formatted"></span>
                            </td>
                            <!-- ÉCHÉANCES / STATUT -->
                            <td class="px-4 py-3 whitespace-nowrap text-center align-middle">
                                <div x-show="ligne.isFirst">
                                    <span class="text-sm font-medium" :class="ligne.contrat.echeances_payees === ligne.contrat.total_echeances ? 'text-green-600' : 'text-blue-600'"><span x-text="ligne.contrat.echeances_payees"></span>/<span x-text="ligne.contrat.total_echeances"></span> payées</span>
                                    <span x-show="ligne.contrat.echeances_en_retard > 0" class="ml-1 text-xs text-red-500">(<span x-text="ligne.contrat.echeances_en_retard"></span> retard)</span>
                                </div>
                                <span x-show="!ligne.isFirst" class="px-2 py-0.5 rounded-full text-xs font-semibold" :class="{'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-200':ligne.echeance.statut==='A_PAYER','bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200':ligne.echeance.statut==='PAYE','bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200':ligne.echeance.statut==='EN_RETARD'}" x-text="ligne.echeance.statut_label"></span>
                            </td>
                            <!-- PROGRESSION -->
                            <td class="px-4 py-3 whitespace-nowrap align-middle">
                                <div x-show="ligne.isFirst" class="flex items-center gap-2">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 dark:bg-gray-700"><div class="h-2 rounded-full" :class="ligne.contrat.progression >= 100 ? 'bg-green-600' : 'bg-blue-600'" :style="'width:'+ligne.contrat.progression+'%'"></div></div>
                                    <span class="text-xs text-gray-500" x-text="ligne.contrat.progression+'%'"></span>
                                </div>
                                <div x-show="!ligne.isFirst" class="flex items-center gap-1"><div class="w-10 bg-gray-200 rounded-full h-1.5 dark:bg-gray-600"><div class="bg-blue-600 h-1.5 rounded-full" :style="'width:'+ligne.echeance.progression+'%'"></div></div><span class="text-xs text-gray-400" x-text="ligne.echeance.progression+'%'"></span></div>
                            </td>
                            <!-- STATUT CONTRAT / ACTIONS ÉCHÉANCE -->
                            <td class="px-4 py-3 whitespace-nowrap text-center align-middle">
                                <span x-show="ligne.isFirst" class="px-2 py-1 rounded-full text-xs font-semibold" :class="{'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-200':ligne.contrat.contrat_statut==='EN_COURS','bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200':ligne.contrat.contrat_statut==='SOLDE','bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200':ligne.contrat.contrat_statut==='RESILIE','bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-200':ligne.contrat.contrat_statut==='EN_RETARD'}" x-text="ligne.contrat.contrat_statut_label"></span>
                                <div x-show="!ligne.isFirst" class="flex gap-1 justify-center">
                                    <button @click.stop="viewDetails(ligne.echeance)" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300" title="Détails"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg></button>
                                    <button @click.stop="marquerCommePayeeEcheance(ligne.echeance)" x-show="ligne.echeance.statut !== 'PAYE'" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300" title="Marquer payée"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"></path></svg></button>
                                </div>
                            </td>
                            <!-- ACTIONS CONTRAT -->
                            <td class="px-4 py-3 whitespace-nowrap text-center align-middle">
                                <div x-show="ligne.isFirst" class="flex gap-1 justify-center">
                                    <button @click.stop="ouvrirPaiementDepuisContrat(ligne.contrat)" x-show="ligne.contrat.reste_a_payer > 0" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300" title="Paiement"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg></button>
                                    <button @click.stop="imprimerContrat(ligne.contrat)" class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300" title="Imprimer"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg></button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="lignesVisibles.length === 0">
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">Aucune échéance trouvée</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="text-sm text-gray-700 dark:text-gray-300">Affichage de <span class="font-medium" x-text="startIndex"></span> à <span class="font-medium" x-text="endIndex"></span> sur <span class="font-medium" x-text="filteredData.length"></span> contrats</div>
                <div class="flex gap-2">
                    <button @click="prevPage" :disabled="currentPage === 1" :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 transition-colors"><svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>Précédent</button>
                    <span class="px-3 py-1 text-sm text-gray-700 dark:text-gray-300">Page <span class="font-medium" x-text="currentPage"></span> / <span class="font-medium" x-text="totalPages"></span></span>
                    <button @click="nextPage" :disabled="currentPage === totalPages" :class="currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 transition-colors">Suivant<svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modale détails échéance -->
    <div x-show="showDetailsModal" x-cloak @keydown.escape.window="closeDetailsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div @click.stop class="relative w-full max-w-2xl max-h-[85vh] overflow-y-auto rounded-xl bg-white shadow-2xl dark:bg-gray-900">
            <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 bg-white px-5 py-3 dark:border-gray-700 dark:bg-gray-900">
                <div><h3 class="text-lg font-semibold text-gray-900 dark:text-white">Détails de l'échéance</h3><p class="mt-0.5 text-xs text-gray-500">Informations complètes</p></div>
                <button @click="closeDetailsModal" class="rounded-lg p-1.5 text-gray-400 hover:text-gray-600"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="p-5">
                <div class="mb-4"><div class="flex justify-between text-xs mb-1"><span>Progression</span><span x-text="selectedEcheance?.progression+'%'"></span></div><div class="w-full bg-gray-200 rounded-full h-2.5"><div class="bg-blue-600 h-2.5 rounded-full" :style="'width:'+(selectedEcheance?.progression||0)+'%'"></div></div></div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="text-xs text-gray-500">N°</label><input :value="'Échéance n°'+(selectedEcheance?.numero||'1')" readonly class="w-full rounded-lg border bg-gray-100 px-3 py-1.5 text-sm cursor-not-allowed"></div>
                    <div><label class="text-xs text-gray-500">Contrat</label><input x-model="selectedEcheance?.contrat_reference" readonly class="w-full rounded-lg border bg-gray-100 px-3 py-1.5 text-sm cursor-not-allowed"></div>
                    <div><label class="text-xs text-gray-500">Client</label><input x-model="selectedEcheance?.client_nom" readonly class="w-full rounded-lg border bg-gray-100 px-3 py-1.5 text-sm cursor-not-allowed"></div>
                    <div><label class="text-xs text-gray-500">Date</label><input x-model="selectedEcheance?.date_echeance_formatted" readonly class="w-full rounded-lg border bg-gray-100 px-3 py-1.5 text-sm cursor-not-allowed"></div>
                    <div><label class="text-xs text-gray-500">Montant prévu</label><input x-model="selectedEcheance?.montant_prevu_formatted" readonly class="w-full rounded-lg border bg-gray-100 px-3 py-1.5 text-sm cursor-not-allowed"></div>
                    <div><label class="text-xs text-gray-500">Statut</label><input x-model="selectedEcheance?.statut_label" readonly class="w-full rounded-lg border font-semibold px-3 py-1.5 text-sm"></div>
                </div>
                <div class="mt-5 flex gap-3 border-t pt-4">
                    <button @click="closeDetailsModal" class="rounded-lg border px-3 py-1.5 text-sm">Fermer</button>
                    <button @click="ouvrirPaiement" x-show="selectedEcheance?.statut!=='PAYE'" class="rounded-lg bg-green-600 px-3 py-1.5 text-sm text-white">Paiement</button>
                    <button @click="marquerCommePayee" x-show="selectedEcheance?.statut!=='PAYE'" class="rounded-lg bg-blue-600 px-3 py-1.5 text-sm text-white">Marquer payée</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('dataTable', () => ({
        search: '', filtreStatut: '', filtrePeriode: '', currentPage: 1, itemsPerPage: 10,
        showDetailsModal: false, selectedEcheance: null, contrats: [],

        get stats() {
            let t=0,mt=0,mp=0,er=0; const tc=this.contrats.length;
            this.contrats.forEach(c=>{if(c.echeances)c.echeances.forEach(e=>{t++;mt+=parseFloat(e.montant_prevu)||0;mp+=parseFloat(e.montant_paye)||0;if(e.statut==='EN_RETARD')er++})});
            return {total:t,total_contrats:tc,montant_total:mt,montant_paye:mp,en_retard:er};
        },

        get filteredData() {
            let d=[...this.contrats];
            if(this.filtreStatut||this.filtrePeriode||this.search){
                d=d.map(c=>({...c,echeances:c.echeances.filter(e=>{
                    let k=true;
                    if(this.filtreStatut&&e.statut!==this.filtreStatut)k=false;
                    if(k&&this.filtrePeriode){
                        const td=new Date(),cm=td.getMonth(),cy=td.getFullYear(),de=new Date(e.date_echeance_raw||e.date_echeance);
                        if(isNaN(de.getTime()))k=false;
                        else{const em=de.getMonth(),ey=de.getFullYear();
                            switch(this.filtrePeriode){case'CE_MOIS':k=em===cm&&ey===cy;break;case'MOIS_PROCHAIN':const nm=cm===11?0:cm+1,ny=cm===11?cy+1:cy;k=em===nm&&ey===ny;break;case'RETARD':k=e.statut==='EN_RETARD';break;case'30_JOURS':const diff=Math.ceil((de-td)/86400000);k=diff>=0&&diff<=30&&e.statut!=='PAYE';break;}
                        }
                    }
                    if(k&&this.search){const s=this.search.toLowerCase();k=(e.contrat_reference&&e.contrat_reference.toLowerCase().includes(s))||(e.client_nom&&e.client_nom.toLowerCase().includes(s))}
                    return k;
                })})).filter(c=>c.echeances.length>0);
            }
            return d;
        },

        // Nouveau getter qui aplatit les contrats en lignes individuelles
        get lignesVisibles() {
            const lignes = [];
            this.paginatedData.forEach(contrat => {
                contrat.echeances.forEach((echeance, index) => {
                    if (index === 0 || contrat.expanded) {
                        lignes.push({
                            key: contrat.id_contrat + '_' + echeance.id,
                            contrat: contrat,
                            echeance: echeance,
                            isFirst: index === 0
                        });
                    }
                });
            });
            return lignes;
        },

        get totalPages(){return Math.ceil(this.filteredData.length/this.itemsPerPage)||1},
        get paginatedData(){const s=(this.currentPage-1)*this.itemsPerPage;return this.filteredData.slice(s,s+this.itemsPerPage)},
        get startIndex(){return this.filteredData.length===0?0:(this.currentPage-1)*this.itemsPerPage+1},
        get endIndex(){return Math.min(this.currentPage*this.itemsPerPage,this.filteredData.length)},
        nextPage(){if(this.currentPage<this.totalPages)this.currentPage++},
        prevPage(){if(this.currentPage>1)this.currentPage--},

        formatPrice(p){if(!p&&p!==0)return'0 FCFA';const n=parseFloat(p);return isNaN(n)||n===0?'0 FCFA':new Intl.NumberFormat('fr-FR').format(n)+' FCFA'},
        formatDate(d){if(!d)return'-';const dt=new Date(d);return isNaN(dt.getTime())?d:dt.toLocaleDateString('fr-FR')},

        async init(){await this.loadEcheancesFromDB();window.addEventListener('paiement-updated',()=>{this.loadEcheancesFromDB()})},

        async loadEcheancesFromDB(){
            try{
                const r=await fetch('/api/echeances');
                if(r.ok){
                    const d=await r.json();
                    console.log('Données reçues:', d); // Pour déboguer
                    if(d.success&&Array.isArray(d.contrats)){
                        this.contrats=d.contrats.map(c=>{
                            const echeances = c.echeances || [];
                            const total = c.montant_total || 0;
                            const paye = c.montant_paye || 0;
                            return {
                                ...c,
                                expanded: false,
                                echeances: echeances,
                                montant_total_formatted: this.formatPrice(total),
                                reste_a_payer: Math.max(0, total - paye),
                                progression: total > 0 ? Math.round((paye / total) * 100) : 0
                            };
                        });
                        console.log('Contrats chargés:', this.contrats); // Pour déboguer
                    }
                    else if(d.success&&Array.isArray(d.echeances)){
                        const g={};
                        d.echeances.forEach(e=>{
                            const idC=e.id_contrat||'sans';
                            if(!g[idC])g[idC]={
                                id_contrat:idC,
                                contrat_reference:e.contrat_reference||'-',
                                client_nom:e.client_nom||'-',
                                montant_total:0,
                                montant_paye:0,
                                reste_a_payer:0,
                                total_echeances:0,
                                echeances_payees:0,
                                echeances_en_retard:0,
                                progression:0,
                                contrat_statut:'EN_COURS',
                                contrat_statut_label:'En cours',
                                expanded:false,
                                echeances:[]
                            };
                            g[idC].echeances.push(e);
                            g[idC].total_echeances++;
                            g[idC].montant_total+=parseFloat(e.montant_prevu)||0;
                            g[idC].montant_paye+=parseFloat(e.montant_paye)||0;
                            if(e.statut==='PAYE')g[idC].echeances_payees++;
                            if(e.statut==='EN_RETARD')g[idC].echeances_en_retard++;
                        });
                        this.contrats=Object.values(g).map(c=>({
                            ...c,
                            montant_total_formatted:this.formatPrice(c.montant_total),
                            reste_a_payer:Math.max(0,c.montant_total-c.montant_paye),
                            progression:c.montant_total>0?Math.round((c.montant_paye/c.montant_total)*100):0
                        }));
                        console.log('Contrats groupés:', this.contrats); // Pour déboguer
                    }
                }
                this.currentPage=1;
            }catch(e){console.error('Erreur:',e)}
        },

        viewDetails(item){this.selectedEcheance=item;this.showDetailsModal=true},
        closeDetailsModal(){this.showDetailsModal=false;this.selectedEcheance=null},
        ouvrirPaiement(){if(this.selectedEcheance){this.closeDetailsModal();this.ouvrirPaiementDepuisEcheance(this.selectedEcheance)}},
        ouvrirPaiementDepuisEcheance(e){window.dispatchEvent(new CustomEvent('open-paiement-form',{detail:{id_contrat:e.id_contrat,reference_contrat:e.contrat_reference,client_nom:e.client_nom,reste_a_payer:e.reste_a_payer}}))},
        ouvrirPaiementDepuisContrat(c){window.dispatchEvent(new CustomEvent('open-paiement-form',{detail:{id_contrat:c.id_contrat,reference_contrat:c.contrat_reference,client_nom:c.client_nom,reste_a_payer:c.reste_a_payer}}))},
        async marquerCommePayee(){if(this.selectedEcheance){await this.marquerCommePayeeEcheance(this.selectedEcheance);this.closeDetailsModal()}},
        async marquerCommePayeeEcheance(e){
            if(!confirm(`Marquer l'échéance n°${e.numero} comme payée ?`))return;
            try{
                const r=await fetch(`/api/echeances/${e.id}/marquer-payee`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'','Accept':'application/json'}});
                const d=await r.json();
                if(d.success){await this.loadEcheancesFromDB();alert(d.message||'Succès');window.dispatchEvent(new CustomEvent('paiement-updated'))}
                else alert(d.message||'Erreur');
            }catch(er){alert('Erreur')}
        },

        async imprimerContrat(c){if(!c.id_contrat)return;try{const r=await fetch(`/api/contrats/${c.id_contrat}/imprimer`);if(r.ok){const d=await r.json();this.genererPDF(d)}else alert('Erreur')}catch(e){alert('Erreur')}},
        genererPDF(data){const contrat=data.contrat,situation=data.situation,paiements=data.paiements||[],echeances=data.echeances||[];const pw=window.open('','_blank','width=900,height=700');const fp=(p)=>{if(!p&&p!==0)return'0 FCFA';const n=parseFloat(p);return isNaN(n)||n===0?'0 FCFA':new Intl.NumberFormat('fr-FR').format(n)+' FCFA'};const gsl=(s)=>{const l={'A_PAYER':'À payer','PAYE':'Payée','EN_RETARD':'En retard'};return l[s]||s};const ltp=(t)=>{const l={'COMPTANT':'Comptant','ECHELONNE':'Échelonné'};return l[t]||t};const ls=(s)=>{const l={'EN_COURS':'En cours','SOLDE':'Soldé','RESILIE':'Résilié','EN_RETARD':'En retard'};return l[s]||s};const lmp=(m)=>{const l={'ESPECES':'Espèces','VIREMENT':'Virement','CHEQUE':'Chèque','MOBILE_MONEY':'Mobile Money'};return l[m]||m||'-'};const lsp=(s)=>{const l={'EN_ATTENTE':'En attente','VALIDE':'Validé','REJETE':'Rejeté','ANNULE':'Annulé'};return l[s]||s};const fd=(d)=>{if(!d)return'-';const dt=new Date(d);return isNaN(dt.getTime())?d:dt.toLocaleDateString('fr-FR')};let h=`<!DOCTYPE html><html><head><title>Contrat ${contrat.reference_contrat}</title><meta charset="UTF-8"><style>*{margin:0;padding:0}body{font-family:Arial;margin:30px}h1{color:#1e40af;font-size:26px}h2{color:#374151;font-size:18px;margin:25px 0 15px;border-bottom:2px solid #3b82f6}.grid{display:grid;grid-template-columns:repeat(2,1fr);gap:15px;background:#f9fafb;padding:20px;border-radius:10px}.label{font-size:12px;color:#6b7280}.value{font-size:16px;font-weight:600}.amount-box{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:20px;border-radius:10px}.amount-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;text-align:center}.amount-item h4{font-size:13px}.amount-item .number{font-size:24px;font-weight:700}.progress-bar{width:100%;height:10px;background:rgba(255,255,255,.3);border-radius:5px;margin:10px 0}.progress-fill{height:100%;background:#10b981}table{width:100%;border-collapse:collapse;margin-top:15px}th{background:#f3f4f6;padding:12px;text-align:left;font-size:13px}td{padding:12px;border-bottom:1px solid #e5e7eb}.footer{margin-top:40px;text-align:center;font-size:12px;color:#9ca3af;border-top:1px solid #e5e7eb;padding-top:20px}.print-btn{background:#2563eb;color:#fff;padding:12px 24px;border:none;border-radius:8px;cursor:pointer;margin-bottom:20px;font-size:16px}@media print{body{margin:15px}.print-btn{display:none}}</style></head><body><button class="print-btn" onclick="window.print()">Imprimer</button><h1>Contrat ${contrat.reference_contrat}</h1><h2>Informations</h2><div class="grid"><div><span class="label">Client:</span><span class="value">${contrat.client?.nom_prenoms||'-'}</span></div><div><span class="label">Date:</span><span class="value">${fd(contrat.date_contrat)}</span></div><div><span class="label">Type:</span><span class="value">${ltp(contrat.type_paiement)}</span></div><div><span class="label">Statut:</span><span class="value">${ls(contrat.statut)}</span></div></div><h2>Situation</h2><div class="amount-box"><div class="amount-grid"><div class="amount-item"><h4>Total</h4><div class="number">${situation.montant_total}</div></div><div class="amount-item"><h4>Payé</h4><div class="number" style="color:#10b981">${situation.montant_paye}</div></div><div class="amount-item"><h4>Reste</h4><div class="number" style="color:${contrat.reste_a_payer>0?'#f59e0b':'#10b981'}">${situation.reste_a_payer}</div></div></div><div style="margin-top:15px"><div style="display:flex;justify-content:space-between;font-size:13px"><span>Progression</span><span>${situation.progression}%</span></div><div class="progress-bar"><div class="progress-fill" style="width:${situation.progression}%"></div></div></div></div>`;if(contrat.type_paiement==='ECHELONNE'&&echeances.length>0){h+=`<h2>Échéances</h2><table><thead><tr><th>N°</th><th>Date</th><th>Montant</th><th>Payé</th><th>Reste</th><th>Statut</th></tr></thead><tbody>`;echeances.forEach(e=>{const mp=parseFloat(e.montant_prevu)||0,mpe=parseFloat(e.montant_paye)||0,r=Math.max(0,mp-mpe);h+=`<tr><td>${e.numero||'-'}</td><td>${e.date_echeance||'-'}</td><td>${fp(mp)}</td><td>${fp(mpe)}</td><td>${fp(r)}</td><td>${gsl(e.statut)}</td></tr>`});h+=`</tbody></table>`}h+=`<h2>Paiements</h2><table><thead><tr><th>Date</th><th>Réf</th><th>Mode</th><th>Montant</th><th>Statut</th></tr></thead><tbody>`;if(paiements.length>0){paiements.forEach(p=>{h+=`<tr><td>${fd(p.date_paiement)}</td><td>${p.reference||'-'}</td><td>${lmp(p.mode_paiement)}</td><td>${fp(p.montant)}</td><td>${lsp(p.statut)}</td></tr>`})}else h+=`<tr><td colspan="5" style="text-align:center;padding:30px">Aucun</td></tr>`;h+=`</tbody></table><div class="footer">${new Date().toLocaleString('fr-FR')}</div></body></html>`;pw.document.write(h);pw.document.close()},

        exportCSV(){const hdr=['Contrat','Client','N°','Date','Montant','Payé','Reste','Statut'];const rows=[];this.filteredData.forEach(c=>{c.echeances.forEach(e=>{rows.push([c.contrat_reference,c.client_nom,e.numero,e.date_echeance,e.montant_prevu,e.montant_paye,e.reste_a_payer,e.statut_label])})});const csv=[hdr.join(','),...rows.map(r=>r.map(c=>`"${c}"`).join(','))].join('\n');const b=new Blob(['\uFEFF'+csv],{type:'text/csv'});const l=document.createElement('a');l.href=URL.createObjectURL(b);l.download=`echeances_${new Date().toISOString().split('T')[0]}.csv`;l.click()},
        exportExcel(){const hdr=['Contrat','Client','N°','Date','Montant','Payé','Reste','Statut'];const rows=[];this.filteredData.forEach(c=>{c.echeances.forEach(e=>{rows.push([c.contrat_reference,c.client_nom,e.numero,e.date_echeance,e.montant_prevu,e.montant_paye,e.reste_a_payer,e.statut_label])})});const xl=[hdr.join('\t'),...rows.map(r=>r.join('\t'))].join('\n');const b=new Blob(['\uFEFF'+xl],{type:'application/vnd.ms-excel'});const l=document.createElement('a');l.href=URL.createObjectURL(b);l.download=`echeances_${new Date().toISOString().split('T')[0]}.xls`;l.click()}
    }))
})
</script>
<style>[x-cloak]{display:none!important}</style>
