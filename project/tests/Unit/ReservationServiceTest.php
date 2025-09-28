<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Services\ReservationService;
use App\Repositories\ReservationRepositoryInterface;
use Mockery;
use Tests\TestCase;

class ReservationServiceTest extends TestCase
{
    public function testCreateReservation()
    {
        $repo = Mockery::mock(ReservationRepositoryInterface::class);
        $repo->shouldReceive('create')->andReturn((object)['id' => 1]);
        $repo->shouldReceive('addPassenger')->twice();
        $repo->shouldReceive('find')->andReturn((object)['id' => 1, 'passengers' => []]);

        $service = new ReservationService($repo);
        $data = ['flight_number' => 'SK123', 'departure_time' => '2025-10-01 10:00', 'passengers' => [['name' => 'John'], ['name' => 'Jane']]];
        $result = $service->createReservation($data);

        $this->assertEquals(1, $result->id);
    }
}
