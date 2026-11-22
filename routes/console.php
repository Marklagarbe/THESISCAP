<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('thesiscap:remind', function () {
    \App\Models\User::where('is_active', true)->each(fn ($u) => \App\Support\Reminders::forUser($u));
    $this->info('Reminders created.');
})->purpose('Create due-soon and overdue reminders for every user');
