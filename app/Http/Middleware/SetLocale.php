<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    private const SUPPORTED = ['id', 'en'];

    public function handle(Request $request, Closure $next): mixed
    {
        $locale = $this->resolve($request);
        app()->setLocale($locale);

        return $next($request);
    }

    private function resolve(Request $request): string
    {
        // 1. Explicit user choice stored in session
        $fromSession = session('locale');
        if ($fromSession && in_array($fromSession, self::SUPPORTED, true)) {
            return $fromSession;
        }

        $isAdmin = str_starts_with($request->path(), 'admin');

        // 2. Browser Accept-Language (public only)
        if (! $isAdmin) {
            $browserLang = substr($request->header('Accept-Language', ''), 0, 2);
            if (in_array($browserLang, self::SUPPORTED, true)) {
                return $browserLang;
            }
        }

        // 3. Default
        return $isAdmin ? 'en' : 'id';
    }
}
