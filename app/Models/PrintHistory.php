<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'print_draft_id',
        'document_type',
        'document_id',
        'dot_matrix_template_id',
        'printed_by',
        'printed_at',
        'output_metadata',
    ];

    protected $casts = [
        'printed_at' => 'datetime',
        'output_metadata' => 'array',
    ];

    public function draft(): BelongsTo
    {
        return $this->belongsTo(PrintDraft::class, 'print_draft_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(DotMatrixTemplate::class, 'dot_matrix_template_id');
    }

    public function printer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'printed_by');
    }
}
