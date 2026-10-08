<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Consultation extends Model
{
    protected $table = 'consultations';

    protected $primaryKey = 'idConsultation';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'idConsultation',
        'idRendeVous',
        'dateConsultation',
    ];

    protected $casts = [
        'dateConsultation' => 'date',
    ];

    public function rendezVous(): BelongsTo
    {
        return $this->belongsTo( RendezVous::class, 'idRendeVous', 'idRendeVous' );
    }


    public function diagnostics(): BelongsToMany
    {
        return $this->belongsToMany( Diagnostic::class, 'consultation_diagnostic', 'idConsultation', 'codeDiagnostic','idConsultation','codeDiagnostic' )->withTimestamps();
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany( Prescription::class, 'idConsultation', 'idConsultation');
    }

    public function analysesLaboratoire(): HasMany
    {
        return $this->hasMany(
            AnalyseLaboratoire::class,'idConsultation', 'idConsultation' );
    }

    public function facture(): HasOne
    {
        return $this->hasOne( Facture::class, 'idConsultation', 'idConsultation' );
    }
}
