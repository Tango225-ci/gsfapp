<?php

namespace App\Models;

use App\Jobs\SendClientSmsJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Notifications\PortailDocumentDisponibleNotification;
use App\Notifications\PortailDocumentRemisNotification;

class ContratDocument extends Model
{
    /**
     * Activer les timestamps automatiques
     */
    public $timestamps = true;

    /**
     * Le nom de la table associée au modèle.
     */
    protected $table = 'contrat_documents';

    /**
     * La clé primaire de la table.
     */
    protected $primaryKey = 'id_contrat_document';

    /**
     * Les attributs qui sont assignables en masse.
     */
    protected $fillable = [
        'id_contrat',
        'id_lot',
        'id_document_type',
        'id_frais',
        'montant',
        'statut_paiement',
        'statut_document',
        'date_paiement',
        'reference_paiement',
        'fichier_path',
        'date_disponible',
        'date_remis',
        'notes',
        'valeursupprimee'
    ];

    /**
     * Les attributs qui doivent être castés.
     */
    protected $casts = [
        'montant' => 'decimal:2',
        'date_paiement' => 'date',
        'date_disponible' => 'date',
        'date_remis' => 'date',
        'valeursupprimee' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Les attributs avec leurs valeurs par défaut.
     */
    protected $attributes = [
        'statut_paiement' => 'non_paye',
        'statut_document' => 'en_attente',
        'valeursupprimee' => 0
    ];

    /**
     * Les attributs à ajouter automatiquement aux réponses JSON.
     */
    protected $appends = [
        'statut_paiement_label',
        'statut_document_label',
        'montant_formatted',
        'date_paiement_formatted',
        'date_disponible_formatted',
        'date_remis_formatted',
        'document_type_nom',
        'est_paye',
        'est_disponible',
        'est_remis'
    ];

    /**
     * Relation avec le contrat
     */
    public function contrat()
    {
        return $this->belongsTo(Contrat::class, 'id_contrat', 'id_contrat');
    }

    /**
     * Relation avec le lot
     */
    public function lot()
    {
        return $this->belongsTo(Lot::class, 'id_lot', 'id_lot');
    }

    /**
     * Relation avec le type de document
     */
    public function documentType()
    {
        return $this->belongsTo(DocumentType::class, 'id_document_type', 'id_document_type');
    }

    /**
     * Relation avec les frais
     */
    public function frais()
    {
        return $this->belongsTo(VilleDocumentFrais::class, 'id_frais', 'id_frais');
    }

    /**
     * Relation avec les paiements (pour les frais de document)
     */
    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'id_contrat_document', 'id_contrat_document')
                    ->where('type_paiement', 'FRAIS_DOCUMENT')
                    ->where('valeursupprimee', 0);
    }

    /**
     * Accesseur pour le libellé du statut paiement
     */
    public function getStatutPaiementLabelAttribute()
    {
        $statuts = [
            'non_paye' => 'Non payé',
            'partiel' => 'Partiellement payé',
            'paye' => 'Payé'
        ];
        return $statuts[$this->statut_paiement] ?? $this->statut_paiement;
    }

    /**
     * Accesseur pour le libellé du statut document
     */
    public function getStatutDocumentLabelAttribute()
    {
        $statuts = [
            'en_attente' => 'En attente',
            'paye' => 'Payé (en attente disponibilité)',
            'disponible' => 'Disponible',
            'remis' => 'Remis au client'
        ];
        return $statuts[$this->statut_document] ?? $this->statut_document;
    }

    /**
     * Accesseur pour le montant formaté
     */
    public function getMontantFormattedAttribute()
    {
        $montant = floatval($this->montant ?? 0);
        if ($montant <= 0) {
            return '0 FCFA';
        }
        return number_format($montant, 0, ',', ' ') . ' FCFA';
    }

    /**
     * Accesseur pour le reste à payer sur ce document (montant - paiements déjà validés)
     */
    public function getResteAPayerAttribute()
    {
        $paye = $this->paiements()->where('statut', 'VALIDE')->sum('montant');
        return max(0, floatval($this->montant ?? 0) - floatval($paye));
    }

    /**
     * Accesseur pour le reste à payer formaté
     */
    public function getResteAPayerFormattedAttribute()
    {
        $reste = $this->reste_a_payer;
        if ($reste <= 0) {
            return '0 FCFA';
        }
        return number_format($reste, 0, ',', ' ') . ' FCFA';
    }

    /**
     * Accesseur pour la date de paiement formatée
     */
    public function getDatePaiementFormattedAttribute()
    {
        if (!$this->date_paiement) {
            return '-';
        }
        return $this->date_paiement instanceof \DateTime
            ? $this->date_paiement->format('d/m/Y')
            : date('d/m/Y', strtotime($this->date_paiement));
    }

    /**
     * Accesseur pour la date disponible formatée
     */
    public function getDateDisponibleFormattedAttribute()
    {
        if (!$this->date_disponible) {
            return '-';
        }
        return $this->date_disponible instanceof \DateTime
            ? $this->date_disponible->format('d/m/Y')
            : date('d/m/Y', strtotime($this->date_disponible));
    }

    /**
     * Accesseur pour la date remis formatée
     */
    public function getDateRemisFormattedAttribute()
    {
        if (!$this->date_remis) {
            return '-';
        }
        return $this->date_remis instanceof \DateTime
            ? $this->date_remis->format('d/m/Y')
            : date('d/m/Y', strtotime($this->date_remis));
    }

    /**
     * Accesseur pour le nom du type de document
     */
    public function getDocumentTypeNomAttribute()
    {
        if ($this->relationLoaded('documentType') && $this->documentType) {
            return $this->documentType->nom;
        }
        if ($this->id_document_type) {
            $docType = DocumentType::find($this->id_document_type);
            return $docType ? $docType->nom : null;
        }
        return null;
    }

    /**
     * Vérifier si le document est payé
     */
    public function getEstPayeAttribute()
    {
        return $this->statut_paiement === 'paye';
    }

    /**
     * Vérifier si le document est disponible
     */
    public function getEstDisponibleAttribute()
    {
        return $this->statut_document === 'disponible' || $this->statut_document === 'remis';
    }

    /**
     * Vérifier si le document est remis
     */
    public function getEstRemisAttribute()
    {
        return $this->statut_document === 'remis';
    }

    /**
     * Marquer le document comme payé
     */
    public function marquerPaye($reference = null)
    {
        $this->statut_paiement = 'paye';
        $this->statut_document = 'paye';
        $this->date_paiement = now();
        if ($reference) {
            $this->reference_paiement = $reference;
        }
        $this->save();

        Log::info('Document de contrat marqué comme payé', [
            'contrat_document_id' => $this->id_contrat_document,
            'contrat_id' => $this->id_contrat,
            'reference' => $reference
        ]);

        return $this;
    }

    /**
     * Marquer le document comme disponible
     */
    public function marquerDisponible()
    {
        $this->statut_document = 'disponible';
        $this->date_disponible = now();
        $this->save();

        Log::info('Document de contrat marqué comme disponible', [
            'contrat_document_id' => $this->id_contrat_document,
            'contrat_id' => $this->id_contrat
        ]);

        if ($this->contrat && $this->contrat->client) {
            $this->contrat->client->notify(new PortailDocumentDisponibleNotification($this));
        }

        return $this;
    }

    /**
     * Marquer le document comme remis au client
     */
    public function marquerRemis()
    {
        $this->statut_document = 'remis';
        $this->date_remis = now();
        $this->save();

        Log::info('Document de contrat marqué comme remis au client', [
            'contrat_document_id' => $this->id_contrat_document,
            'contrat_id' => $this->id_contrat
        ]);

        if ($this->contrat && $this->contrat->client) {
            $this->contrat->client->notify(new PortailDocumentRemisNotification($this));

            $nomDocument = $this->document_type_nom ?? 'Votre document';
            $message = "GSF - {$nomDocument} vous a été remis. Merci de votre confiance.";
            SendClientSmsJob::dispatch($this->contrat->client->contact, $message);
        }

        return $this;
    }

    /**
     * Marquer le document comme partiellement payé
     */
    public function marquerPartiel()
    {
        $this->statut_paiement = 'partiel';
        $this->save();

        return $this;
    }

    /**
     * Scope pour les documents non supprimés
     */
    public function scopeNotDeleted($query)
    {
        return $query->where(function($q) {
            $q->where('valeursupprimee', '!=', 1)
              ->orWhereNull('valeursupprimee');
        });
    }

    /**
     * Scope pour les documents à payer
     */
    public function scopeAPayer($query)
    {
        return $query->where('statut_paiement', 'non_paye');
    }

    /**
     * Scope pour les documents disponibles
     */
    public function scopeDisponibles($query)
    {
        return $query->where('statut_document', 'disponible');
    }

    /**
     * Scope pour les documents remis
     */
    public function scopeRemis($query)
    {
        return $query->where('statut_document', 'remis');
    }

    /**
     * Scope pour les documents d'un contrat spécifique
     */
    public function scopePourContrat($query, $idContrat)
    {
        return $query->where('id_contrat', $idContrat);
    }

    /**
     * Suppression logique
     */
    public function delete()
    {
        $this->valeursupprimee = 1;
        return $this->save();
    }
}
