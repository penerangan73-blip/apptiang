<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('about:pju', function () {
    $this->info('PJU Surveyor API is ready.');
})->purpose('Show PJU API status');
