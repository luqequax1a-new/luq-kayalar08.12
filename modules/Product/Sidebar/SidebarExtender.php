<?php

namespace Modules\Product\Sidebar;

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
                $item->icon('fa fa-cube');
                $item->weight(10);
                $item->route('admin.products.index');
                $item->authorize(
                    $this->auth->hasAnyAccess([
                        'admin.products.create',
                        'admin.products.index',
                        'admin.categories.index',
                        'admin.attributes.index',
                        'admin.attribute_sets.index',
                        'admin.variations.index',
                        'admin.options.index',
                    ])
                );

                $item->item(trans('product::sidebar.create_product'), function (Item $item) {
                    $item->weight(5);
                    $item->route('admin.products.create');
                    $item->authorize(
                        $this->auth->hasAccess('admin.products.create')
                    );
                });

                $item->item(trans('product::sidebar.all_products'), function (Item $item) {
                    $item->weight(6);
                    $item->route('admin.products.index');
                    $item->isActiveWhen(route('admin.products.index', null, false));
                    $item->authorize(
                        $this->auth->hasAccess('admin.products.index')
                    );
                });

                $item->item('Toplu Ürün Güncelleme', function (Item $item) {
                    $item->weight(7);
                    $item->route('admin.products.bulk_editor');
                    $item->isActiveWhen(route('admin.products.bulk_editor', null, false));
                    $item->authorize(
                        $this->auth->hasAnyAccess([
                            'admin.bulk_product_edits.manage',
                            'admin.products.edit',
                        ])
                    );
                });
            });
        });

        $menu->group(trans('admin::sidebar.system'), function (Group $group) {
            $group->item('SEO', function (Item $item) {
                $item->icon('fa fa-external-link');
                $item->weight(20);
                $item->route('admin.redirects.index');
                $item->authorize(true);

                $item->item('Yönlendirmeler', function (Item $item) {
                    $item->weight(1);
                    $item->route('admin.redirects.index');
                    $item->isActiveWhen(route('admin.redirects.index', null, false));
                    $item->authorize(true);
                });

                $item->item('Bulk Meta Manager', function (Item $item) {
                    $item->weight(2);
                    $item->route('admin.seo.bulk_meta.index');
                    $item->isActiveWhen(route('admin.seo.bulk_meta.index', null, false));
                    $item->authorize(true);
                });

                $item->item('Sitemap', function (Item $item) {
                    $item->weight(3);
                    $item->route('admin.sitemaps.create');
                    $item->isActiveWhen(route('admin.sitemaps.create', null, false));
                    $item->authorize(true);
                });

                $item->item('Robots.txt', function (Item $item) {
                    $item->weight(4);
                    $item->route('admin.robots.edit');
                    $item->isActiveWhen(route('admin.robots.edit', null, false));
                    $item->authorize(true);
                });

                $item->item('Product Feeds', function (Item $item) {
                    $item->weight(5);
                    $item->route('admin.product_feeds.settings.index');
                    $item->isActiveWhen(route('admin.product_feeds.settings.index', null, false));
                    $item->icon('fa fa-rss');
                    $item->authorize(
                        $this->auth->hasAccess('admin.settings.edit')
                    );
                });
            });
        });
    }
}
