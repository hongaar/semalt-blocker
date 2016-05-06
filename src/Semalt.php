<?php

namespace Nabble;

/**
 * Only here for historical reasons.
 *
 * @deprecated Use \Nabble\SemaltBlocker\Blocker instead
 */
class Semalt
{
    /**
     * @deprecated Use \Nabble\SemaltBlocker\Blocker instead
     *
     * @param string $action If empty, send 403 response; if URL, redirect here; if non-empty string, print message
     */
    public static function block($action = '')
    {
        SemaltBlocker\Blocker::protect($action);
    }
}
