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
     *
     */
    public static function flipArray(array $a1)
    {
        $a2 = [];
        foreach ($a1 as $key => $value) {
            if (is_array($value)) {
                $a2[] = self::flipArray($value);
            } elseif (isset($a2[$value])) {
                if (is_array($a2[$value])) {
                    $a2[$value][] = $key;
                } else {
                    $_tmp = $a2[$value];
                    $a2[$value] = [$_tmp];
                    $a2[$value][] = $key;
                }
            } else {
                $a2[$value] = $key;
            }
        }
        return $a2;
    }
}
