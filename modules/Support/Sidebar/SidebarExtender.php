<?php

namespace Modules\Support\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.system'), function (Group $group) {
            $group->item(trans('support::clear_cache.clear_cache'), function (Item $item) {
                $item->icon('fa fa-recycle ');
                $item->weight(25);
                $item->route('admin.clear_cache.all');
            });
        });
    }
}
