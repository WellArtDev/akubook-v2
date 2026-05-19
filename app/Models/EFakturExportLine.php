<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EFakturExportLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'e_faktur_export_id',
        'faktur_pajak_id',
        'line_number',
        'faktur_number',
        'faktur_date',
        'customer_name',
        'customer_tax_id',
        'dpp',
        'ppn_amount',
        'grand_total',
    ];

    protected $casts = [
        'faktur_date' => 'date',
        'dpp' => 'decimal:2',
        'ppn_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'e_faktur_export_id' => 'integer',
        'faktur_pajak_id' => 'integer',
        'line_number' => 'integer',
    ];

    public function export(): BelongsTo
    {
        return $this->belongsTo(EFakturExport::class, 'e_faktur_export_id');
    }

    public function fakturPajak(): BelongsTo
    {
        return $this->belongsTo(FakturPajak::class);
    }
}
