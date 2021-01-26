<?php

namespace Ricubai\PHPHelpers;

use eftec\bladeone\BladeOne;
use InvalidArgumentException;

class BladeExt extends BladeOne
{
    /** @var string The "regular" / legacy echo string format. */
    protected $echoFormat = '\esc_html(%s)';

    /**
     * Change template content before Blade compile it.
     *
     * @param string $value
     * @return string
     */
    public function compileString($value)
    {
        $value = $this->compileDynamicBlocks($value);
        return parent::compileString($value);
    }

    public function compileDynamicBlocks($html)
    {
        //
        $html = preg_replace_callback(
            '/(?<all>\@dynamicblock\( *(?<block_name>[\w\d]+) *, *(?<loop_var>[^)]*)\)'
            . '(?<block_content>[\w\W]+)'
            . '\@enddynamicblock\( *(\k<block_name>) *\))/is',
            static function ($matches) {
                if (!trim($matches['block_name'])) {
                    $block_name = \DH::rand();
                } else {
                    $block_name = trim($matches['block_name']);
                }

                // Create a div container for the whole block
                $out = '<div id="' . $block_name . '_container">' . "\n";

                // Add JS tpl.
                $out .= self::convertToPredifenedJsTemplate($matches['block_content'], $block_name);
                $out .= "\n";

                // Add common Blade @foreach loop
                $out .= '@foreach($courses as $item)' . "\n";
                $out .= $matches['block_content'] . "\n";
                $out .= '@endforeach()' . "\n";
                $out .= '</div>' . "\n";
                return $out;
            },
            $html
        );

        return $html;
    }

    /**
     * Converts reusable block html to HTML which will be used for JS templating.
     * Depends on JS templating library.
     * Currently `Handlebars" is using.
     *
     * @param $html
     * @return string|string[]|null
     */
    private static function convertToPredifenedJsTemplate($html, $name)
    {
        // Replaces provided format of variables to JS templating syntax.
        $html = preg_replace_callback(
            '/(?<open_tag>\{\{|\{\!\!) *(?<content>.*) *(?<close_tag>\!\!\}|\}\})/isUm',
            'self::processBlade2JsSyntax',
            $html
        );

        $tpl_id = $name . '_template';

        return '<div id="' . $tpl_id . '" style="display: none;">' . $html . '</div>';
    }

    /**
     * Converts reusable block html to HTML which will be used for JS templating.
     * Depends on JS templating library.
     * Currently `EJS" (https://ejs.co) is using.
     *
     * @param $html
     * @return string|string[]|null
     */
    private static function processBlade2JsSyntax($matches)
    {
        if (trim($matches['open_tag']) === '{!!') {
            $open_tag = '<%-';
            $close_tag = '%>';
        } else {
            $open_tag = '<%=';
            $close_tag = '%>';
        }
        $content = self::bladeVars2Js($matches['content']);
        // If nothing was changed, then no JS syntax was applying. So no need to change Blade syntax.
        if( $content === $matches['content'] ){
            return $matches[0];
        } else {
            return $open_tag . $content . $close_tag;
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
    private static function bladeVars2Js($content)
    {
        // Replaces provided format of variables to JS templating syntax.
        return preg_replace_callback(
            '/ *(?<content>\$(?<varname1>[\w\d]+)(\[\'(?<varname2>[\w\d]+)\'\]){0,1} *\|*[^\!\}]*) */is',
            static function ($matches) {
                print_r($matches);
                if (trim($matches['varname2'])) {
                    $var = trim($matches['varname2']);
                } elseif (trim($matches['varname1'])) {
                    $var = trim($matches['varname1']);
                } else {
                    $var = $matches[0];
                }
                return $var;
            },
            $content
        );
    }

    private static function bladeIfs2Js($content)
    {
        echo $content;
        // Replaces provided format of variables to JS templating syntax.
        return preg_replace_callback(
            '/ *(?<content>\$(?<varname1>[\w\d]+)(\[\'(?<varname2>[\w\d]+)\'\]){0,1} *\|*[^\!\}]*) */is',
            static function ($matches) {
                print_r($matches);
                if (trim($matches['varname2'])) {
                    $var = trim($matches['varname2']);
                } elseif (trim($matches['varname1'])) {
                    $var = trim($matches['varname1']);
                } else {
                    $var = $matches[0];
                }
                return $var;
            },
            $content
        );
    }

    /**
     * Converts reusable block html to HTML which will be used for JS templating.
     * Depends on JS templating library.
     * Currently `Handlebars" is using.
     *
     * @param $html
     * @return string|string[]|null
     */
    private static function _bladeVars2JsSyntax($html, $name)
    {
        // Replaces provided format of variables to JS templating syntax.
        $html = preg_replace_callback(
            '/(?<open_tag>\{\{|\{\!\!) *(?<content>\$(?<varname1>[\w\d]+)(\[\'(?<varname2>[\w\d]+)\'\]){0,1} *\|*[^\!\}]*) *(?<close_tag>\!\!\}|\}\})/is',
            static function ($matches) {
                $open_tag = trim($matches['open_tag']);
                if (trim($matches['open_tag']) === '{!!') {
                    $open_tag = '{{{';
                    $close_tag = '}}}';
                } else {
                    $open_tag = '{{';
                    $close_tag = '}}';
                }
                if (trim($matches['varname2'])) {
                    $var = trim($matches['varname2']);
                } elseif (trim($matches['varname1'])) {
                    $var = trim($matches['varname1']);
                } else {
                    return $matches[0];
                }
                return '@' . $open_tag . $var . $close_tag;
            },
            $html
        );

        $tpl_id = $name . '_template';

        return '<div id="' . $tpl_id . '" style="display: none;">' . $html . '</div>';
    }
}
