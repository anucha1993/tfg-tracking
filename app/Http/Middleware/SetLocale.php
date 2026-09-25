<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED = ['th', 'en', 'zh'];

    public function handle(Request $request, Closure $next): Response
    {
        $requested = $request->query('lang');
        $locale = in_array($requested, self::SUPPORTED, true)
            ? $requested
            : ($request->cookie('lang') ?: 'th');

        if (!in_array($locale, self::SUPPORTED, true)) {
            $locale = 'th';
        }

        App::setLocale($locale);

        /** @var Response $response */
        $response = $next($request);

        if ($requested && $requested !== $request->cookie('lang')) {
            $response->headers->setCookie(Cookie::make('lang', $locale, 60 * 24 * 365));
        }

        return $response;
    }
}
