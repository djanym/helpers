<?php

namespace Ricubai\PHPHelpers;

use Valitron\Validator;

class DataHelper
{
    /**
     *
     */
    public static function validateFormPost(array $fields)
    {
        print_r($fields);
        $rules = array_flip($fields);
        print_r($rules);
        die;
        $v = new Validator($_POST);
        $v->rule('required', ['name', 'email']);
        $v->rule('email', 'email');
        if ($v->validate()) {
            return true;
        } else {
            return $v->errors();
        }
        return true;
    }
}
