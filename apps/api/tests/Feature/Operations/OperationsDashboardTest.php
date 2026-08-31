<?php

declare(strict_types=1);

namespace Tests\Feature\Operations;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

final class OperationsDashboardTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_operations_dashboard_requires_authentication(): void
    {
        $this->get('/operations')->assertRedirect('/operations/login');
    }

    public function test_authenticated_staff_can_view_request_details(): void
    {
        $user = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create();

        $this->actingAs($user)
            ->get(route('operations.requests.show', $serviceRequest))
            ->assertOk()
            ->assertSee($serviceRequest->public_number)
            ->assertSee($serviceRequest->customer->name);
    }
}
