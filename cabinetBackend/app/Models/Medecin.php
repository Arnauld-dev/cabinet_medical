<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medecin extends Model
{
    protected $table = 'medecins';

    protected $primaryKey = 'idMedecin';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'idMedecin',
        'nom',
        'prenom',
        'specialite',
    ];

    public function rendezVous(): HasMany
    {
        return $this->hasMany(
            RendezVous::class,
            'idMedecin',
            'idMedecin'
        );
    }
}
