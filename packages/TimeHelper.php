<?php

namespace Ricubai\PHPHelpers;

abstract class TimeHelper
{
    /**
     * Retrieves the current time based on specified type.
     *
     * @param string $type Type of time to retrieve. Accepts 'mysql', 'timestamp',
     *                       or PHP date format string (e.g. 'Y-m-d').
     * @return int|string Integer if $type is 'timestamp', string otherwise.
     */
    public static function current_time($type = 'mysql')
    {
        // Don't use non-GMT timestamp, unless you know the difference and really need to.
        if ('timestamp' === $type || 'U' === $type) {
            return time();
        }

        if ('mysql' === $type) {
            $type = 'Y-m-d H:i:s';
            return date($type, time());
        }
    }
}
