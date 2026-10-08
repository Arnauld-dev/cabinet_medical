<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockMedicament extends Model
{
    protected $table = 'stock_medicament';

    protected $primaryKey = 'idStock';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'idStock',
        'codeMedicament',
        'quantite',
        'quantiteMin',
        'dateExpiration',
        'fournisseur',
        'emplacement',
    ];

    protected $casts = [
        'dateExpiration' => 'date',
    ];

    public function medicament(): BelongsTo
    {
        return $this->belongsTo(
            Medicament::class,
            'codeMedicament',
            'codeMedicament'
        );
    }

    public function mouvements(): HasMany
    {
        return $this->hasMany(
            MouvementStock::class,
            'idStock',
            'idStock'
        );
    }
}
