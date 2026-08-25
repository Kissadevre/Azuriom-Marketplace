<?php

namespace Azuriom\Plugin\Marketplace\Http\Middleware;

use Azuriom\Plugin\Marketplace\Models\Restriction;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMarketplaceActionAllowed
{
    public function handle(Request $request, Closure $next, string $action): Response
    {
        abort_unless(in_array($action, Restriction::actions(), true), 500);

        $restriction = Restriction::activeFor($request->user(), $action);

        if ($restriction !== null) {
            abort(403, trans('marketplace::messages.restrictions.blocked', [
                'action' => trans('marketplace::admin.restrictions.actions.'.$action),
                'until' => $restriction->expires_at
                    ? trans('marketplace::messages.restrictions.until', [
                        'date' => format_date($restriction->expires_at, true),
                    ])
                    : trans('marketplace::messages.restrictions.indefinite'),
            ]));
        }

        return $next($request);
    }
}
