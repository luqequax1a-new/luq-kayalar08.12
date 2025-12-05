<?php

namespace Modules\Coupon\Admin;

use Modules\Admin\Ui\AdminTable;
use Modules\Coupon\Entities\Coupon;

class ReviewCouponTable extends AdminTable
{
    protected array $rawColumns = ['actions', 'status'];

    public function make()
    {
        return $this->newTable()
            ->addColumn('code', function (Coupon $coupon) {
                return e($coupon->code);
            })
            ->addColumn('customer', function (Coupon $coupon) {
                if ($coupon->order) {
                    $first = $coupon->order->customer_first_name ?? '';
                    $last  = $coupon->order->customer_last_name ?? '';
                    $email = $coupon->order->customer_email ?? '';
                    $full = trim($first . ' ' . $last);
                    return e($full . ($email ? " ({$email})" : ''));
                }
                return $coupon->customer_id ? ('Müşteri #' . e($coupon->customer_id)) : '-';
            })
            ->addColumn('order_id', function (Coupon $coupon) {
                return $coupon->order_id ? ('#' . e($coupon->order_id)) : '-';
            })
            ->addColumn('discount', function (Coupon $coupon) {
                return $coupon->is_percent
                    ? ((int) $coupon->value) . ' %'
                    : $coupon->value->format();
            })
            ->addColumn('created', function (Coupon $coupon) {
                return optional($coupon->created_at)->format('d.m.Y H:i');
            })
            ->editColumn('status', function (Coupon $coupon) {
                if ($coupon->redeemed_at) {
                    return '<span class="badge badge-danger">Kullanıldı</span>';
                }
                return $coupon->is_active
                    ? '<span class="badge badge-success">' . trans('admin::admin.table.active') . '</span>'
                    : '<span class="badge badge-danger">' . trans('admin::admin.table.inactive') . '</span>';
            })
            ->addColumn('actions', function (Coupon $coupon) {
                return view('coupon::admin.review_coupons.partials.actions', compact('coupon'));
            });
    }
}
