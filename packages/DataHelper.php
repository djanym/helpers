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
     * Escapes data for use in a MySQL query.
     *
     * Usually you should prepare queries using wpdb::prepare().
     * Sometimes, spot-escaping is required or useful. One example
     * is preparing an array for use in an IN clause.
     *
     * NOTE: Since 4.8.3, '%' characters will be replaced with a placeholder string,
     * this prevents certain SQLi attacks from taking place. This change in behaviour
     * may cause issues for code that expects the return value of esc_sql() to be useable
     * for other purposes.
     *
     * @param string|array $data Unescaped data
     * @return string|array Escaped data
     */
    public static function esc_sql($data, $con)
    {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                if (is_array($v)) {
                    $data[$k] = self::esc_sql($v);
                } else {
                    $data[$k] = mysqli_real_escape_string($con, $v);
                }
            }
        } else {
            $data = mysqli_real_escape_string($con, $data);
        }

        return $data;
    }

    /**
     * Serialize data, if needed.
     *
     * @param string|array|object $data Data that might be serialized.
     * @return mixed A scalar data
     * @since 2.0.5
     *
     */
    public static function maybe_serialize($data)
    {
        if (is_array($data) || is_object($data)) {
            return serialize($data);
        }

        // Double serialization is required for backward compatibility.
        // See https://core.trac.wordpress.org/ticket/12930
        // Also the world will end. See WP 3.6.1.
//        if (self::is_serialized($data, false)) {
//            return serialize($data);
//        }

        return $data;
    }

    /**
     * Unserialize value only if it was serialized.
     *
     * @param string $original Maybe unserialized original, if is needed.
     * @return mixed Unserialized data can be any type.
     */
    public static function maybe_unserialize($original)
    {
        if (self::is_serialized($original)) { // don't attempt to unserialize data that wasn't serialized going in
            return @unserialize($original, ['allowed_classes' => []]);
        }
        return $original;
    }

    /**
     * Get hash of given string.
     *
     * @param string $data Plain text to hash
     * @param string $scheme Authentication scheme (secure_auth, logged_in, nonce)
     * @return string Hash of $data
     */
    public static function get_hash($data, $scheme = 'secure_auth')
    {
        $salt = self::get_salt($scheme);
        return hash_hmac('md5', $data, $salt);
    }

    /**
     * Returns a salt to add to hashes.
     *
     * Salting passwords helps against tools which has stored hashed values of
     * common dictionary strings. The added values makes it harder to crack.
     *
     * @param string $scheme Authentication scheme (auth, secure_auth, logged_in, nonce)
     * @return string Salt value
     */
    public static function get_salt($scheme = 'secure_auth')
    {
        $const_key = strtoupper("{$scheme}_key");
        $const_salt = strtoupper("{$scheme}_salt");
        return constant($const_key) . constant($const_salt);
    }

    /**
     * Generates a random number.
     *
     * @param int $min Lower limit for the generated number
     * @param int $max Upper limit for the generated number
     * @return int A random number between min and max
     *
     * @global string $rnd_value
     * @staticvar string $seed
     * @staticvar bool $use_random_int_functionality
     */
    public static function rand($min = 0, $max = 0)
    {
        global $rnd_value;

        // Some misconfigured 32bit environments (Entropy PHP, for example) truncate integers larger than PHP_INT_MAX to PHP_INT_MAX rather than overflowing them to floats.
        $max_random_number = 3000000000 === 2147483647 ? (float)'4294967295' : 4294967295; // 4294967295 = 0xffffffff

        // We only handle Ints, floats are truncated to their integer value.
        $min = (int)$min;
        $max = (int)$max;

        // Use PHP's CSPRNG, or a compatible method
        static $use_random_int_functionality = true;
        if ($use_random_int_functionality) {
            try {
                $_max = (0 != $max) ? $max : $max_random_number;
                // \DH::rand() can accept arguments in either order, PHP cannot.
                $_max = max($min, $_max);
                $_min = min($min, $_max);
                $val = random_int($_min, $_max);
                if (false !== $val) {
                    return absint($val);
                } else {
                    $use_random_int_functionality = false;
                }
            } catch (Error $e) {
                $use_random_int_functionality = false;
            } catch (Exception $e) {
                $use_random_int_functionality = false;
            }
        }

        // Reset $rnd_value after 14 uses
        // 32(md5) + 40(sha1) + 40(sha1) / 8 = 14 random numbers from $rnd_value
        if (strlen($rnd_value) < 8) {
            static $seed = '';
            $rnd_value = md5(uniqid(microtime() . mt_rand(), true) . $seed);
            $rnd_value .= sha1($rnd_value);
            $rnd_value .= sha1($rnd_value . $seed);
            $seed = md5($seed . $rnd_value);
        }

        // Take the first 8 digits for our value
        $value = substr($rnd_value, 0, 8);

        // Strip the first eight, leaving the remainder for the next call to \DH::rand().
        $rnd_value = substr($rnd_value, 8);

        $value = abs(hexdec($value));

        // Reduce the value to be within the min - max range
        if ($max !== 0) {
            $value = $min + ($max - $min + 1) * $value / ($max_random_number + 1);
        }

        return abs((int)$value);
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

    /**
     * Check value to find if it was serialized.
     *
     * If $data is not an string, then returned value will always be false.
     * Serialized data is always a string.
     *
     * @param string $data Value to check to see if was serialized.
     * @param bool $strict Optional. Whether to be strict about the end of the string. Default true.
     * @return bool False if not serialized and true if it was.
     */
    private static function is_serialized($data, $strict = true)
    {
        // if it isn't a string, it isn't serialized.
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        if (strlen($data) < 4) {
            return false;
        }
        if (':' !== $data[1]) {
            return false;
        }
        if ($strict) {
            $lastc = substr($data, -1);
            if (';' !== $lastc && '}' !== $lastc) {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace = strpos($data, '}');
            // Either ; or } must exist.
            if (false === $semicolon && false === $brace) {
                return false;
            }
            // But neither must be in the first X characters.
            if (false !== $semicolon && $semicolon < 3) {
                return false;
            }
            if (false !== $brace && $brace < 4) {
                return false;
            }
        }
        $token = $data[0];
        switch ($token) {
            case 's':
                if ($strict) {
                    if ('"' !== substr($data, -2, 1)) {
                        return false;
                    }
                } elseif (false === strpos($data, '"')) {
                    return false;
                }
            // or else fall through
            case 'a':
            case 'O':
                return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';
                return (bool)preg_match("/^{$token}:[0-9.E+-]+;$end/", $data);
        }
        return false;
    }
}
