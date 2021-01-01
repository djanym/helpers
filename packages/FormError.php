<?php

namespace Ricubai\PHPHelpers;

class FormError
{
    /**
     * Stores the list of errors.
     * @var array
     */
    public $errors = array();

    /**
     * Stores the list of data for error codes.
     * @var array
     */
    public $error_data = array();

    /**
     * Initialize the error object.
     *
     * If `$field` is empty, the other parameters will be ignored.
     * When `$code` is not empty, `$message` will be used even if
     * it is empty. The `$data` parameter will be used only if it
     * is not empty.
     *
     * $field can be a array with codes and messages.
     *
     * Possible usage:
     * - FormError('field','message');
     * - FormError([
     *      'field1'=>['message1', 'message2', ...],
     *      'field2'=>['message1', 'message2', ...]
     *  );
     *
     * Though the class is constructed with a single error code and
     * message, multiple codes can be added using the `add()` method.
     *
     * @param string|int|array $field Field name or a error code or an array with multiple fields and messages
     * @param string $message Optional. Error message. Should be used if a single field was used.
     * @param mixed $data Optional. Error data.
     */
    public function __construct($field = '', $message = '', $data = '')
    {
        if (empty($field)) {
            return;
        }

        if (is_array($field)) {
            $this->add_errors($field);
        } else {
//            $this->errors[$code][] = $message;
            $this->add($field, $message);
        }


        if (!empty($data)) {
//            $this->error_data[$code] = $data;
            $this->add_data($data, $code);
        }
    }

    /**
     * Retrieve all error codes.
     * @return array List of error codes, if available.
     */
    public function get_error_codes()
    {
        if (empty($this->errors)) {
            return array();
        }

        return array_keys($this->errors);
    }

    /**
     * Retrieve first error code available.
     * @return string|int Empty string, if no error codes.
     */
    public function get_error_code()
    {
        $codes = $this->get_error_codes();

        if (empty($codes)) {
            return '';
        }

        return $codes[0];
    }

    /**
     * Retrieve all error messages or error messages matching code.
     * @param string|int $code Optional. Retrieve messages matching code, if exists.
     * @return array Error strings on success, or empty array on failure (if using code parameter).
     */
    public function get_error_messages($code = '')
    {
        // Return all messages if no code specified.
        if (empty($code)) {
            $all_messages = array();
            foreach ((array)$this->errors as $code => $messages) {
                $all_messages = array_merge($all_messages, $messages);
            }

            return $all_messages;
        }

        if (isset($this->errors[$code])) {
            return $this->errors[$code];
        } else {
            return array();
        }
    }

    /**
     * Get single error message.
     *
     * This will get the first message available for the code. If no code is
     * given then the first code available will be used.
     *
     * @param string|int $code Optional. Error code to retrieve message.
     * @return string
     */
    public function get_error_message($code = '')
    {
        if (empty($code)) {
            $code = $this->get_error_code();
        }
        $messages = $this->get_error_messages($code);
        if (empty($messages)) {
            return '';
        }
        return $messages[0];
    }

    /**
     * Retrieve error data for error code.
     *
     * @param string|int $code Optional. Error code.
     * @return mixed Error data, if it exists.
     */
    public function get_error_data($code = '')
    {
        if (empty($code)) {
            $code = $this->get_error_code();
        }

        if (isset($this->error_data[$code])) {
            return $this->error_data[$code];
        }
    }

    /**
     * Add an error or append additional message to an existing error.
     */
    public function add($field, $message, $data = '')
    {
        if (is_array($message)) {
            foreach ($message as $item) {
                $this->add($field, $item);
            }
        } else {
            $this->errors[$field][] = $message;
        }
        if (!empty($data)) {
            $this->error_data[$code] = $data;
        }
    }

    /**
     * Add an error or append additional message to an existing error.
     */
    public function add_errors($errors)
    {
        foreach ($errors as $field => $errors) {
            $this->add($field, $errors);
        }
    }

    /**
     * Add data for error code.
     *
     * The error code can only contain one error data.
     *
     * @param mixed $data Error data.
     * @param string|int $code Error code.
     */
    public function add_data($data, $code = '')
    {
        if (empty($code)) {
            $code = $this->get_error_code();
        }

        $this->error_data[$code] = $data;
    }

    /**
     * Removes the specified error.
     *
     * This function removes all error messages associated with the specified
     * error code, along with any error data for that code.
     *
     * @param string|int $code Error code.
     */
    public function remove($code)
    {
        unset($this->errors[$code]);
        unset($this->error_data[$code]);
    }
}
