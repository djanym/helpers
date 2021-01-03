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
     * Retrieve translated string with gettext context.
     * Quite a few times, there will be collisions with similar translatable text
     * found in more than two places, but with different translated context.
     * By including the context in the pot file, translators can translate the two
     * strings differently.
     *
     * @param string $text Text to translate.
     * @param string $context Context information for the translators.
     * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
     *                        Default 'default'.
     * @return string Translated context string without pipe.
     */
    public static function _x($text, $context, $domain = 'default')
    {
        return self::translate_with_gettext_context($text, $context, $domain);
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
        $translations = self::get_translations_for_domain($domain);
        $translation = $translations->translate($text);
        return $translation;
    }

    /**
     * Retrieve the translation of $text in the context defined in $context.
     * If there is no translation, or the text domain isn't loaded the original
     * text is returned.
     * *Note:* Don't use translate_with_gettext_context() directly, use _x() or related functions.
     *
     * @param string $text Text to translate.
     * @param string $context Context information for the translators.
     * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
     *                        Default 'default'.
     * @return string Translated text on success, original text on failure.
     */
    private static function translate_with_gettext_context($text, $context, $domain = 'default')
    {
        $translations = get_translations_for_domain($domain);
        $translation = $translations->translate($text, $context);
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

    /**
     * Return the Translations instance for a text domain.
     *
     * If there isn't one, returns empty Translations instance.
     *
     * @param string $domain Text domain. Unique identifier for retrieving translated strings.
     * @return Translations|NOOP_Translations A Translations instance.
     * @since 2.8.0
     *
     * @global MO[] $l10n
     * @staticvar NOOP_Translations $noop_translations
     *
     */
    private static function get_translations_for_domain($domain)
    {
        global $l10n;
        if (isset($l10n[$domain]) || (_load_textdomain_just_in_time($domain) && isset($l10n[$domain]))) {
            return $l10n[$domain];
        }

        static $noop_translations = null;
        if (null === $noop_translations) {
            $noop_translations = new NOOP_Translations;
        }

        return $noop_translations;
    }
}
