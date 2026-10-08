<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prescription extends Model
{
    protected $table = 'prescriptions';

    protected $primaryKey = 'idPrescription';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'idPrescription',
        'idConsultation',
        'codeMedicament',
        'posologie',
        'dureeTraitement',
    ];

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(
            Consultation::class,
            'idConsultation',
            'idConsultation'
        );
    }

    public function medicament(): BelongsTo
    {
        return $this->belongsTo(
            Medicament::class,
            'codeMedicament',
            'codeMedicament'
        );
    }
}
