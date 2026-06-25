<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (auth()->user()->status === 'nonaktif') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'Akun Anda nonaktif. Silakan hubungi admin.']);
        }

        $user = auth()->user();
        $matchedRole = collect($roles)->first(fn($role) => $user->hasRoleAccess($role));

        if (!$matchedRole) {
            abort(403, 'Akses ditolak.');
        }

        if (!in_array($user->activeRole(), $roles, true)) {
            $request->session()->put('active_role', $matchedRole);
        }

        return $next($request);
    }
}
