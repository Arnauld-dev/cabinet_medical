<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RendezVous extends Model
{
    protected $table = 'rendevous';

    protected $primaryKey = 'idRendeVous';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'idPatient',
        'idMedecin',
        'dateHeure',
    ];

    protected $casts = [
        'dateHeure' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(
            Patient::class,
            'idPatient',
            'idPatient'
        );
    }

    public function medecin(): BelongsTo
    {
        return $this->belongsTo(
            Medecin::class,
            'idMedecin',
            'idMedecin'
        );
    }

    public function consultation(): HasOne
    {
        return $this->hasOne(
            Consultation::class,
            'idRendeVous',
            'idRendeVous'
        );
    }
}
