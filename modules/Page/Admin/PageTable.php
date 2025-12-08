<?php

namespace Modules\Page\Admin;

use Modules\Admin\Ui\AdminTable;
use Modules\Page\Entities\Page;

class PageTable extends AdminTable
{
    /**
     * Raw columns that will not be escaped.
     *
     * @var array
     */
    protected array $rawColumns = ['status', 'created', 'actions', 'name'];

    /**
     * Make table response for the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function make()
    {
        return $this->newTable()
            ->editColumn('name', function (Page $page) {
                $url = route('admin.pages.edit', $page->id);

                return "<a href='{$url}' class='name-link' title='Edit'>" . e($page->name) . '</a>';
            })
            ->addColumn('actions', function (Page $page) {
                $editUrl = route('admin.pages.edit', $page->id);
                $viewUrl = $page->url();

                return "<div class='actions-grid'>
                    <a href='{$editUrl}' class='action-edit' title='Edit' data-toggle='tooltip' onclick='event.stopPropagation();'>
                        <svg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none'>
                            <path d='M4 20H20' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/>
                            <path d='M16.44 3.56006L20.44 7.56006' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/>
                            <path d='M14.02 5.98999L6.91 13.1C6.52 13.49 6.15 14.25 6.07 14.81L5.64 17.83C5.45 19.08 6.42 20.04 7.67 19.86L10.69 19.43C11.25 19.35 12.01 18.98 12.41 18.59L19.52 11.48' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/>
                        </svg>
                    </a>
                    <a href='{$viewUrl}' target='_blank' class='action-view' title='View' data-toggle='tooltip' onclick='event.stopPropagation();'>
                        <svg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none'>
                            <path d='M2 12C2 12 5.99997 4 12 4C18 4 22 12 22 12C22 12 18 20 12 20C5.99997 20 2 12 2 12Z' stroke='#292D32' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/>
                            <path d='M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z' stroke='#292D32' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/>
                        </svg>
                    </a>
                </div>";
            });
    }
}
