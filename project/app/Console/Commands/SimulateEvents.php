<?php

namespace App\Console\Commands;

use App\Services\ReservationService;
use Illuminate\Console\Command;

class SimulateEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:simulate-events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate events';

    /**
     * Execute the console command.
     */
    public function handle(ReservationService $service)
    {
        while (true) {
            $reservations = $service->getReservations([]);
            if ($reservations->isEmpty()) continue;

            $random = $reservations->random();
            $statuses = ['CONFIRMED', 'CANCELLED', 'CHECKED_IN'];
            $newStatus = $statuses[array_rand($statuses)];
            echo 'Changing status to ' . $newStatus . ' for id: ' . $random->id . "\n";
            $service->updateStatus($random->id, $newStatus);
            sleep(5);
        }
    }
}
