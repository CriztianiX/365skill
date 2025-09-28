<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Services\ReservationService;
use Illuminate\Http\Request;

class ReservationsController extends Controller
{
    protected $service;

    public function __construct(ReservationService $service)
    {
        $this->service = $service;
    }

    public function store(StoreReservationRequest $request)
    {
        $data = $request->validated();
        $reservation = $this->service->createReservation($data);

        return response()->json($reservation, 201);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'date']);
        $reservations = $this->service->getReservations($filters);

        return response()->json($reservations);
    }

    public function updateStatus($id, Request $request)
    {
        $data = $request->validate(['status' => 'required|in:CONFIRMED,CANCELLED,CHECKED_IN']);
        $reservation = $this->service->updateStatus($id, $data['status']);

        return response()->json($reservation);
    }
}
