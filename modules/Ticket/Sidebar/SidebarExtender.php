<?php

namespace Modules\Ticket\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.content'), function (Group $group) {
            $group->item(trans('admin::sidebar.sales'), function (Item $item) {
                $item->icon('fa fa-dollar');
                $item->weight(15);

                $item->item(trans('ticket::ticket.tickets'), function (Item $item) {
                    $item->icon('fa fa-ticket');
                    $item->weight(20);
                    $item->route('admin.tickets.index');
                    $item->authorize(true);
                });
            });
        });
    }
}
