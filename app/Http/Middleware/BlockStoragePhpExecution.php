<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockStoragePhpExecution
{
    /**
     * Block any request that tries to execute PHP files from storage paths.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();

        if (preg_match('/\.(php|phtml|php[3-8]|phar)$/i', $path)) {
            if (str_starts_with($path, 'storage/') || str_starts_with($path, 'uploads/')) {
                abort(403, 'Forbidden');
            }
        }

        return $next($request);
    }
}
