<?php

if (!function_exists('is_error')):
    /**
     * Check whether variable is a FormError.
     * Returns true if $thing is an object of the FormError class.
     * @param mixed $thing Check if unknown variable is a FormError object.
     * @return bool True, if FormError. False, if not eError.
     */
    function is_error($thing)
    {
        return ($thing instanceof \Ricubai\PHPHelpers\FormError);
    }
endif;

if (!function_exists('dh')):
    /**
     * Short alias for DataHelper class.
     */
function dh()
{
    return \Ricubai\PHPHelpers\DataHelper::class;
}
endif;
