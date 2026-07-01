<?php

namespace App\Http\Requests;

use App\Enums\TransferStatus;
use App\Models\AssetRequest;
use App\Models\Transfer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransferRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
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
            'quantity.required' => 'A quantidade é obrigatória.',
            'quantity.min' => 'A quantidade deve ser de no mínimo 1.',
        ];
    }

    /**
     * Validate the request against the offer it draws from.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $offer = $this->route('assetRequest');

            if (! $offer instanceof AssetRequest || ! $offer->isAvailableOffer()) {
                $validator->errors()->add('quantity', 'Esta oferta não está mais disponível.');

                return;
            }

            if ($offer->branch_id === $this->user()->branch_id) {
                $validator->errors()->add('quantity', 'Não é possível solicitar transferência da própria filial.');

                return;
            }

            $alreadyRequested = Transfer::query()
                ->where('branch_id', $this->user()->branch_id)
                ->where('status', TransferStatus::Pending)
                ->whereHas('assetRequest', fn ($query) => $query->where('asset_id', $offer->asset_id))
                ->exists();

            if ($alreadyRequested) {
                $validator->errors()->add('quantity', 'Sua filial já registrou um pedido de transferência para este ativo.');

                return;
            }

            if ($this->integer('quantity') > $offer->available_quantity) {
                $validator->errors()->add(
                    'quantity',
                    "A quantidade solicitada não pode ser maior que o saldo disponível ({$offer->available_quantity}).",
                );
            }
        });
    }
}
