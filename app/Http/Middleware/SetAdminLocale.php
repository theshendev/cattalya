<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetAdminLocale
{
    public const LOCALE = 'fa';

    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale(self::LOCALE);
        Carbon::setLocale(self::LOCALE);

        return $next($request);
    }
}
