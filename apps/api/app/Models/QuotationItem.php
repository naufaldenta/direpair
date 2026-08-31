<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\QuotationItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class QuotationItem extends Model
{
    /** @use HasFactory<QuotationItemFactory> */
    use HasFactory;

    protected $fillable = [
        'quotation_id', 'type', 'label', 'description', 'quantity', 'unit_price', 'total', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:2'];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }
}
