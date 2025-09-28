<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Reservation;
use App\Models\Passenger;
use App\Models\Notification;

class ReservationRepository implements ReservationRepositoryInterface
{
    public function create(array $data)
    {
        return Reservation::create([
            'flight_number' => $data['flight_number'],
            'departure_time' => $data['departure_time'],
            'status' => 'PENDING',
        ]);
    }

    public function find($id)
    {
        return Reservation::with('passengers')->findOrFail($id);
    }

    public function all(array $filters)
    {
        $query = Reservation::query()->with('passengers');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date'])) {
            $query->whereDate('departure_time', $filters['date']);
        }

        return $query->get();
    }

    public function updateStatus($id, $status)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->update(['status' => $status]);

        return $reservation->fresh(['passengers']);
    }

    public function addPassenger($reservationId, array $passenger)
    {
        return Passenger::create([
            'reservation_id' => $reservationId,
            'name' => $passenger['name'],
        ]);
    }

    public function logNotification($eventType, $data)
    {
        return Notification::create([
            'event_type' => $eventType,
            'data' => $data,
        ]);
    }
}
