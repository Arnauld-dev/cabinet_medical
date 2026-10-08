<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyseLaboratoire extends Model
{
    protected $table = 'analyse_laboratoire';

    protected $primaryKey = 'idAnalyse';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'idAnalyse',
        'idPatient',
        'idConsultation',
        'typeAnalyse',
        'datePrescription',
        'laboratoire',
        'resultat',
        'dateResultat',
    ];

    protected $casts = [
        'datePrescription' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo( Patient::class,'idPatient','idPatient');
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo( Consultation::class, 'idConsultation', 'idConsultation');
    }
}
