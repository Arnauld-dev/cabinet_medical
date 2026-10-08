<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MouvementStock extends Model
{
    protected $table = 'mouvement_stock';

    protected $primaryKey = 'idMouvement';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'idMouvement',
        'idStock',
        'typeMouvement',
        'quantite',
        'dateMouvement',
    ];

    protected $casts = [
        'quantite' => 'integer',
        'dateMouvement' => 'date',
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(
            StockMedicament::class,
            'idStock',
            'idStock'
        );
    }
}
