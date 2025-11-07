<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('doctor.{doctorId}', function ($user, $doctorId) {
    return (int) $user->id === (int) $doctorId;
});