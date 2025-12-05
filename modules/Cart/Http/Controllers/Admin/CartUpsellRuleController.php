<?php

namespace Modules\Cart\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Cart\Entities\CartUpsellRule;
use Modules\Product\Entities\Product;

class CartUpsellRuleController extends Controller
{
    public function index()
    {
        $rules = CartUpsellRule::with(['mainProduct', 'upsellProduct', 'preselectedVariant'])
            ->orderByDesc('sort_order')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('cart::admin.upsell_rules.index', compact('rules'));
    }

    public function create()
    {
        $rule = new CartUpsellRule([
            'status' => true,
            'trigger_type' => 'product_to_product',
            'discount_type' => 'none',
        ]);

        return view('cart::admin.upsell_rules.create', [
            'rule' => $rule,
        ]);
    }

    public function store(Request $request)
    {
        \Log::info('[UPSSELL] store.raw', [
            'all' => $request->all(),
        ]);

        $data = $this->validateData($request);
        \Log::info('[UPSSELL] store.payload', [
            'data' => $data,
            'db' => \DB::connection()->getDatabaseName(),
        ]);

        $rule = CartUpsellRule::create($data);

        \Log::info('[UPSSELL] store.created', [
            'id' => optional($rule)->id,
            'total' => CartUpsellRule::count(),
        ]);

        return redirect()->route('admin.cart_upsell_rules.index')
            ->withSuccess(trans('admin::messages.resource_created', ['resource' => trans('cart::upsell.admin_title')]));
    }

    public function edit($id)
    {
        $rule = CartUpsellRule::with(['mainProduct', 'upsellProduct', 'preselectedVariant'])
            ->withoutGlobalScope('active')
            ->findOrFail($id);

        return view('cart::admin.upsell_rules.edit', compact('rule'));
    }

    public function update(Request $request, $id)
    {
        $rule = CartUpsellRule::withoutGlobalScope('active')->findOrFail($id);

        $data = $this->validateData($request);

        $rule->update($data);

        return redirect()->route('admin.cart_upsell_rules.index')
            ->withSuccess(trans('admin::messages.resource_updated', ['resource' => trans('cart::upsell.admin_title')]));
    }

    public function destroy($id)
    {
        $rule = CartUpsellRule::withoutGlobalScope('active')->findOrFail($id);
        $rule->delete();

        return redirect()->route('admin.cart_upsell_rules.index')
            ->withSuccess(trans('admin::messages.resource_deleted', ['resource' => trans('cart::upsell.admin_title')]));
    }

    protected function validateData(Request $request): array
    {
        $locale = locale();

        $validated = $request->validate([
            'status' => ['sometimes', 'boolean'],
            'trigger_type' => ['required', 'string'],
            'main_product_id' => ['required_if:trigger_type,product_to_product', 'nullable', 'integer', 'exists:products,id'],
            'upsell_product_id' => ['required', 'integer', 'exists:products,id'],
            'preselected_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'discount_type' => ['required', 'in:none,percent,fixed'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'title' => ['nullable', 'string'],
            'subtitle' => ['nullable', 'string'],
            'internal_name' => ['nullable', 'string', 'max:255'],
            'show_on' => ['required', 'in:checkout,post_checkout,product'],
            'min_cart_total' => ['nullable', 'numeric', 'min:0'],
            'max_cart_total' => ['nullable', 'numeric', 'min:0'],
            'hide_if_already_in_cart' => ['nullable', 'boolean'],
            'has_countdown' => ['nullable', 'boolean'],
            'countdown_minutes' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $title = $request->input('title');
        $subtitle = $request->input('subtitle');

        $validated['status'] = (bool) $request->input('status', false);
        $validated['hide_if_already_in_cart'] = (bool) $request->input('hide_if_already_in_cart', true);
        $validated['has_countdown'] = (bool) $request->input('has_countdown', false);
        $validated['discount_value'] = $validated['discount_value'] ?? 0;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $validated['title'] = $title ? [$locale => $title] : null;
        $validated['subtitle'] = $subtitle ? [$locale => $subtitle] : null;

        return $validated;
    }
}
