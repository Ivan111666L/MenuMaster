<?php
// App/Utils/Validator.php
namespace app\Utils;

use Exception;

class Validator {
    /**
     * Valida datos contra un conjunto de reglas.
     * @param array $data Los datos a validar (ej. ['email' => 'test@test.com']).
     * @param array $rules Las reglas de validación (ej. ['email' => 'required|email']).
     * @throws Exception Si la validación falla.
     */
    public static function validate(array $data, array $rules): void
    {
        foreach ($rules as $field => $ruleString) {
            $ruleList = explode('|', $ruleString);
            $value = $data[$field] ?? null;

            foreach ($ruleList as $rule) {
                if (strpos($rule, ':')) {
                    [$ruleName, $ruleValue] = explode(':', $rule, 2);
                    self::applyRule($field, $value, $ruleName, $ruleValue);
                } else {
                    self::applyRule($field, $value, $rule);
                }
            }
        }
    }

    private static function applyRule(string $field, $value, string $ruleName, $ruleValue = null): void
    {
        switch ($ruleName) {
            case 'required':
                if (empty($value)) {
                    throw new Exception("El campo '{$field}' es obligatorio.", 400);
                }
                break;
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("El campo '{$field}' debe ser un correo electrónico válido.", 400);
                }
                break;
            case 'min':
                if (strlen($value) < $ruleValue) {
                    throw new Exception("El campo '{$field}' debe tener al menos {$ruleValue} caracteres.", 400);
                }
                break;
            // Puedes añadir más reglas aquí (numeric, max, etc.)
        }
    }
}