<?php

namespace Modules\Product\Admin;

use Modules\Admin\Ui\AdminTable;
use Modules\Product\Entities\UrlRedirect;

class RedirectTable extends AdminTable
{
    protected array $rawColumns = ['target', 'status', 'active', 'actions'];

    public function make()
    {
        return $this->newTable()
            ->editColumn('source_path', function (UrlRedirect $r) {
                return e($r->source_path);
            })
            ->addColumn('target', function (UrlRedirect $r) {
                return e($r->target_url ?: '/');
            })
            ->addColumn('status', function (UrlRedirect $r) {
                return '<span class="badge">' . e($r->status_code) . '</span>';
            })
            ->addColumn('active', function (UrlRedirect $r) {
                $checked = $r->is_active ? 'checked' : '';
                return "<form action='" . e(route('admin.redirects.status', $r->id)) . "' method='POST' class='js-toggle-form'>"
                    . csrf_field() . method_field('PATCH')
                    . "<input type='hidden' name='is_active' value='0'>"
                    . "<div class='switch'><input type='checkbox' class='js-toggle-active' name='is_active' value='1' id='redirect-" . e($r->id) . "-active' " . $checked . "/><label for='redirect-" . e($r->id) . "-active'></label></div>"
                    . "</form>";
            })
            ->editColumn('created_at', function (UrlRedirect $r) {
                return optional($r->created_at)->format('d.m.Y H:i');
            })
            ->addColumn('actions', function (UrlRedirect $r) {
                $editUrl = route('admin.redirects.edit', $r->id);
                $deleteId = $r->id;
                return "<div class='d-flex align-items-center'>"
                    . "<a href='" . e($editUrl) . "' class='action-edit' title='Edit' data-toggle='tooltip'>"
                    . "<svg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none'><path d='M4 20H20' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/><path d='M16.44 3.56006L20.44 7.56006' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/><path d='M14.02 5.98999L6.91 13.1C6.52 13.49 6.15 14.25 6.07 14.81L5.64 17.83C5.45 19.08 6.42 20.04 7.67 19.86L10.69 19.43C11.25 19.35 12.01 18.98 12.41 18.59L19.52 11.48' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/></svg>"
                    . "</a>"
                    . "<a href='#' class='action-delete m-l-10' data-id='" . e($deleteId) . "' title='Delete' data-toggle='tooltip' data-confirm>"
                    . "<svg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none'><path d='M9 3H15' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/><path d='M4 7H20' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/><path d='M7 7L7.5 19C7.5 20.1046 8.39543 21 9.5 21H14.5C15.6046 21 16.5 20.1046 16.5 19L17 7' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/></svg>"
                    . "</a>"
                    . "</div>";
            });
    }
}
