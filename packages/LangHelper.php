<?php

namespace Ricubai\PHPHelpers;

use \MO;

class LangHelper
{
    /**
     * Retrieve the translation of $text.
     * If there is no translation, or the text domain isn't loaded, the original text is returned.
     *
     * @param string $text Text to translate.
     * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
     *                       Default 'default'.
     * @return string Translated text.
     */
    public static function __($text, $domain = 'default')
    {
        return self::translate($text, $domain);
    }

    /**
     * Retrieve the translation of $text.
     * If there is no translation, or the text domain isn't loaded, the original text is returned.
     * *Note:* Don't use translate() directly, use __() or related functions.
     *
     * @param string $text Text to translate.
     * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
     *                       Default 'default'.
     * @return string Translated text
     */
    private static function translate($text, $domain = 'default')
    {
        $translations = get_translations_for_domain($domain);
        $translation = $translations->translate($text);
        return $translation;
    }

    /**
     * Load a .mo file into the text domain $domain.
     * If the text domain already exists, the translations will be merged. If both
     * sets have the same string, the translation from the original value will be taken.
     * On success, the .mo file will be placed in the $l10n global by $domain
     * and will be a MO object.
     *
     * @param string $domain Text domain. Unique identifier for retrieving translated strings.
     * @param string $mofile Path to the .mo file.
     * @return bool True on success, false on failure.
     *
     * @global MO[] $l10n An array of all currently loaded text domains.
     * @global MO[] $l10n_unloaded An array of all text domains that have been unloaded again.
     */
    public static function load_textdomain($domain, $mofile)
    {
        global $l10n, $l10n_unloaded;

        $l10n_unloaded = (array)$l10n_unloaded;

        if (!is_readable($mofile)) {
            return false;
        }

        $mo = new MO();
        if (!$mo->import_from_file($mofile)) {
            return false;
        }

        if (isset($l10n[$domain])) {
            $mo->merge_with($l10n[$domain]);
        }

        unset($l10n_unloaded[$domain]);

        $l10n[$domain] = &$mo;

        return true;
    }
}
