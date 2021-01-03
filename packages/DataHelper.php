<?php

namespace Ricubai\PHPHelpers;

use Valitron\Validator;

class DataHelper
{
    /**
     * Validates a form $_POST data using $fields array.
     * In case of errors - returns an array in format:
     * array('fieldname' => ['message 1', 'message 2', ...], 'fieldname_2' => ['message 1', ...])
     * @param array $fields
     * @return true|array Returns bool true, or an array with errors.
     */
    public static function validateFormPost(array $fields)
    {
        $v = new Validator($_POST);
        // Process array with fields and rules as per Valitron\Validator format requirements.
        foreach ($fields as $field_key => $rules_array) {
            foreach ($rules_array as $rule_key => $rule_value) {
                // If rule has a value (like max length = 6)
                if (is_string($rule_key)) {
                    $v->rule($rule_key, $field_key, $rule_value);
                } else { // If rule without values (like required), then $rule_value is the $rule_key
                    $v->rule($rule_value, $field_key);
                }
            }
        }
        if ($v->validate()) {
            return true;
        } else {
            return $v->errors();
        }
        return true;
    }

    /**
     * Escapes HTML blocks.
     *
     * @param $text
     * @return string
     */
    public static function esc_html($text, $echo = true)
    {
        $safe_text = self::check_invalid_utf8($text);
        $safe_text = self::htmlspecialchars($safe_text, ENT_QUOTES);
        if ($echo) {
            echo $safe_text;
        } else {
            return $safe_text;
        }
    }

    /**
     * Escapes for HTML tag attributes.
     *
     * @param string $text
     * @return string
     */
    public static function esc_attr($text)
    {
        $safe_text = self::check_invalid_utf8($text);
        $safe_text = self::htmlspecialchars($safe_text, ENT_QUOTES);
        return $safe_text;
    }

    /**
     * Escapes for HTML textarea values.
     *
     * @param string $text
     * @return string
     */
    public static function esc_textarea($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Checks for invalid UTF8 in a string.
     *
     * @param string $string The text which is to be checked.
     * @return string The checked text.
     *
     * @staticvar bool $utf8_pcre
     */
    private static function check_invalid_utf8($string)
    {
        $string = (string)$string;

        if ($string === '') {
            return '';
        }

        // Check for support for utf8 in the installed PCRE library once and store the result in a static
        static $utf8_pcre = null;
        if (!isset($utf8_pcre)) {
            $utf8_pcre = @preg_match('/^./u', 'a');
        }

        // We can't demand utf8 in the PCRE installation, so just return the string in those cases
        if (!$utf8_pcre) {
            return $string;
        }

        // preg_match fails when it encounters invalid UTF8 in $string
        if (1 === @preg_match('/^./us', $string)) {
            return $string;
        }

        return '';
    }

    /**
     * Converts a number of special characters into their HTML entities.
     * Specifically deals with: &, <, >, ", and '.
     *
     * $quote_style can be set to ENT_COMPAT to encode " to
     * &quot;, or ENT_QUOTES to do both. Default is ENT_NOQUOTES where no quotes are encoded.
     *
     * @param string $string The text which is to be encoded.
     * @param int|string $quote_style Optional. Converts double quotes if set to ENT_COMPAT,
     *                                    both single and double if set to ENT_QUOTES or none if set to ENT_NOQUOTES.
     *                                    Check PHP manual for htmlspecialchars() function.
     *                                    Default is ENT_NOQUOTES.
     * @param false|string $charset Optional. The character encoding of the string. Default is false.
     * @param bool $double_encode Optional. Whether to encode existing html entities. Default is false.
     * @return string The encoded text with HTML entities.
     *
     * @staticvar string $_charset
     */
    private static function htmlspecialchars($string, $quote_style = ENT_NOQUOTES, $charset = 'UTF-8', $double_encode = false)
    {
        $string = (string)$string;

        if ($string === '') {
            return '';
        }

        // Skip if there are no specialchars
        if (!preg_match('/[&<>"\']/', $string)) {
            return $string;
        }

        // Account for the previous behaviour of the function when the $quote_style is not an accepted value
        if (empty($quote_style)) {
            $quote_style = ENT_NOQUOTES;
        } elseif (!in_array($quote_style, array(0, 2, 3, 'single', 'double'), true)) {
            $quote_style = ENT_QUOTES;
        }

        $string = htmlspecialchars($string, $quote_style, $charset, $double_encode);

        return $string;
    }
}
