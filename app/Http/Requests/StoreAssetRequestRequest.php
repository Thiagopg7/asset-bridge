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
     * Validate the request against the branch's stock for the chosen asset.
     *
     * A surplus offer cannot exceed the available stock, and a need should not
     * be opened for an asset the branch already holds in stock.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $type = $this->enum('type', AssetRequestType::class);

            $stock = StockItem::query()
                ->where('branch_id', $this->user()->branch_id)
                ->where('asset_id', $this->integer('asset_id'))
                ->value('quantity') ?? 0;

            if ($type === AssetRequestType::Surplus && $this->integer('quantity') > $stock) {
                $validator->errors()->add(
                    'quantity',
                    "A quantidade ofertada não pode ser maior que o estoque disponível ({$stock}).",
                );
            }

            if ($type === AssetRequestType::Need && $stock > 0) {
                $validator->errors()->add(
                    'asset_id',
                    "Este ativo parece existir no estoque da sua filial ({$stock}). Verifique com o seu gerente a liberação do ativo ou a atualização da quantidade no sistema.",
                );
            }
        });
    }
}
