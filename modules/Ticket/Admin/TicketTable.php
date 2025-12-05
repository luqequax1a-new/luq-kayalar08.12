<?php

namespace Modules\Ticket\Admin;

use Modules\Admin\Ui\AdminTable;

class TicketTable extends AdminTable
{
    public function make()
    {
        return $this->newTable()
            ->editColumn('user', function ($ticket) {
                return optional($ticket->user)->email;
            })
            ->editColumn('status', function ($ticket) {
                $status = (string) $ticket->status;
                switch ($status) {
                    case 'closed':
                        return 'Kapalı';
                    case 'waiting_admin':
                    case 'waiting_customer':
                    case 'open':
                    default:
                        return 'Açık';
                }
            })
            ->editColumn('created', function ($ticket) {
                return view('admin::partials.table.date')->with('date', $ticket->created_at);
            })
            ->editColumn('updated', function ($ticket) {
                return view('admin::partials.table.date')->with('date', $ticket->updated_at);
            })
            ->addColumn('actions', function ($ticket) {
                $url = route('admin.tickets.show', $ticket->id);
                return '<a href="' . e($url) . '" class="btn btn-primary btn-sm">Görüntüle</a>';
            })
            ->rawColumns(['actions']);
    }
}
