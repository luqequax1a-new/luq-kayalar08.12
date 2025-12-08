<?php

namespace Modules\Setting\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.system'), function (Group $group) {
            $group->item(trans('setting::sidebar.settings'), function (Item $item) {
                $item->weight(25);
                $item->icon('fa fa-cogs');
                $item->route('admin.settings.edit');
                $item->authorize(
                    $this->auth->hasAccess('admin.settings.edit')
                );
            });
        });

        $menu->group(trans('admin::sidebar.content'), function (Group $group) {
            $group->item(trans('admin::sidebar.automations'), function (Item $item) {
                $item->weight(21);
                $item->icon('fa fa-tasks');

                $item->item(trans('admin::sidebar.review_campaigns'), function (Item $item) {
                    $item->weight(1);
                    $item->route('admin.settings.review_campaign');
                    $item->authorize(
                        $this->auth->hasAccess('admin.settings.edit')
                    );
                });

                $item->item(trans('admin::sidebar.whatsapp_module'), function (Item $item) {
                    $item->weight(2);
                    $item->route('admin.settings.whatsapp_module');
                    $item->authorize(
                        $this->auth->hasAccess('admin.settings.edit')
                    );
                });

                $item->item('Etiket-Görsel', function (Item $item) {
                    $item->weight(3);
                    $item->route('admin.tag_badges.index');
                    $item->icon('fa fa-image');
                    $item->authorize(
                        $this->auth->hasAccess('admin.settings.edit')
                    );
                });

                $item->item('Pop-up', function (Item $item) {
                    $item->weight(4);
                    $item->route('admin.popups.index');
                    $item->icon('fa fa-window-restore');
                    $item->authorize(
                        $this->auth->hasAccess('admin.settings.edit')
                    );
                });
                
                $item->item('Özelleştirmeler', function (Item $item) {
                    $item->weight(5);
                    $item->icon('fa fa-magic');
                    $item->route('admin.settings.customizations');
                    $item->authorize(
                        $this->auth->hasAccess('admin.settings.edit')
                    );
                });
            });
        });
    }
}
