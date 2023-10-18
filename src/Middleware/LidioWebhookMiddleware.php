<?php

namespace Shotwn\LidioAPI\Middleware;

use Illuminate\Http\Request;
use Shotwn\LidioAPI\LidioAPI;

class LidioWebhookMiddleware
{

    public function handle($request, \Closure $next)
    {
        $lidio = new LidioAPI();
        $paymentNotification = $lidio->handleWebhook($request);

        // Inject paymentNotification into request
        $request->attributes->add(['paymentNotification' => $paymentNotification]);

        return $next($request);
    }
}
