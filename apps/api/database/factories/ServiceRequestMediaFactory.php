<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ServiceRequest;
use App\Models\ServiceRequestMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceRequestMedia>
 */
final class ServiceRequestMediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_request_id' => ServiceRequest::factory(),
            'disk' => 'private',
            'path' => 'service-requests/'.fake()->uuid().'/example.jpg',
            'original_name' => 'example.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 1024,
            'is_demo' => false,
        ];
    }
}
