<?php

use Illuminate\Support\Facades\Broadcast;

// Public channel — anyone can listen to live price updates
Broadcast::channel('crypto-prices', fn () => true);

// Private per-user channel for price alert notifications
Broadcast::channel('App.Models.User.{id}', fn ($user, $id) => (int) $user->id === (int) $id);
