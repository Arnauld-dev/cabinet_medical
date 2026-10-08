<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    protected $table = 'patients';

    protected $primaryKey = 'idPatient';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'idPatient',
        'nom',
        'prenom',
        'dateNaissance',
        'adresse',
        'telephone',
    ];

    protected $casts = [
        'dateNaissance' => 'date',
    ];

    public function rendezVous(): HasMany
    {
        return $this->hasMany(
            RendezVous::class,
            'idPatient',
            'idPatient'
        );
    }

    public function factures(): HasMany
    {
        return $this->hasMany(
            Facture::class,
            'idPatient',
            'idPatient'
        );
    }

    public function analysesLaboratoire(): HasMany
    {
        return $this->hasMany(
            AnalyseLaboratoire::class,
            'idPatient',
            'idPatient'
        );
    }
}
