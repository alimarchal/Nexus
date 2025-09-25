<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AksicApplicationEducation extends Model
{
    /** @use HasFactory<\Database\Factories\AksicApplicationEducationFactory> */
    use HasFactory;

    protected $table = 'aksic_application_education';

    protected $fillable = [
        'aksic_application_id',
        'aksic_id',
        'applicant_id',
        'education_level',
        'degree_title',
        'institute',
        'passing_year',
        'grade_or_percentage',
        'educations_json',
    ];

    protected $casts = [
        'passing_year' => 'integer',
        'educations_json' => 'array',
    ];

    /**
     * Get the application that owns this education record.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(AksicApplication::class, 'aksic_application_id');
    }
}
