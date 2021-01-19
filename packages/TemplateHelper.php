<?php

namespace Ricubai\PHPHelpers;

use eftec\bladeone\BladeOne;

class TemplateHelper
{
    public static function reusableTemplateBlock(array $items, string $html)
    {
        $tpl = $html;
        echo self::convertToPredifenedJsTemplating($html);
        if (!$items) {
            return;
        }
        foreach ($items as $item) {
            echo self::replaceVars($html, $item);
        }
    }

    /**
     * Converts reusable block html to HTML which will be used for JS templating.
     * Depends on JS templating library.
     * Currently `Handlebars" is using.
     *
     * @param $html
     * @return string|string[]|null
     */
    private static function convertToPredifenedJsTemplating($html)
    {
        // Replaces provided format of variables to JS templating syntax.
        $html = preg_replace_callback(
            '/\{\{([\w\d,]+)\}\}/is',
            static function ($matches) {
                $args = explode(',', $matches[1]);
                $value = '';
                $var = $args[0];
                return '{{' . $var . '}}';
            },
            $html
        );

        return '<div id="course_template" style="display: none;">' . $html . '</div>';
    }

    private static function replaceVars($html, $data)
    {
        return preg_replace_callback(
            '/\{\{([\w\d,]+)\}\}/is',
            static function ($matches) use ($data) {
                $args = explode(',', $matches[1]);
                $value = '';
                $var = $args[0];
                $func = $args[1] ?? null;
                if ($var && isset($data[$var])) {
                    $value = $data[$var];
                }

                if ($func && method_exists('\Ricubai\PHPHelpers\DataHelper', $func)) {
                    $value = DataHelper::$func($value, false);
                }
                return $value;
            },
            $html
        );
    }
}
