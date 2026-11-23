<?php

use App\Providers\AppServiceProvider;
use Mccarlosen\LaravelMpdf\LaravelMpdfServiceProvider;
use Modules\Finishing\Providers\FinishingServiceProvider;

return [
    AppServiceProvider::class,
    FinishingServiceProvider::class,
    LaravelMpdfServiceProvider::class,
];
