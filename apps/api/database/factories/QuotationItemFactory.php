<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuotationItem>
 */
final class QuotationItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quotation_id' => Quotation::factory(),
            'type' => 'labor',
            'label' => 'Jasa repair',
            'description' => null,
            'quantity' => 1,
            'unit_price' => 350000,
            'total' => 350000,
            'sort_order' => 0,
        ];
    }
}
