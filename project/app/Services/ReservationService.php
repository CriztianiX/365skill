<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\ReservationRepositoryInterface;

class ReservationService
{
    protected $repo;

    public function __construct(ReservationRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function createReservation(array $data)
    {
        $reservation = $this->repo->create($data);
        foreach ($data['passengers'] as $passenger) {
            $this->repo->addPassenger($reservation->id, $passenger);
        }

        return $this->repo->find($reservation->id);
    }

    public function updateStatus($id, $status)
    {
        $reservation = $this->repo->updateStatus($id, $status);
        $this->repo->logNotification('reservation.updated', $reservation);

        // TODO, usar el facade
        $redis = new \Predis\Client([
            'scheme' => 'tcp',
            'host'   => env('REDIS_HOST', 'redis'),
            'port'   => env('REDIS_PORT', 6379),
        ]);

        $redis->publish('reservation-events', json_encode([
            'event' => 'reservation.updated',
            'data' => $reservation
        ]));

        return $reservation;
    }

    public function getReservations(array $filters)
    {
        return $this->repo->all($filters);
    }
}
