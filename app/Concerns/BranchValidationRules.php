<?php

namespace App\Concerns;

use App\Models\Branch;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait BranchValidationRules
{
    /**
     * Get the validation rules used to validate branches.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function branchRules(?int $branchId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                $branchId === null
                    ? Rule::unique(Branch::class, 'code')
                    : Rule::unique(Branch::class, 'code')->ignore($branchId),
            ],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'size:2'],
            'active' => ['boolean'],
        ];
    }

    /**
     * Get the validation messages used when validating branches.
     *
     * @return array<string, string>
     */
    protected function branchMessages(): array
    {
        return [
            'name.required' => 'O nome da filial é obrigatório.',
            'code.required' => 'O código da filial é obrigatório.',
            'code.unique' => 'Já existe uma filial com este código.',
            'state.size' => 'A UF deve ter exatamente 2 letras.',
        ];
    }
}
