<?php

namespace Modules\Order\Http\Controllers\Admin;

use Modules\Order\Entities\Order;
use Modules\Admin\Traits\HasCrudActions;

class OrderController
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['products', 'coupon', 'taxes'];

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected $label = 'order::orders.order';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected $viewPath = 'order::admin.orders';

    public function update($id)
    {
        $entity = $this->getEntity($id);
        $this->disableSearchSyncing();
        $data = $this->getRequest('update')->except(array_keys(request()->query()));
        $entity->update($data);
        $entity->withoutEvents(function () use ($entity) {
            $entity->touch();
        });
        $this->searchable($entity);

        $tracking = isset($data['tracking_reference']) ? trim((string) $data['tracking_reference']) : null;
        if ($tracking !== null && $tracking !== '') {
            $trkUrl = null;
            $trkNo = null;
            if (filter_var($tracking, FILTER_VALIDATE_URL)) {
                $trkUrl = $tracking;
                $parts = parse_url($tracking);
                $q = $parts['query'] ?? '';
                if ($q !== '') {
                    parse_str($q, $qp);
                    if (isset($qp['code']) && is_string($qp['code']) && $qp['code'] !== '') {
                        $trkNo = $qp['code'];
                    }
                }
            } else {
                $trkNo = $tracking;
            }
            $upd = [];
            if ($trkUrl) { $upd['shipping_tracking_url'] = $trkUrl; }
            if ($trkNo && !$entity->shipping_tracking_number) { $upd['shipping_tracking_number'] = $trkNo; }
            if (!empty($upd)) { $entity->update($upd); }
        }

        if ($tracking && $entity->status !== Order::SHIPPED && $entity->status !== Order::COMPLETED) {
            $entity->transitionTo(Order::SHIPPED);
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => trans('admin::messages.resource_updated', ['resource' => $this->getLabel()]),
            ], 200);
        }

        return redirect()->route("{$this->getRoutePrefix()}.index")
            ->withSuccess(trans('admin::messages.resource_updated', ['resource' => $this->getLabel()]));
    }
}
