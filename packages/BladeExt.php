<?php

namespace Ricubai\PHPHelpers;

use eftec\bladeone\BladeOne;
use InvalidArgumentException;

class BladeExt extends BladeOne
{
    /** @var array The stack of in-progress sections. */
    protected $dynamicBlockStack = [];

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
//                print_r($matches);
                $out = '@foreach($courses as $item)'."\n";
                $out .= $matches['block_content']."\n";
                $out .= '@endforeach()'."\n";
                return $out;

                $args = explode(',', $matches[1]);
                $value = '';
                $var = $args[0];
                return $out;
            },
            $html
        );

        return $html;
    }

    /**
     * Compile the end-for-each statements into valid PHP.
     *
     * @return string
     */
//    protected function compileEndDynamicBlock($expression = null)
//    {
//        \preg_match('/\( *(.*) * as *([^)]*)/', $expression, $matches);
//        $iteratee = \trim($matches[1]);
//        $iteration = \trim($matches[2]);
//        $initLoop = "\$__currentLoopData = {$iteratee}; \$this->addLoop(\$__currentLoopData);\$this->getFirstLoop();\n";
//        $iterateLoop = '$loop = $this->incrementLoopIndices(); ';
//        print_r($expression);
//        print_r($iteratee);
//        print_r($iteration);
//        print_r($initLoop);
//        print_r($iterateLoop);
//        die;
//        $out = $this->phpTag . '$__dynamicBlockTpl = ob_get_clean();' . "\n"
//            . '$__currentLoopData = ' . $iteratee . ';' . "\n"
//            . '$this->processDynamicBlock($__currentLoopData, $__dynamicBlockTpl); ' . "\n"
//            . '$this->addLoop($__currentLoopData);' . "\n"
//            . '$this->getFirstLoop();' . "\n"
//            . 'foreach($__currentLoopData as ' . $iteration . '):' . "\n"
//            . '$loop = $this->incrementLoopIndices();' . "\n"
//            . 'echo $__dynamicBlockTpl;' . "\n"
    /*            . 'endforeach; $this->popLoop(); $loop = $this->getFirstLoop(); ?>';*/
//        return $out;
//    }

//    public function compileDynamicBlock($expression = null)
//    {
//        $this->appendSection();
//        $this->yieldSection();
//        print_r($this);die;
//        \preg_match('/\( *(.*) * as *([^)]*)/', $expression, $matches);
//        $iteratee = \trim($matches[1]);
//        $iteration = \trim($matches[2]);
//        $initLoop = "\$__currentLoopData = {$iteratee}; \$this->addLoop(\$__currentLoopData);\$this->getFirstLoop();\n";
////        $initLoop = "\$__currentLoopData = {$iteratee}; \$this->addLoop(\$__currentLoopData);\$this->getFirstLoop();\n";
//        $iterateLoop = '$loop = $this->incrementLoopIndices(); ';
//        print_r($expression);
//        print_r($iteratee);
//        print_r($iteration);
//        print_r($initLoop);
//        print_r($iterateLoop);
//        die;
    /*        return $this->phpTag . "ob_start(); ?>";*/
    /*        return $this->phpTag . "{$initLoop} foreach(\$__currentLoopData as {$iteration}): {$iterateLoop} ?>";*/
//    }

    /**
     * Compile the end-for-each statements into valid PHP.
     *
     * @return string
     */
//    protected function compileEndDynamicBlock($expression = null)
//    {
//        \preg_match('/\( *(.*) * as *([^)]*)/', $expression, $matches);
//        $iteratee = \trim($matches[1]);
//        $iteration = \trim($matches[2]);
//        $initLoop = "\$__currentLoopData = {$iteratee}; \$this->addLoop(\$__currentLoopData);\$this->getFirstLoop();\n";
//        $iterateLoop = '$loop = $this->incrementLoopIndices(); ';
//        print_r($expression);
//        print_r($iteratee);
//        print_r($iteration);
//        print_r($initLoop);
//        print_r($iterateLoop);
//        die;
//        $out = $this->phpTag . '$__dynamicBlockTpl = ob_get_clean();' . "\n"
//            . '$__currentLoopData = ' . $iteratee . ';' . "\n"
//            . '$this->processDynamicBlock($__currentLoopData, $__dynamicBlockTpl); ' . "\n"
//            . '$this->addLoop($__currentLoopData);' . "\n"
//            . '$this->getFirstLoop();' . "\n"
//            . 'foreach($__currentLoopData as ' . $iteration . '):' . "\n"
//            . '$loop = $this->incrementLoopIndices();' . "\n"
//            . 'echo $__dynamicBlockTpl;' . "\n"
    /*            . 'endforeach; $this->popLoop(); $loop = $this->getFirstLoop(); ?>';*/
//        return $out;
//    }

    public function processDynamicBlock(array $items, string $html)
    {
        print_r($items);
        return;
//        echo $html;
//        $this->addLoop($items);
//        $this->getFirstLoop();
//        foreach($items as {$iteration}): {$iterateLoop}
        die;
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
