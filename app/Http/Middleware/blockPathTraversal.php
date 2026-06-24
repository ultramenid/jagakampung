<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class blockPathTraversal
{
    /**
     * Reject path-traversal / absolute paths in UniSharp LFM request params.
     * ponytail: LFM crop/resize/jsonitems feed `img`/`working_dir` etc. straight
     * into absolute filesystem reads (CVE-2019-18393 surface). Normal LFM usage
     * only ever passes relative names ("1", "shares/photos", "foo.jpg"), so
     * blocking `..` and leading slashes is safe and closes the LFI until the
     * abandoned package is removed.
     */
    private const PARAMS = ['img', 'working_dir', 'folder', 'file', 'name', 'path', 'item'];

    public function handle(Request $request, Closure $next): Response
    {
        foreach (self::PARAMS as $param) {
            $value = $request->input($param);
            if (is_string($value) && (str_contains($value, '..') || str_starts_with($value, '/') || str_starts_with($value, '\\'))) {
                abort(400, 'Invalid path parameter.');
            }
        }

        return $next($request);
    }
}