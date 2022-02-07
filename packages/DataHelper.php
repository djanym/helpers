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
                    if ($rule_key === 'label') {
                        $v->labels([$field_key => $rule_value]);
                    } elseif ($rule_key === 'allowed_values') {
                        $v->rule('in', $field_key, $rule_value);
                    } else {
                        $v->rule($rule_key, $field_key, $rule_value);
                    }
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
     * Checks and cleans a URL.
     *
     * A number of characters are removed from the URL. If the URL is for displaying
     * (the default behaviour) ampersands are also replaced. The {@see 'clean_url'} filter
     * is applied to the returned cleaned URL.
     *
     * @param string $url The URL to be cleaned.
     * @param array $protocols Optional. An array of acceptable protocols.
     *                          Defaults to return value of allowed_protocols()
     * @param string $_context Private. Use esc_url_raw() for database usage.
     * @return string The cleaned $url after the {@see 'clean_url'} filter is applied.
     */
    public static function esc_url($url, $protocols = null, $_context = 'display')
    {
        $original_url = $url;

        if ('' == $url) {
            return $url;
        }

        $url = str_replace(' ', '%20', ltrim($url));
        $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\[\]\\x80-\\xff]|i', '', $url);

        if ('' === $url) {
            return $url;
        }

        if (0 !== stripos($url, 'mailto:')) {
            $strip = array('%0d', '%0a', '%0D', '%0A');
            $url = _deep_replace($strip, $url);
        }

        $url = str_replace(';//', '://', $url);
        /* If the URL doesn't appear to contain a scheme, we
         * presume it needs http:// prepended (unless a relative
         * link starting with /, # or ? or a php file).
         */
        if (strpos($url, ':') === false && !in_array($url[0], array('/', '#', '?')) &&
            !preg_match('/^[a-z0-9-]+?\.php/i', $url)) {
            $url = 'http://' . $url;
        }

        // Replace ampersands and single quotes only when displaying.
        if ('display' == $_context) {
            $url = self::kses_normalize_entities($url);
            $url = str_replace(array('&amp;', "'"), array('&#038;', '&#039;'), $url);
        }

        if ((false !== strpos($url, '[')) || (false !== strpos($url, ']'))) {
            $parsed = wp_parse_url($url);
            $front = '';

            if (isset($parsed['scheme'])) {
                $front .= $parsed['scheme'] . '://';
            } elseif ('/' === $url[0]) {
                $front .= '//';
            }

            if (isset($parsed['user'])) {
                $front .= $parsed['user'];
            }

            if (isset($parsed['pass'])) {
                $front .= ':' . $parsed['pass'];
            }

            if (isset($parsed['user']) || isset($parsed['pass'])) {
                $front .= '@';
            }

            if (isset($parsed['host'])) {
                $front .= $parsed['host'];
            }

            if (isset($parsed['port'])) {
                $front .= ':' . $parsed['port'];
            }

            $end_dirty = str_replace($front, '', $url);
            $end_clean = str_replace(array('[', ']'), array('%5B', '%5D'), $end_dirty);
            $url = str_replace($end_dirty, $end_clean, $url);
        }

        if ('/' === $url[0]) {
            $good_protocol_url = $url;
        } else {
            if (!is_array($protocols)) {
                $protocols = array(
                    'http',
                    'https',
                    'ftp',
                    'ftps',
                    'mailto',
                    'news',
                    'irc',
                    'gopher',
                    'nntp',
                    'feed',
                    'telnet',
                    'mms',
                    'rtsp',
                    'sms',
                    'svn',
                    'tel',
                    'fax',
                    'xmpp',
                    'webcal',
                    'urn'
                );
            }
            $good_protocol_url = self::kses_bad_protocol($url, $protocols);
            if (strtolower($good_protocol_url) != strtolower($url)) {
                return '';
            }
        }

        /**
         * Filters a string cleaned and escaped for output as a URL.
         *
         * @param string $good_protocol_url The cleaned URL to be returned.
         * @param string $original_url The URL prior to cleaning.
         * @param string $_context If 'display', replace ampersands and single quotes only.
         * @since 2.3.0
         *
         */
        return apply_filters('clean_url', $good_protocol_url, $original_url, $_context);
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

    /**
     * Converts and fixes HTML entities.
     *
     * This function normalizes HTML entities. It will convert `AT&T` to the correct
     * `AT&amp;T`, `&#00058;` to `&#058;`, `&#XYZZY;` to `&amp;#XYZZY;` and so on.
     *
     * When `$context` is set to 'xml', HTML entities are converted to their code points.  For
     * example, `AT&T&hellip;&#XYZZY;` is converted to `AT&amp;T…&amp;#XYZZY;`.
     *
     * @param string $string Content to normalize entities.
     * @param string $context Context for normalization. Can be either 'html' or 'xml'.
     *                        Default 'html'.
     * @return string Content with normalized entities.
     */
    private static function kses_normalize_entities($string, $context = 'html')
    {
        // Disarm all entities by converting & to &amp;
        $string = str_replace('&', '&amp;', $string);

        // Change back the allowed entities in our list of allowed entities.
        if ('xml' === $context) {
            $string = preg_replace_callback('/&amp;([A-Za-z]{2,8}[0-9]{0,2});/', 'self::kses_xml_named_entities', $string);
        } else {
            $string = preg_replace_callback('/&amp;([A-Za-z]{2,8}[0-9]{0,2});/', 'self::kses_named_entities', $string);
        }
        $string = preg_replace_callback('/&amp;#(0*[0-9]{1,7});/', 'self::kses_normalize_entities2', $string);
        $string = preg_replace_callback('/&amp;#[Xx](0*[0-9A-Fa-f]{1,6});/', 'self::kses_normalize_entities3', $string);

        return $string;
    }

    /**
     * Callback for `kses_normalize_entities()` regular expression.
     *
     * This function only accepts valid named entity references, which are finite,
     * case-sensitive, and highly scrutinized by HTML and XML validators.
     *
     * @param array $matches preg_replace_callback() matches array.
     * @return string Correctly encoded entity.
     * @since 3.0.0
     *
     * @global array $allowedentitynames
     *
     */
    private static function kses_named_entities($matches)
    {
        global $allowedentitynames;

        if (empty($matches[1])) {
            return '';
        }

        $i = $matches[1];
        return (!in_array($i, $allowedentitynames, true)) ? "&amp;$i;" : "&$i;";
    }

    /**
     * Callback for `kses_normalize_entities()` regular expression.
     *
     * This function only accepts valid named entity references, which are finite,
     * case-sensitive, and highly scrutinized by XML validators.  HTML named entity
     * references are converted to their code points.
     *
     * @param array $matches preg_replace_callback() matches array.
     * @return string Correctly encoded entity.
     * @global array $allowedxmlnamedentities
     *
     * @since 5.5.0
     *
     * @global array $allowedentitynames
     */
    private static function kses_xml_named_entities($matches)
    {
        global $allowedentitynames, $allowedxmlnamedentities;

        if (empty($matches[1])) {
            return '';
        }

        $i = $matches[1];

        if (in_array($i, $allowedxmlnamedentities, true)) {
            return "&$i;";
        } elseif (in_array($i, $allowedentitynames, true)) {
            return html_entity_decode("&$i;", ENT_HTML5);
        }

        return "&amp;$i;";
    }

    /**
     * Callback for `kses_normalize_entities()` regular expression.
     *
     * This function helps `kses_normalize_entities()` to only accept 16-bit
     * values and nothing more for `&#number;` entities.
     *
     * @access private
     * @param array $matches `preg_replace_callback()` matches array.
     * @return string Correctly encoded entity.
     * @ignore
     * @since 1.0.0
     *
     */
    private static function kses_normalize_entities2($matches)
    {
        if (empty($matches[1])) {
            return '';
        }

        $i = $matches[1];
        if (valid_unicode($i)) {
            $i = str_pad(ltrim($i, '0'), 3, '0', STR_PAD_LEFT);
            $i = "&#$i;";
        } else {
            $i = "&amp;#$i;";
        }

        return $i;
    }

    /**
     * Callback for `kses_normalize_entities()` for regular expression.
     *
     * This function helps `kses_normalize_entities()` to only accept valid Unicode
     * numeric entities in hex form.
     *
     * @param array $matches `preg_replace_callback()` matches array.
     * @return string Correctly encoded entity.
     * @since 2.7.0
     * @access private
     * @ignore
     *
     */
    private static function kses_normalize_entities3($matches)
    {
        if (empty($matches[1])) {
            return '';
        }

        $hexchars = $matches[1];
        return (!valid_unicode(hexdec($hexchars))) ? "&amp;#x$hexchars;" : '&#x' . ltrim($hexchars, '0') . ';';
    }

    /**
     * Sanitizes a string and removed disallowed URL protocols.
     *
     * This function removes all non-allowed protocols from the beginning of the
     * string. It ignores whitespace and the case of the letters, and it does
     * understand HTML entities. It does its work recursively, so it won't be
     * fooled by a string like `javascript:javascript:alert(57)`.
     *
     * @param string $string Content to filter bad protocols from.
     * @param string[] $allowed_protocols Array of allowed URL protocols.
     * @return string Filtered content.
     */
    private static function kses_bad_protocol($string, $allowed_protocols)
    {
        $string = self::kses_no_null($string);
        $iterations = 0;

        do {
            $original_string = $string;
            $string = self::kses_bad_protocol_once($string, $allowed_protocols);
        } while ($original_string != $string && ++$iterations < 6);

        if ($original_string != $string) {
            return '';
        }

        return $string;
    }

    /**
     * Sanitizes content from bad protocols and other characters.
     *
     * This function searches for URL protocols at the beginning of the string, while
     * handling whitespace and HTML entities.
     *
     * @param string $string Content to check for bad protocols.
     * @param string[] $allowed_protocols Array of allowed URL protocols.
     * @param int $count Depth of call recursion to this function.
     * @return string Sanitized content.
     */
    private static function kses_bad_protocol_once($string, $allowed_protocols, $count = 1)
    {
        $string = preg_replace('/(&#0*58(?![;0-9])|&#x0*3a(?![;a-f0-9]))/i', '$1;', $string);
        $string2 = preg_split('/:|&#0*58;|&#x0*3a;|&colon;/i', $string, 2);
        if (isset($string2[1]) && !preg_match('%/\?%', $string2[0])) {
            $string = trim($string2[1]);
            $protocol = self::kses_bad_protocol_once2($string2[0], $allowed_protocols);
            if ('feed:' === $protocol) {
                if ($count > 2) {
                    return '';
                }
                $string = self::kses_bad_protocol_once($string, $allowed_protocols, ++$count);
                if (empty($string)) {
                    return $string;
                }
            }
            $string = $protocol . $string;
        }

        return $string;
    }

    /**
     * Callback for `kses_bad_protocol_once()` regular expression.
     *
     * This function processes URL protocols, checks to see if they're in the
     * list of allowed protocols or not, and returns different data depending
     * on the answer.
     *
     * @access private
     *
     * @param string $string URI scheme to check against the list of allowed protocols.
     * @param string[] $allowed_protocols Array of allowed URL protocols.
     * @return string Sanitized content.
     */
    private static function kses_bad_protocol_once2($string, $allowed_protocols)
    {
        $string2 = self::kses_decode_entities($string);
        $string2 = preg_replace('/\s/', '', $string2);
        $string2 = self::kses_no_null($string2);
        $string2 = strtolower($string2);

        $allowed = false;
        foreach ((array)$allowed_protocols as $one_protocol) {
            if (strtolower($one_protocol) == $string2) {
                $allowed = true;
                break;
            }
        }

        if ($allowed) {
            return "$string2:";
        } else {
            return '';
        }
    }

    /**
     * Removes any invalid control characters in a text string.
     *
     * Also removes any instance of the `\0` string.
     *
     * @param string $string Content to filter null characters from.
     * @param array $options Set 'slash_zero' => 'keep' when '\0' is allowed. Default is 'remove'.
     * @return string Filtered content.
     */
    private static function kses_no_null($string, $options = null)
    {
        if (!isset($options['slash_zero'])) {
            $options = array('slash_zero' => 'remove');
        }

        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $string);
        if ('remove' === $options['slash_zero']) {
            $string = preg_replace('/\\\\+0+/', '', $string);
        }

        return $string;
    }

    /**
     * Converts all numeric HTML entities to their named counterparts.
     *
     * This function decodes numeric HTML entities (`&#65;` and `&#x41;`).
     * It doesn't do anything with named entities like `&auml;`, but we don't
     * need them in the allowed URL protocols system anyway.
     *
     * @param string $string Content to change entities.
     * @return string Content after decoded entities.
     * @since 1.0.0
     *
     */
    private static function kses_decode_entities($string)
    {
        $string = preg_replace_callback('/&#([0-9]+);/', 'self::kses_decode_entities_chr', $string);
        $string = preg_replace_callback('/&#[Xx]([0-9A-Fa-f]+);/', 'self::kses_decode_entities_chr_hexdec', $string);

        return $string;
    }

    /**
     * Regex callback for `wp_kses_decode_entities()`.
     *
     * @access private
     *
     * @param array $match preg match
     * @return string
     */
    private static function kses_decode_entities_chr($match)
    {
        return chr($match[1]);
    }

    /**
     * Regex callback for `wp_kses_decode_entities()`.
     *
     * @access private
     *
     * @param array $match preg match
     * @return string
     */
    private static function kses_decode_entities_chr_hexdec($match)
    {
        return chr(hexdec($match[1]));
    }
}
