<?php

namespace Modules\Category\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Category\Entities\Category;
use Modules\Core\Http\Requests\Request;

class SaveCategoryRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var string
     */
    protected $availableAttributes = 'category::attributes';


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'slug' => $this->getSlugRules(),
            'is_active' => 'required|boolean',
            'faq_items' => 'nullable|array',
            'faq_items.*.question' => 'required|string',
            'faq_items.*.answer' => 'required|string',
        ];
    }


    private function getSlugRules()
    {
        $rules = $this->route()->getName() === 'admin.categories.update'
            ? ['required']
            : ['nullable'];

        $slug = Category::withoutGlobalScope('active')->where('id', $this->id)->value('slug');

        $rules[] = Rule::unique('categories', 'slug')->ignore($slug, 'slug');

        return $rules;
    }
}
