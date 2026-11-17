<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Picks the admin panel language from the session, then the query string.
 *
 * Arabic is the shop's primary language, so the panel opens in Arabic (RTL)
 * until a user chooses English. Filament renders the document direction from
 * the application locale, which is why this runs before the panel renders.
 */
class SetPanelLocale
{
    /**
     * Locales the panel has translations for.
     *
     * @var list<string>
     */
    public const SUPPORTED = ['ar', 'en'];

    public const DEFAULT = 'ar';

    public const SESSION_KEY = 'panel_locale';

    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale($this->resolve($request));

        return $next($request);
    }

    private function resolve(Request $request): string
    {
        $requested = $request->query('locale');

        if (is_string($requested) && in_array($requested, self::SUPPORTED, true)) {
            $request->session()->put(self::SESSION_KEY, $requested);

            return $requested;
        }

        $stored = $request->session()->get(self::SESSION_KEY);

        if (is_string($stored) && in_array($stored, self::SUPPORTED, true)) {
            return $stored;
        }

        return self::DEFAULT;
    }
}
