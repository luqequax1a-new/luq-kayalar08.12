<?php

namespace Modules\DynamicCategory\Admin;

use Modules\Admin\Ui\Tab;
use Modules\Admin\Ui\Tabs;

class DynamicCategoryTabs extends Tabs
{
    public function make()
    {
        $this->group('dynamic_category_information', trans('dynamic_category::dynamic_categories.tabs.group.dynamic_category_information'))
            ->active()
            ->add($this->general())
            ->add($this->tagRules());
    }

    private function general(): Tab
    {
        return tap(new Tab('general', trans('dynamic_category::dynamic_categories.tabs.general')), function (Tab $tab) {
            $tab->active();
            $tab->weight(5);
            $tab->fields(['name', 'description', 'image_id', 'slug', 'is_active', 'meta_title', 'meta_description']);
            $tab->view('dynamic_category::admin.dynamic_categories.tabs.general');
        });
    }

    private function tagRules(): Tab
    {
        return tap(new Tab('tag_rules', trans('dynamic_category::dynamic_categories.tabs.tag_rules')), function (Tab $tab) {
            $tab->weight(10);
            $tab->fields(['include_tags', 'exclude_tags']);
            $tab->view('dynamic_category::admin.dynamic_categories.tabs.tag_rules');
        });
    }

    // Preview tab deliberately removed; dynamic categories are configured only by basic info and tag rules.
}
