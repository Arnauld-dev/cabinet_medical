<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Medicament extends Model
{
    protected $table = 'medicament';

    protected $primaryKey = 'codeMedicament';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'codeMedicament',
        'nomMedicament',
        'prix',
    ];

    protected $casts = [
        'prix' => 'decimal:2',
    ];

    public function prescriptions(): HasMany
    {
        return $this->hasMany(
            Prescription::class,
            'codeMedicament',
            'codeMedicament'
        );
    }

    public function stock(): HasOne
    {
        return $this->hasOne(
            StockMedicament::class,
            'codeMedicament',
            'codeMedicament'
        );
    }
}
