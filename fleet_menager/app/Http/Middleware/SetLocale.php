<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ustawia język aplikacji na podstawie sesji ('locale').
 *
 * Wybór języka zapisywany jest przez LocaleController; jeśli sesja
 * nic nie ma, używamy locale skonfigurowanego w config/app.php.
 */
class SetLocale
{
    /** @var array<int, string> dozwolone języki */
    private const SUPPORTED = ['pl', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = (string) $request->session()->get('locale', config('app.locale'));

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = (string) config('app.locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
