<?php

/**
 * Add short alias for DataHelper.
 * Useful for using DataHelper class in theme files to avoid using class full path.
 */
if (!class_exists('DH')) {
    class_alias('\Ricubai\PHPHelpers\DataHelper', 'DH');
}

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
