<?php

namespace App\Validation;

use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator as Respect;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Validator
 * @package App\Validation
 */
class Validator
{
    /**
     * @var array
     */
    protected $errors;

    /**
     * @param Request $request
     * @param array $rules
     * @return Validator
     */
    public function validate(Request $request, array $rules): Validator
    {
        foreach ($rules as $field => $rule) {
            try {
                $rule->setName(preg_replace('([_])', ' ', ucfirst($field)))->assert($request->getParam($field));
            } catch (NestedValidationException $e) {
                $this->errors[$field] = $e->getMessages();
            }
        }
        $_SESSION['errors'] = $this->errors;
        return $this;
    }

    /**
     * @return bool
     */
    public function failed()
    {
        return !empty($this->errors);
    }
}