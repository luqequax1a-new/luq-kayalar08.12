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
            'rules_mode' => ['nullable', 'in:all,any'],
            'include_tags' => ['nullable', 'array'],
            'include_tags.*' => ['integer', 'exists:tags,id'],
            'rules' => ['nullable', 'array'],
            'rules.*.field' => ['nullable', 'string', 'max:255'],
            'rules.*.operator' => ['nullable', 'string', 'max:50'],
            'rules.*.value' => ['nullable'],
            'rules.*.group_no' => ['nullable', 'integer', 'min:1'],
            'rules.*.boolean' => ['nullable', 'in:AND,OR,and,or'],
        ];
    }
}
