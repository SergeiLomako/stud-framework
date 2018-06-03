<?php
/**
 * Created by PhpStorm.
 * User: flameseeker
 * Date: 03.06.18
 * Time: 22:52
 */

namespace Mindk\Framework\Validation;

use Mindk\Framework\Http\Request\Request;
use Mindk\Framework\Exceptions\ValidationException;

class Validation
{

    protected $rules = ['min', 'max', 'file', 'email', 'required', 'confirmed'];

    public function validate(Request $request, $data){
        $errors = [];
        foreach($data as $field => $rules){
            $field_rules = is_int(strpos($rules, '|')) ? explode('|', $rules) : [$rules];
            foreach($field_rules as $rule){
                $rule_array = is_int(strpos($rule, ':')) ? explode(':', $rule) : [$rule];
                if(!in_array($rule_array[0], $this->rules)){
                    throw new ValidationException($rule_array[0] . ' not found in rules');
                }
                if(count($rule_array) === 1){
                    $result = $this->{$rule_array[0]}($field);
                    if(is_array($result)){
                       $errors += $result;
                       break;
                    }
                }
                if(count($rule_array) === 2){
                    $result = $this->{$rule_array[0]}($field, $rule_array[1]);
                    if(is_array($result)){
                        $errors += $result;
                        break;
                    }
                }
                $result = $this->{$rule_array[0]}($field, $rule_array[1], $rule_array[2]);
                if(is_array($result)){
                    $errors += $result;
                    break;
                }
            }
        }
        
        return empty($errors) ? true : $errors;
    }
    
    public function min($field, int $min) {

        return strlen($field) > $min ? true : [$field => ucfirst($field) . " must be at least $min characters"];
    }
    
    public function max($field, int $max) {

        return strlen($field) < $max ? true : [$field =>  ucfirst($field) . "must not exceed $max characters"];
    }

    public function file($file_field) {

        return is_file($file_field['tmp_name']) ? true : [$file_field => ucfirst($file_field) . " is not a file"];
    }
    
    public function email($field) {

        return is_string(filter_var($field, FILTER_VALIDATE_EMAIL)) ? true : [$field => "Incorrect email"];
    }
    
    public function required($field) {
        return !empty($field) ? true : [$field => ucfirst($field) . " is required"];
    }
    
    public function confirmed($field, Request $request){
        $confirmed_field = 'confirmed_' . lcfirst($field);
        if(!$request->has($confirmed_field)){
            throw new ValidationException($confirmed_field . 'not found in Request');
        }

        return $field === $request->get($confirmed_field, null, 'string') ? true : [$field => ucfirst($field) . 's do not match'];
    }

    public function unique($field, $table_name, $column){
        $model_name = ucfirst(substr($table_name, 0, -1)) . 'Model';
        $model = new $model_name();
        $check = $model->exist($column, $field);

        return empty($check) ? true : [$field => ucfirst($field) . " already exists"];
    }

}