<?php
// Helper para validación de datos

/**
 * Valida que un campo tenga contenido
 */
function validateRequired($value, $fieldName) {
    if (empty(trim($value))) {
        return "El campo $fieldName es obligatorio";
    }
    return '';
}

/**
 * Valida que un campo tenga una longitud mínima
 */
function validateMinLength($value, $fieldName, $min = 3) {
    if (strlen(trim($value)) < $min) {
        return "El campo $fieldName debe tener al menos $min caracteres";
    }
    return '';
}

/**
 * Valida que un campo tenga una longitud máxima
 */
function validateMaxLength($value, $fieldName, $max = 255) {
    if (strlen(trim($value)) > $max) {
        return "El campo $fieldName no debe exceder los $max caracteres";
    }
    return '';
}

/**
 * Valida que un campo sea un email válido
 */
function validateEmail($value, $fieldName = 'Email') {
    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
        return "El $fieldName no es válido";
    }
    return '';
}

/**
 * Valida que un campo sea numérico
 */
function validateNumeric($value, $fieldName) {
    if (!is_numeric($value)) {
        return "El campo $fieldName debe ser un número";
    }
    return '';
}

/**
 * Valida que un campo sea una fecha válida
 */
function validateDate($value, $fieldName, $format = 'Y-m-d') {
    $date = DateTime::createFromFormat($format, $value);
    if (!$date || $date->format($format) !== $value) {
        return "El campo $fieldName debe ser una fecha válida en formato $format";
    }
    return '';
}

/**
 * Valida que un campo sea igual a otro
 */
function validateMatch($value1, $value2, $fieldName) {
    if ($value1 !== $value2) {
        return "Los campos $fieldName no coinciden";
    }
    return '';
}

/**
 * Valida que un valor esté dentro de un array de opciones
 */
function validateInArray($value, $fieldName, $options) {
    if (!in_array($value, $options)) {
        $optionsStr = implode(', ', $options);
        return "El campo $fieldName debe ser uno de los siguientes valores: $optionsStr";
    }
    return '';
}

/**
 * Función para validar varios campos a la vez
 * Recibe un array de reglas y devuelve un array de errores
 */
function validate($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $fieldRules) {
        foreach ($fieldRules as $rule) {
            $ruleName = $rule[0];
            $params = array_slice($rule, 1);
            
            // Si el campo no existe y no es requerido, omitir validación
            if (!isset($data[$field]) && $ruleName !== 'validateRequired') {
                continue;
            }
            
            // Obtener valor del campo o vacío si no existe
            $value = isset($data[$field]) ? $data[$field] : '';
            
            // Aplicar regla de validación
            $error = '';
            switch ($ruleName) {
                case 'validateRequired':
                    $error = validateRequired($value, $params[0]);
                    break;
                case 'validateMinLength':
                    $error = validateMinLength($value, $params[0], $params[1]);
                    break;
                case 'validateMaxLength':
                    $error = validateMaxLength($value, $params[0], $params[1]);
                    break;
                case 'validateEmail':
                    $error = validateEmail($value, $params[0]);
                    break;
                case 'validateNumeric':
                    $error = validateNumeric($value, $params[0]);
                    break;
                case 'validateDate':
                    $error = validateDate($value, $params[0], isset($params[1]) ? $params[1] : 'Y-m-d');
                    break;
                case 'validateMatch':
                    $error = validateMatch($value, $data[$params[0]], $params[1]);
                    break;
                case 'validateInArray':
                    $error = validateInArray($value, $params[0], $params[1]);
                    break;
            }
            
            // Si hay error, agregarlo y pasar al siguiente campo
            if (!empty($error)) {
                $errors[$field] = $error;
                break;
            }
        }
    }
    
    return $errors;
}
?>