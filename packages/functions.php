<?php

use Ricubai\PHPHelpers\LangHelper;
use Ricubai\PHPHelpers\DataHelper;

/**
 * Add short alias for DataHelper.
 * Useful for using DataHelper class in theme files to avoid using class full path.
 */
if (!class_exists('DH')) {
    class_alias('\Ricubai\PHPHelpers\DataHelper', 'DH');
}

/**
 * Add short alias for TemplateHelper.
 * Useful for using TemplateHelper class in theme files to avoid using class full path.
 */
if (!class_exists('TPL')) {
    class_alias('\Ricubai\PHPHelpers\TemplateHelper', 'TPL');
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

if (!function_exists('__')):
    /**
     * Return translated text.
     *
     * @param string $text Text to translate.
     * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
     *                       Default 'default'.
     */
    function __($text, $domain = 'default')
    {
        return LangHelper::__($text, $domain);
    }
endif;

if (!function_exists('_e')):
    /**
     * Display translated text.
     *
     * @param string $text Text to translate.
     * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
     *                       Default 'default'.
     */
    function _e($text, $domain = 'default')
    {
        echo LangHelper::__($text, $domain);
    }
endif;

if (!function_exists('_x')):
    /**
     * Retrieve translated string with gettext context.
     * Short alias for LangHelper::_x().
     *
     * @param string $text Text to translate.
     * @param string $context Context information for the translators.
     * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
     *                        Default 'default'.
     * @return string Translated context string without pipe.
     */
    function _x($text, $context, $domain = 'default')
    {
        return LangHelper::_x($text, $context, $domain);
    }
endif;

if (!function_exists('esc_html_e')):
    /**
     * Display translated text that has been escaped for safe use in HTML output.
     *
     * @param string $text Text to translate.
     * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
     *                       Default 'default'.
     * @since 2.8.0
     *
     */
    function esc_html_e($text, $domain = 'default')
    {
        echo DataHelper::esc_html(
            LangHelper::__($text, $domain)
        );
    }
endif;

if (!function_exists('esc_attr_e')):
    /**
     * Display translated text that has been escaped for safe use in an attribute.
     *
     * @param string $text Text to translate.
     * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
     *                       Default 'default'.
     * @since 2.8.0
     *
     */
    function esc_attr_e($text, $domain = 'default')
    {
        echo DataHelper::esc_attr(
            LangHelper::__($text, $domain)
        );
    }
endif;
