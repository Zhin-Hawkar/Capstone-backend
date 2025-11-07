<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewAppointmentRequest implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $appointment;
    public $doctorId;

    public function __construct($appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new Channel('doctor');
    }

  public function broadcastWith(): array
    {
        return [
            'id' => $this->appointment->id,
            'patient_name' => $this->appointment->firstName,
            'status' => $this->appointment->status,
            'time' => $this->appointment->date_time,
        ];
    }
  public function broadcastAs()
    {
        return "doctor-event";
    }
}
