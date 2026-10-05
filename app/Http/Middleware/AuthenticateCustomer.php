<?php

namespace Masso\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Protege las rutas de cuenta de cliente (guard `customer`), separado del
 * middleware `auth` existente que solo entiende el guard `web` de staff.
 */
class AuthenticateCustomer
{
    public function handle($request, Closure $next)
    {
        if (!Auth::guard('customer')->check()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'No autenticado.'], 401);
            }

            // Ruta relativa (ver AccountController) para no perder el puerto
            // cuando se corre en uno distinto al del dominio configurado.
            return redirect(route('customer.login', [], false));
        }

        return $next($request);
    }
}
