<?php
declare(strict_types=1);

namespace App\Repositories;

interface ReservationRepositoryInterface
{
    public function create(array $data);
    public function find($id);
    public function all(array $filters);
    public function updateStatus($id, $status);
    public function addPassenger($reservationId, array $passenger);
    public function logNotification($eventType, $data);
}
