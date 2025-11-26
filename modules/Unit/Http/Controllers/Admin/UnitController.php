<?php

namespace Modules\Unit\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Modules\Unit\Entities\Unit;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Unit\Http\Requests\UnitStoreRequest;
use Modules\Unit\Http\Requests\UnitUpdateRequest;

class UnitController
{
    use HasCrudActions;

    protected string $model = Unit::class;
    protected string $label = 'units.unit';
    protected string $viewPath = 'unit::admin.units';
    protected string|array $validation = [
        'store' => UnitStoreRequest::class,
        'update' => UnitUpdateRequest::class,
    ];

    public function index(): Factory|View|Application
    {
        $units = Unit::orderBy('name')->paginate(20);
        return view('unit::admin.units.index', compact('units'));
    }

    public function create(): Factory|View|Application
    {
        return view('unit::admin.units.create');
    }

    public function store(UnitStoreRequest $request)
    {
        $entity = Unit::create($request->validated());

        $message = trans('admin::messages.resource_created', ['resource' => 'Unit']);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'unit' => $entity,
            ], 200);
        }

        return redirect()->route('admin.units.index')->withSuccess($message);
    }

    public function edit(Unit $unit): Factory|View|Application
    {
        return view('unit::admin.units.edit', compact('unit'));
    }

    public function update(UnitUpdateRequest $request, Unit $unit): Response|JsonResponse
    {
        $unit->update($request->validated());

        $message = trans('admin::messages.resource_updated', ['resource' => 'Unit']);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'unit' => $unit,
            ], 200);
        }

        return redirect()->route('admin.units.index')->withSuccess($message);
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();
        return response()->json(['success' => true]);
    }
}
