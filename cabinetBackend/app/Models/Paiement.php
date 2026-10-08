<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paiement extends Model
{
    protected $table = 'paiement';

    protected $primaryKey = 'idPaiement';

    protected $fillable = [
        'idFacture',
        'datePaiement',
        'montant',
        'modePaiement',
    ];

    protected $casts = [
        'datePaiement' => 'date',
        'montant' => 'decimal:2',
    ];

    public function facture(): BelongsTo
    {
        return $this->belongsTo(
            Facture::class,
            'idFacture',
            'idFacture'
        );
    }
}
