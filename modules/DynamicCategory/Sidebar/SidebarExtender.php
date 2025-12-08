<?php

namespace Modules\DynamicCategory\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.content'), function (Group $group) {
            $group->item(trans('product::sidebar.products'), function (Item $item) {
                $item->item(trans('dynamic_category::dynamic_categories.dynamic_categories'), function (Item $item) {
                    $item->weight(11);
                    $item->route('admin.dynamic_categories.index');
                    $item->authorize(
                        $this->auth->hasAccess('admin.dynamic_categories.index')
                    );
                });
            });
        });
    }
}
