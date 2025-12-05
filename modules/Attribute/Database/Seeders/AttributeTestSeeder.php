<?php

namespace Modules\Attribute\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Attribute\Entities\AttributeSet;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeValue;

class AttributeTestSeeder extends Seeder
{
    public function run()
    {
        Model::unguard();

        $this->createSet('Genel Özellikler', 'General Features', [
            [
                'tr' => 'Renk', 'en' => 'Color', 'values' => [
                    ['tr' => 'Yeşil', 'en' => 'Green'],
                    ['tr' => 'Kırmızı', 'en' => 'Red'],
                    ['tr' => 'Mavi', 'en' => 'Blue'],
                ],
            ],
            [
                'tr' => 'Kumaş Türü', 'en' => 'Fabric Type', 'values' => [
                    ['tr' => 'Pamuk', 'en' => 'Cotton'],
                    ['tr' => 'Keten', 'en' => 'Linen'],
                    ['tr' => 'Polyester', 'en' => 'Polyester'],
                ],
            ],
        ]);

        $this->createSet('Teknik Özellikler', 'Technical Specifications', [
            [
                'tr' => 'Genişlik', 'en' => 'Width', 'values' => [
                    ['tr' => '150 cm', 'en' => '150 cm'],
                    ['tr' => '200 cm', 'en' => '200 cm'],
                ],
            ],
            [
                'tr' => 'Ağırlık', 'en' => 'Weight', 'values' => [
                    ['tr' => '200 gsm', 'en' => '200 gsm'],
                    ['tr' => '300 gsm', 'en' => '300 gsm'],
                ],
            ],
        ]);

        $this->createSet('Bakım Talimatları', 'Care Instructions', [
            [
                'tr' => 'Yıkama Sıcaklığı', 'en' => 'Wash Temperature', 'values' => [
                    ['tr' => '30°C', 'en' => '30°C'],
                    ['tr' => '40°C', 'en' => '40°C'],
                    ['tr' => '60°C', 'en' => '60°C'],
                ],
            ],
            [
                'tr' => 'Ütüleme', 'en' => 'Ironing', 'values' => [
                    ['tr' => 'Düşük', 'en' => 'Low'],
                    ['tr' => 'Orta', 'en' => 'Medium'],
                ],
            ],
        ]);
    }

    private function createSet(string $nameTr, string $nameEn, array $attributes)
    {
        $set = new AttributeSet();
        $set->translateOrNew('tr')->name = $nameTr;
        $set->translateOrNew('en')->name = $nameEn;
        $set->save();

        foreach ($attributes as $attrDef) {
            $attr = new Attribute(['attribute_set_id' => $set->id, 'is_filterable' => false]);
            $attr->translateOrNew('tr')->name = $attrDef['tr'];
            $attr->translateOrNew('en')->name = $attrDef['en'];
            $attr->save();

            $pos = 0;
            foreach ($attrDef['values'] as $valDef) {
                $val = new AttributeValue(['attribute_id' => $attr->id, 'position' => $pos++]);
                $val->translateOrNew('tr')->value = $valDef['tr'];
                $val->translateOrNew('en')->value = $valDef['en'];
                $val->save();
            }
        }
    }
}

