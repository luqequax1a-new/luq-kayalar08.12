<?php

namespace Modules\Category\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Category\Entities\Category;

class TestTextileCategorySeeder extends Seeder
{
    public function run()
    {
        app()->setLocale('tr');

        $tekstil = new Category(['parent_id' => null, 'position' => 1, 'is_active' => true, 'is_searchable' => true]);
        $tekstil->translateOrNew('tr')->name = 'Tekstil';
        $tekstil->translateOrNew('en')->name = 'Textile';
        $tekstil->save();

        $kumaslar = new Category(['parent_id' => $tekstil->id, 'position' => 1, 'is_active' => true, 'is_searchable' => true]);
        $kumaslar->translateOrNew('tr')->name = 'Kumaşlar';
        $kumaslar->translateOrNew('en')->name = 'Fabrics';
        $kumaslar->save();

        $pamuk = new Category(['parent_id' => $kumaslar->id, 'position' => 1, 'is_active' => true, 'is_searchable' => true]);
        $pamuk->translateOrNew('tr')->name = 'Pamuk';
        $pamuk->translateOrNew('en')->name = 'Cotton';
        $pamuk->save();

        $polyester = new Category(['parent_id' => $kumaslar->id, 'position' => 2, 'is_active' => true, 'is_searchable' => true]);
        $polyester->translateOrNew('tr')->name = 'Polyester';
        $polyester->translateOrNew('en')->name = 'Polyester';
        $polyester->save();

        $ipek = new Category(['parent_id' => $kumaslar->id, 'position' => 3, 'is_active' => true, 'is_searchable' => true]);
        $ipek->translateOrNew('tr')->name = 'İpek';
        $ipek->translateOrNew('en')->name = 'Silk';
        $ipek->save();

        $yun = new Category(['parent_id' => $kumaslar->id, 'position' => 4, 'is_active' => true, 'is_searchable' => true]);
        $yun->translateOrNew('tr')->name = 'Yün';
        $yun->translateOrNew('en')->name = 'Wool';
        $yun->save();

        $aksesuar = new Category(['parent_id' => $tekstil->id, 'position' => 2, 'is_active' => true, 'is_searchable' => true]);
        $aksesuar->translateOrNew('tr')->name = 'Aksesuar';
        $aksesuar->translateOrNew('en')->name = 'Accessories';
        $aksesuar->save();

        $dugme = new Category(['parent_id' => $aksesuar->id, 'position' => 1, 'is_active' => true, 'is_searchable' => true]);
        $dugme->translateOrNew('tr')->name = 'Düğme';
        $dugme->translateOrNew('en')->name = 'Buttons';
        $dugme->save();

        $fermuar = new Category(['parent_id' => $aksesuar->id, 'position' => 2, 'is_active' => true, 'is_searchable' => true]);
        $fermuar->translateOrNew('tr')->name = 'Fermuar';
        $fermuar->translateOrNew('en')->name = 'Zippers';
        $fermuar->save();

        $dikis = new Category(['parent_id' => $tekstil->id, 'position' => 3, 'is_active' => true, 'is_searchable' => true]);
        $dikis->translateOrNew('tr')->name = 'Dikiş Malzemeleri';
        $dikis->translateOrNew('en')->name = 'Sewing Supplies';
        $dikis->save();

        $igne = new Category(['parent_id' => $dikis->id, 'position' => 1, 'is_active' => true, 'is_searchable' => true]);
        $igne->translateOrNew('tr')->name = 'İğne';
        $igne->translateOrNew('en')->name = 'Needles';
        $igne->save();

        $iplik = new Category(['parent_id' => $dikis->id, 'position' => 2, 'is_active' => true, 'is_searchable' => true]);
        $iplik->translateOrNew('tr')->name = 'İplik';
        $iplik->translateOrNew('en')->name = 'Threads';
        $iplik->save();

        $ev = new Category(['parent_id' => null, 'position' => 2, 'is_active' => true, 'is_searchable' => true]);
        $ev->translateOrNew('tr')->name = 'Ev Tekstili';
        $ev->translateOrNew('en')->name = 'Home Textile';
        $ev->save();

        $nevresim = new Category(['parent_id' => $ev->id, 'position' => 1, 'is_active' => true, 'is_searchable' => true]);
        $nevresim->translateOrNew('tr')->name = 'Nevresim';
        $nevresim->translateOrNew('en')->name = 'Bedding';
        $nevresim->save();

        $perde = new Category(['parent_id' => $ev->id, 'position' => 2, 'is_active' => true, 'is_searchable' => true]);
        $perde->translateOrNew('tr')->name = 'Perde';
        $perde->translateOrNew('en')->name = 'Curtains';
        $perde->save();

        $karisik = new Category(['parent_id' => null, 'position' => 3, 'is_active' => true, 'is_searchable' => true]);
        $karisik->translateOrNew('tr')->name = 'Karışık';
        $karisik->translateOrNew('en')->name = 'Mixed';
        $karisik->save();

        $ozel = new Category(['parent_id' => $karisik->id, 'position' => 1, 'is_active' => true, 'is_searchable' => true]);
        $ozel->translateOrNew('tr')->name = 'Özel Kumaşlar';
        $ozel->translateOrNew('en')->name = 'Special Fabrics';
        $ozel->save();

        $karisim = new Category(['parent_id' => $ozel->id, 'position' => 1, 'is_active' => true, 'is_searchable' => true]);
        $karisim->translateOrNew('tr')->name = '%50 Pamuk %50 Polyester';
        $karisim->translateOrNew('en')->name = '50% Cotton 50% Polyester';
        $karisim->save();

        $surdurulebilir = new Category(['parent_id' => $karisik->id, 'position' => 2, 'is_active' => true, 'is_searchable' => true]);
        $surdurulebilir->translateOrNew('tr')->name = 'Sürdürülebilir';
        $surdurulebilir->translateOrNew('en')->name = 'Sustainable';
        $surdurulebilir->save();

        $organik = new Category(['parent_id' => $surdurulebilir->id, 'position' => 1, 'is_active' => true, 'is_searchable' => true]);
        $organik->translateOrNew('tr')->name = 'Organik Pamuk';
        $organik->translateOrNew('en')->name = 'Organic Cotton';
        $organik->save();
    }
}

