<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCpf implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cpf = preg_replace('/[^0-9]/', '', (string) $value);

        if (strlen($cpf) !== 11 || preg_match('/^(.)\1+$/', $cpf)) {
            $fail('CPF inválido.');
            return;
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += (int) $cpf[$i] * ($t + 1 - $i);
            }
            $digit = ($sum * 10 % 11) % 10;
            if ((int) $cpf[$t] !== $digit) {
                $fail('CPF inválido.');
                return;
            }
        }
    }
}
