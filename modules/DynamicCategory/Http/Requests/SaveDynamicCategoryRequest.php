<?php

namespace Modules\DynamicCategory\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;
use Modules\DynamicCategory\Entities\DynamicCategory;

class SaveDynamicCategoryRequest extends Request
{
    protected $availableAttributes = 'dynamic_category::attributes';

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('dynamic_categories', 'slug')->ignore($this->id),
            ],
            'is_active' => ['required', 'boolean'],
            'include_tags' => ['required', 'array', 'min:1'],
            'include_tags.*' => ['integer', 'exists:tags,id'],
        ];
    }
}
