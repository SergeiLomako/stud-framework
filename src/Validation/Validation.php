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
use Mindk\Framework\DB\DBOConnectorInterface;

class Validation
{
    protected $db;
    protected $rules = ['min', 'max', 'file', 'email', 'required', 'confirmed', 'unique'];

    public function validate(Request $request, $data, DBOConnectorInterface $db){
        $this->db = $db;
        $errors = [];
        foreach($data as $field => $rules){
            $field_rules = is_int(strpos($rules, '|')) ? explode('|', $rules) : [$rules];
            foreach($field_rules as $rule){
                $rule_array = is_int(strpos($rule, ':')) ? explode(':', $rule) : [$rule];
                if(!in_array($rule_array[0], $this->rules)){
                    throw new ValidationException($rule_array[0] . ' not found in rules');
                }
                if(count($rule_array) === 1){
                    if($rule_array[0] == 'confirmed'){
                        $result = $this->{$rule_array[0]}($field, $request->get($field), $request);
                    }
                    else {
                        $result = $this->{$rule_array[0]}($field, $request->get($field));
                    }
                    if(is_array($result)){
                       $errors += $result;
                       break;
                    }
                }
                if(count($rule_array) === 2){
                    $result = $this->{$rule_array[0]}($field, $request->get($field), $rule_array[1]);
                    if(is_array($result)){
                        $errors += $result;
                        break;
                    }
                }
                if(count($rule_array) === 3) {
                    $result = $this->{$rule_array[0]}($field, $request->get($field), $rule_array[1], $rule_array[2]);
                    if (is_array($result)) {
                        $errors += $result;
                        break;
                    }
                }
            }
        }
        
        return empty($errors) ? true : $errors;
    }
    
    public function min($field, $field_value, int $min) {

        return strlen($field_value) >= $min ? true : [$field => ucfirst($field) . " must be at least $min characters"];
    }
    
    public function max($field, $field_value, int $max) {

        return strlen($field_value) <= $max ? true : [$field =>  ucfirst($field) . " must not exceed $max characters"];
    }

    public function file($file_field, $field_value) {

        return isset($field_value['tmp_name']) && is_file($field_value['tmp_name']) ? true : [$file_field => ucfirst($file_field) . " is not a file"];
    }
    
    public function email($field, $field_value) {

        return is_string(filter_var($field_value, FILTER_VALIDATE_EMAIL)) ? true : [$field => "Incorrect email"];
    }
    
    public function required($field, $field_value) {
        return !empty($field_value) ? true : [$field => ucfirst($field) . " is required"];
    }
    
    public function confirmed($field, $field_value, Request $request){
        $confirmed_field = 'confirm_' . lcfirst($field);
        if(!$request->has($confirmed_field)){
            throw new ValidationException($confirmed_field . ' not found in Request');
        }

        return $field_value === $request->get($confirmed_field, null, 'string') ? true : [$field => ucfirst($field) . 's do not match'];
    }

    public function unique($field, $field_value, $table_name, $column){
        $namespace = $table_name == 'users' ? '\Mindk\Framework\Models\\' : '\App\Models\\'; 
        $model_name = $namespace . ucfirst(substr($table_name, 0, -1)) . 'Model';
        if(!class_exists($model_name)){
            throw new ValidationException("Table '$table_name' or $model_name not found");
        }
        $model = new $model_name($this->db);
        $columns = $model->getColumnsNames();
        if(!in_array($column, $columns)){
            throw new ValidationException("'$column' not found in '$table_name'");
        }
        $check = $model->exist($column, $field_value);

        return empty($check) ? true : [$field => ucfirst($field) . " already exists"];
    }

}