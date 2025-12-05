<?php

namespace Modules\Coupon\Admin;

use Modules\Admin\Ui\AdminTable;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Exceptions\Exception;

class CouponTable extends AdminTable
{
    /**
     * Make table response for the resource.
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function make()
    {
        return $this->newTable()
            ->addColumn('discount', function ($coupon) {
                return $coupon->is_percent
                    ? ((int) $coupon->value) . '%'
                    : $coupon->value->format();
            })
            ->addColumn('validity', function ($coupon) {
                $startCarbon = $coupon->start_date;
                $endCarbon = $coupon->end_date;
                $start = $startCarbon ? $startCarbon->format('Y-m-d') : null;
                $end = $endCarbon ? $endCarbon->format('Y-m-d') : null;

                if ($start && $end) {
                    $days = $endCarbon->diffInDays($startCarbon);
                    return $start . ' ' . $end . ' ( ' . $days . ' Gün )';
                }
                if ($end) {
                    $daysLeft = $endCarbon->isFuture() ? now()->diffInDays($endCarbon) : 0;
                    return 'Son tarih: ' . $end . ' ( ' . $daysLeft . ' Gün )';
                }
                if ($start) {
                    return 'Başlangıç: ' . $start . ' ( Süresiz )';
                }
                return 'Süresiz';
            });
    }
}
