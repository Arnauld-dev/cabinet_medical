<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Diagnostic extends Model
{
    protected $table = 'diagnostics';

    protected $primaryKey = 'codeDiagnostic';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['codeDiagnostic','nomDiagnostic','description',];


    public function consultations(): BelongsToMany
    {
        return $this->belongsToMany( Consultation::class,'consultation_diagnostic','codeDiagnostic','idConsultation','codeDiagnostic','idConsultation' )->withTimestamps();
    }
}
