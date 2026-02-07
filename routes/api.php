<?php

use Illuminate\Support\Facades\Route;

Route::post('/stripe/webhook', \App\Http\Controllers\Api\StripeWebhookController::class)
    ->name('stripe.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

include("api/articles.php");
include("api/commerce.php");
include("api/fleet.php");
include("api/hr.php");
include("api/ged.php");
include("api/expense.php");
include("api/payroll.php");
include("api/intervention.php");
include("api/banque.php");
include("api/gpao.php");

