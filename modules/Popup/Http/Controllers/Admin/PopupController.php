<?php

namespace Modules\Popup\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Popup\Entities\Popup;
use Modules\Media\Entities\File;

class PopupController extends Controller
{
    public function index(Request $request)
    {
        $query = Popup::query()->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->boolean('status'));
        }

        if ($request->filled('target_scope')) {
            $query->where('target_scope', $request->input('target_scope'));
        }

        $popups = $query->paginate(20);

        return view('popup::admin.popups.index', compact('popups'));
    }

    public function create()
    {
        $popup = new Popup([
            'status' => true,
            'device' => 'both',
            'trigger_type' => 'on_load_delay',
            'frequency_type' => 'per_session',
            'close_label' => 'Tıkla Pencereyi Kapat',
        ]);

        $imageFile = new File();

        return view('popup::admin.popups.create', compact('popup', 'imageFile'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $popup = Popup::create($data);

        return redirect()->route('admin.popups.index')
            ->withSuccess(trans('admin::messages.resource_created', ['resource' => trans('popup::popup.admin_title')]));
    }

    public function edit($id)
    {
        $popup = Popup::findOrFail($id);

        $imageFile = $popup->image ?: new File();
        
        return view('popup::admin.popups.edit', compact('popup', 'imageFile'));
    }

    public function update(Request $request, $id)
    {
        $popup = Popup::findOrFail($id);

        $data = $this->validateData($request);

        $popup->update($data);

        return redirect()->route('admin.popups.index')
            ->withSuccess(trans('admin::messages.resource_updated', ['resource' => trans('popup::popup.admin_title')]));
    }

    public function duplicate($id)
    {
        $popup = Popup::findOrFail($id);

        $copy = $popup->replicate();
        $copy->name = $popup->name . ' (Kopya)';
        $copy->status = false;
        $copy->save();

        return redirect()->route('admin.popups.edit', $copy->id)
            ->withSuccess(trans('admin::messages.resource_created', ['resource' => trans('popup::popup.admin_title')]));
    }

    public function destroy($id)
    {
        $popup = Popup::findOrFail($id);
        $popup->delete();

        return redirect()->route('admin.popups.index')
            ->withSuccess(trans('admin::messages.resource_deleted', ['resource' => trans('popup::popup.admin_title')]));
    }

    protected function validateData(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'boolean'],
            'device' => ['required', 'in:desktop,mobile,both'],
            'trigger_type' => ['required', 'in:on_load_delay,exit_intent'],
            'trigger_value' => ['nullable', 'integer', 'min:0'],
            'frequency_type' => ['required', 'in:always,per_session,per_days,per_hours'],
            'frequency_value' => ['nullable', 'integer', 'min:1'],
            'target_scope' => ['required', 'in:all,homepage,category,product,cart,checkout,custom_url'],
            'headline' => ['nullable', 'string', 'max:255'],
            'subheadline' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'cta_label' => ['nullable', 'string', 'max:255'],
            'cta_url' => ['nullable', 'string', 'max:2048'],
            'close_label' => ['nullable', 'string', 'max:255'],
            'image_path' => ['nullable', 'integer', 'exists:files,id'],
        ]);

        $validated['status'] = (bool) $request->input('status', false);

        $targeting = [
            'categories' => $request->input('target_categories', []),
            'products' => $request->input('target_products', []),
            'patterns' => $request->input('target_custom_urls', []),
        ];

        $validated['targeting'] = $targeting;

        if (empty($validated['close_label'])) {
            $validated['close_label'] = 'Tıkla Pencereyi Kapat';
        }

        if ($validated['trigger_type'] === 'on_load_delay' && $validated['trigger_value'] === null) {
            $validated['trigger_value'] = 3;
        }

        return $validated;
    }
}
