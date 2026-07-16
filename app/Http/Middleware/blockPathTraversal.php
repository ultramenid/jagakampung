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

    /**
     * Extensions a renamed file may keep. Blocks renaming an upload to
     * `.php` (RCE on nginx). ponytail: allowlist over mime sniff since the
     * `file` param may be relative and unreliable to compare here.
     */
    private const RENAME_EXT = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'bmp'];

    public function handle(Request $request, Closure $next): Response
    {
        // Original fixed-allowlist guard (kept for explicitness / abort code compat).
        foreach (self::PARAMS as $param) {
            $value = $request->input($param);
            if (is_string($value) && (str_contains($value, '..') || str_starts_with($value, '/') || str_starts_with($value, '\\'))) {
                abort(403, 'Invalid path parameter.');
            }
        }

        // Recurse over ALL inputs: any string value containing `..` or
        // starting with a slash is a traversal/absolute-path attempt.
        $this->rejectTraversal($request->all());

        // Rename hardening: block extension-swap RCE.
        $newName = $request->input('new_name');
        if (is_string($newName) && $newName !== '') {
            if (!preg_match('/^[A-Za-z0-9._-]+$/', $newName)) {
                abort(403, 'Invalid rename name.');
            }
            $ext = strtolower(pathinfo($newName, PATHINFO_EXTENSION));
            if ($ext === '' || !in_array($ext, self::RENAME_EXT, true)) {
                abort(403, 'Invalid rename extension.');
            }
        }

        return $next($request);
    }

    private function rejectTraversal(array $inputs): void
    {
        foreach ($inputs as $value) {
            if (is_string($value)) {
                if (str_contains($value, '..') || str_starts_with($value, '/') || str_starts_with($value, '\\')) {
                    abort(403, 'Invalid path parameter.');
                }
            } elseif (is_array($value)) {
                $this->rejectTraversal($value);
            }
        }
    }
}