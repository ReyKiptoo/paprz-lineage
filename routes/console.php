<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment('Foundations built on truth endure every storm.');
})->purpose('Display an inspiring quote');
