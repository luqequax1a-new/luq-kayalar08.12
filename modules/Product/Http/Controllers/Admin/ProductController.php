<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Modules\Product\Entities\Product;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Product\Http\Requests\SaveProductRequest;
use Modules\Product\Transformers\ProductEditResource;
use Modules\Product\Services\ProductDuplicator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class ProductController
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected string $model = Product::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected string $label = 'product::products.product';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected string $viewPath = 'product::admin.products';

    /**
     * Form requests for the resource.
     *
     * @var array|string
     */
    protected string|array $validation = SaveProductRequest::class;


    /**
     * Store a newly created resource in storage.
     *
     * @return Response|JsonResponse
     */
    public function store()
    {
        $this->disableSearchSyncing();

        $entity = $this->getModel()->create(
            $this->getRequest('store')->all()
        );

        $this->searchable($entity);

        $message = trans('admin::messages.resource_created', ['resource' => $this->getLabel()]);

        if (request()->query('exit_flash')) {
            session()->flash('exit_flash', $message);
        }

        if (request()->wantsJson()) {
            return response()->json(
                [
                    'success' => true,
                    'message' => $message,
                ],
                200
            );
        }

        return redirect()->route("{$this->getRoutePrefix()}.index")
            ->withSuccess($message);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Factory|View|Application
     */
    public function edit($id): Factory|View|Application
    {
        $entity = $this->getEntity($id);
        $productEditResource = new ProductEditResource($entity);

        return view(
            "{$this->viewPath}.edit",
            [
                'product' => $entity,
                'product_resource' => $productEditResource->response()->content(),
            ]
        );
    }


    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     */
    public function update($id)
    {
        $entity = $this->getEntity($id);
        $oldSlug = $entity->slug;
        $requestOldSlug = request('original_slug');
        $newSlug = request('slug');
        $shouldCreateRedirect = (bool) request('redirect_on_slug_change');

        $this->disableSearchSyncing();

        $entity->update(
            $this->getRequest('update')->all()
        );

        if ($shouldCreateRedirect && $newSlug && $oldSlug && $newSlug !== $oldSlug && (!$requestOldSlug || $requestOldSlug === $oldSlug)) {
            try {
                $sourcePath = '/products/' . ltrim($oldSlug, '/');
                $targetUrl = '/products/' . ltrim($newSlug, '/');

                \Modules\Product\Entities\UrlRedirect::updateOrCreate(
                    ['source_path' => $sourcePath],
                    [
                        'target_type' => 'custom',
                        'target_id' => null,
                        'target_url' => $targetUrl,
                        'status_code' => 301,
                        'is_active' => true,
                    ]
                );
            } catch (\Throwable $e) {
            }
        }

        $entity->withoutEvents(function () use ($entity) {
            $entity->touch();
        });

        $productEditResource = new ProductEditResource($entity);

        $this->searchable($entity);

        $message = trans('admin::messages.resource_updated', ['resource' => $this->getLabel()]);

        if (request()->query('exit_flash')) {
            session()->flash('exit_flash', $message);
        }

        if (request()->wantsJson()) {
            return response()->json(
                [
                    'success' => true,
                    'message' => $message,
                    'product_resource' => $productEditResource,
                ],
                200
            );
        }
    }

    public function status($id)
    {
        $entity = $this->getEntity($id);

        $entity->update([
            'is_active' => request('is_active') ? 1 : 0,
        ]);

        return response()->json([
            'success' => true,
        ], 200);
    }

    /**
     * Permanently delete resources by given ids (force delete) and detach relations.
     *
     * @param string $ids
     * @return JsonResponse
     */
    public function destroy(string $ids): JsonResponse
    {
        $idList = collect(explode(',', $ids))
            ->map(fn ($id) => (int) $id)
            ->filter();

        $deleted = [];

        foreach ($this->getModel()->withoutGlobalScope('active')->whereIn('id', $idList)->get() as $product) {
            try {
                $redirectType = request('redirect.type');
                $statusCode = (int) (request('redirect.status_code') ?? 301);
                $targetId = request('redirect.target_id');
                $targetUrl = request('redirect.target_url');
                $sourcePath = '/products/' . ltrim($product->slug, '/');

                if ($redirectType && $redirectType !== 'none') {
                    $targetType = $redirectType;
                    if ($redirectType === 'home') {
                        $targetUrl = '/';
                    }

                    \Modules\Product\Entities\UrlRedirect::updateOrCreate(
                        ['source_path' => $sourcePath],
                        [
                            'target_type' => $targetType,
                            'target_id' => $targetId,
                            'target_url' => $targetUrl,
                            'status_code' => in_array($statusCode, [301,302,410,404]) ? $statusCode : 301,
                            'is_active' => true,
                        ]
                    );
                }

                // Detach pivot relations
                if (method_exists($product, 'categories')) {
                    $product->categories()->detach();
                }
                if (method_exists($product, 'tags')) {
                    $product->tags()->detach();
                }
                if (method_exists($product, 'options')) {
                    $product->options()->detach();
                }

                // Delete variants hard
                if (method_exists($product, 'variants')) {
                    $product->variants()->withoutGlobalScope('active')->forceDelete();
                }

                // Delete translations
                if (method_exists($product, 'translations')) {
                    $product->translations()->delete();
                }

                // Detach media
                if (method_exists($product, 'files')) {
                    $product->files()->detach();
                }

                $product->forceDelete();
                $deleted[] = $product->id;
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return response()->json([
            'success' => true,
            'message' => trans('admin::messages.resource_deleted', ['resource' => $this->getLabel()]),
            'ids' => $deleted,
        ], 200);
    }

    public function inventory($id)
    {
        $entity = $this->getEntity($id);
        $resource = new \Modules\Product\Transformers\ProductEditResource(
            $entity->load(['variants' => function ($q) {
                $q->withoutGlobalScope('active')->orderBy('position');
            }, 'saleUnit'])
        );

        return response()->json([
            'success' => true,
            'product' => $resource->toArray(request()),
        ], 200);
    }

    public function updateInventory($id)
    {
        $entity = $this->getEntity($id);

        $payload = request()->all();

        if (array_key_exists('qty', $payload)) {
            $qty = $payload['qty'];
            $entity->withoutEvents(function () use ($entity, $qty) {
                $entity->update(['qty' => $qty]);
            });
        }

        if (array_key_exists('variants', $payload) && is_array($payload['variants'])) {
            foreach ($payload['variants'] as $vid => $attrs) {
                $variant = $entity->variants()->withoutGlobalScope('active')->where('id', $vid)->first();
                if ($variant) {
                    $update = [];
                    if (isset($attrs['qty'])) $update['qty'] = $attrs['qty'];
                    if (isset($attrs['in_stock'])) $update['in_stock'] = $attrs['in_stock'] ? 1 : 0;
                    if (isset($attrs['manage_stock'])) $update['manage_stock'] = $attrs['manage_stock'] ? 1 : 0;

                    if (!empty($update)) {
                        $variant->update($update);
                    }
                }
            }

            
        }

        $entity->refresh();

        return response()->json([
            'success' => true,
        ], 200);
    }

    /**
     * Duplicate the specified product and redirect to its edit page.
     *
     * @param int $id
     * @param ProductDuplicator $duplicator
     *
     * @return \Illuminate\Http\RedirectResponse|JsonResponse
     */
    public function duplicate($id, ProductDuplicator $duplicator)
    {
        $product = $this->getEntity($id);

        $newProduct = $duplicator->duplicate($product);

        $message = trans('admin::messages.resource_created', ['resource' => trans('product::products.product')]);

        $redirectUrl = route('admin.products.edit', $newProduct->id);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'new_id' => $newProduct->id,
                'redirect_url' => $redirectUrl,
            ], 200);
        }

        return redirect()->to($redirectUrl)->withSuccess($message);
    }
}
