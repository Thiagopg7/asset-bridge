<?php

namespace App\Http\Requests;

use App\Enums\AssetRequestType;
use App\Models\StockItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetRequestRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'asset_id' => ['required', Rule::exists('assets', 'id')->where('active', true)],
            'type' => ['required', Rule::enum(AssetRequestType::class)],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get the validation messages for the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'asset_id.required' => 'O ativo é obrigatório.',
            'asset_id.exists' => 'Ativo inválido ou inativo.',
            'type.required' => 'O tipo da solicitação é obrigatório.',
            'type.enum' => 'Tipo de solicitação inválido.',
            'quantity.required' => 'A quantidade é obrigatória.',
            'quantity.min' => 'A quantidade deve ser de no mínimo 1.',
        ];
    }

    /**
     * Validate that a surplus offer does not exceed the branch's available stock.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if ($this->enum('type', AssetRequestType::class) !== AssetRequestType::Surplus) {
                return;
            }

            $available = StockItem::query()
                ->where('branch_id', $this->user()->branch_id)
                ->where('asset_id', $this->integer('asset_id'))
                ->value('quantity') ?? 0;

            if ($this->integer('quantity') > $available) {
                $validator->errors()->add(
                    'quantity',
                    "A quantidade ofertada não pode ser maior que o estoque disponível ({$available}).",
                );
            }
        });
    }
}
