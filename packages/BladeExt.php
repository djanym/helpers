<?php

namespace Ricubai\PHPHelpers;

use eftec\bladeone\BladeOne;
use InvalidArgumentException;

class BladeExt extends BladeOne
{
    /** @var string The "regular" / legacy echo string format. */
    protected $echoFormat = '\esc_html(%s)';

    /**
     * We create the new tags @hello <br>
     * The name of the method must starts with "compile"<br>
     * <b>Example:</b><br>
     * <pre>
     * @hello()
     * @hello("name")
     * </pre>
     *
     * @param null|string $expression expects a value like null, (), ("hello") or ($somevar)
     * @return string returns a fragment of code (php and html)
     */
    public function compileHello($expression = null)
    {
        if ($expression === null || $expression === '()') {
            return "<?php echo '--empty--'; ?>";
        }
        return "<?php echo 'Hello '.$expression; ?>";
    }

    /**
     * Compile the given Blade template contents.
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
