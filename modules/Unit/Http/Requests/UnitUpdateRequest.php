<?php

namespace Modules\Unit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnitUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $unitId = $this->unit->id ?? 'NULL';

        return [
            // code otomatik üretilecek, güncellemede gerek yok
            'name' => ['required', 'string', 'max:100'],
            'label' => ['required', 'string', 'max:100'],
            // kaldırıldı: tekil info yerine info_top/info_bottom
            'info_top' => ['nullable', 'string', 'max:1000'],
            'info_bottom' => ['nullable', 'string', 'max:1000'],
            'step' => ['required', 'numeric', 'min:0.01'],
            'min' => ['required', 'numeric', 'min:0.01'],
            'default_qty' => ['nullable', 'numeric', 'min:0.01', 'gte:min'],
            'is_default' => ['nullable', 'boolean'],
            'is_decimal_stock' => ['nullable', 'boolean'],
            'short_suffix' => ['nullable', 'string', 'max:10'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
