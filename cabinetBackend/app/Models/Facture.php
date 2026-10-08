<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facture extends Model
{
    protected $table = 'facture';

    protected $primaryKey = 'idFacture';

    protected $fillable = [
        'numeroFacture',
        'idPatient',
        'idConsultation',
        'dateFacturation',
        'montant',
        'statut',
    ];

    protected $casts = [
        'dateFacturation' => 'date',
        'montant' => 'decimal:2',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(
            Patient::class,
            'idPatient',
            'idPatient'
        );
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(
            Consultation::class,
            'idConsultation',
            'idConsultation'
        );
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(
            Paiement::class,
            'idFacture',
            'idFacture'
        );
    }
}
