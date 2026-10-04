<?php

class validateInputs
{
    /**
     * Input data to validate
     */
    public array $inputs;

    /**
     * Validation rules for each field
     */
    private array $rules = [
        'item_name' => [
            'required' => true,
            'minLength' => 4,
            'maxLength' => 255,
            'regex' => '#^[а-яА-Яa-zA-Z0-9\s.,\-()/]+$#u'
        ],
        'item_description' => [
            'required' => false,
            'minLength' => 4,
            'maxLength' => 10000
        ],
        'item_art' => [
            'required' => false,
            'minLength' => 4,
            'maxLength' => 255,
            'regex' => '#^[а-яА-Яa-zA-Z0-9\s.,\-()&*/]+$#u'
        ],
        'item_category' => [
            'required' => false,
            'minLength' => 4,
            'maxLength' => 100,
            'regex' => '#^[а-яА-Яa-zA-Z0-9\s.,\-()&*/]+$#u'
        ],
        'item_cost' => [
            'required' => true,
            'type' => 'decimal',
            'decimal' => 2,
            'min' => 0.01,
            'max' => 999999.99
        ],
        'item_retail' => [
            'required' => true,
            'type' => 'decimal',
            'decimal' => 2,
            'min' => 0.01,
            'max' => 999999.99
        ],
        'item_manufacturer' => [
            'required' => false,
            'minLength' => 2,
            'maxLength' => 255,
            'regex' => '#^[а-яА-Яa-zA-Z0-9\s.,\-()&*/]+$#u'
        ],
        'item_remain' => [
            'required' => true,
            'type' => 'integer',
            'min' => 0,
            'max' => 9999999
        ],
        'item_unit' => [
            'required' => false,
            'minLength' => 1,
            'maxLength' => 50,
            'regex' => '#^[а-яА-Яa-zA-Z0-9\s.,/]+$#u'
        ],
        'item_status' => [
            'required' => false,
            'minLength' => 2,
            'maxLength' => 255,
            'regex' => '#^[а-яА-Яa-zA-Z\s-]+$#u'
        ]
    ];

    /**
     * Constructor
     */
    public function __construct(array $inputs)
    {
        $this->inputs = $inputs;
    }

    /**
     * Main validation method - routes to appropriate validation
     */
    public function validate(): array
    {
        $cleanData = [];
        $errors = [];

        // Validate each field against its rules
        foreach ($this->rules as $field => $rule) {
            // Get field value or null if not set
            $value = $this->inputs[$field] ?? null;

            // Check required fields
            if ($this->isEmpty($value)) {
                if (!empty($rule['required'])) {
                    $errors[$field][] = 'Field require';
                } else {
                    $cleanData[$field] = null;
                }
                continue;
            }

            // Trim string values
            if (is_string($value)) {
                $value = trim($value);
            }

            // Validate as number or string based on rule type
            if (isset($rule['type'])) {
                $result = $this->validateNumber($value, $rule, $field);
                
                if ($result['error']) {
                    $errors[$field][] = $result['error'];
                } else {
                    $cleanData[$field] = $result['value'];
                }
            } else {
                $result = $this->validateString($value, $rule, $field);
                
                if ($result['error']) {
                    $errors[$field][] = $result['error'];
                } else {
                    $cleanData[$field] = $result['value'];
                }
            }
        }

        $success = empty($errors);
        
        $result = [
            'success' => $success,
            'error' => $success ? null : [
                'code' => 400,
                'message' => 'Invalid inputs'
            ]
        ];

        if ($success) {
            $result['fields'] = $cleanData;
        }

        return $result;
    }

    /**
     * Validate string values
     */
    private function validateString($value, array $rule, string $field): array
    {
        if (isset($rule['minLength']) && mb_strlen($value, 'UTF-8') < $rule['minLength']) {
            return ['error' => "Min {$rule['minLength']} simbols", 'value' => null];
        }

        if (isset($rule['maxLength']) && mb_strlen($value, 'UTF-8') > $rule['maxLength']) {
            return ['error' => "Max {$rule['maxLength']} simbols", 'value' => null];
        }

        if (isset($rule['regex']) && !preg_match($rule['regex'], $value)) {
            return ['error' => 'Field have invalid simbols', 'value' => null];
        }

        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        return ['error' => null, 'value' => $value];
    }

    /**
     * Validate numeric values (integer/decimal)
     */
    private function validateNumber($value, array $rule, string $field): array
    {
        $value = str_replace(',', '.', $value);

        if (!is_numeric($value)) {
            return ['error' => 'Numbers must be float', 'value' => null];
        }

        $num = (float)$value;

        if (isset($rule['min']) && $num < $rule['min']) {
            return ['error' => "Min value: {$rule['min']}", 'value' => null];
        }

        if (isset($rule['max']) && $num > $rule['max']) {
            return ['error' => "Max value: {$rule['max']}", 'value' => null];
        }

        if ($rule['type'] === 'decimal' && isset($rule['decimal'])) {
            if (strpos($value, '.') !== false) {
                $decimals = strlen(substr(strrchr($value, "."), 1));
                if ($decimals > $rule['decimal']) {
                    return ['error' => "Max {$rule['decimal']} decimal places", 'value' => null];
                }
            }
            return ['error' => null, 'value' => number_format($num, $rule['decimal'], '.', '')];
        }

        if ($rule['type'] === 'integer') {
            if (strpos($value, '.') !== false) {
                return ['error' => 'Must be integer', 'value' => null];
            }
            return ['error' => null, 'value' => (string)(int)$num];
        }

        return ['error' => null, 'value' => (string)$num];
    }

    /**
     * Check if value is empty
     */
    private function isEmpty($value): bool
    {
        if ($value === null) return true;
        if ($value === '') return true;
        if (is_array($value) && empty($value)) return true;
        if (is_string($value) && trim($value) === '') return true;
        return false;
    }
}