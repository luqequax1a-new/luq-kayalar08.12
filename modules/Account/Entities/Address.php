<?php

namespace Modules\Account\Entities;

use Modules\Support\State;
use Modules\Support\Country;
use Modules\User\Entities\User;
use Modules\Support\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'address_1',
        'address_2',
        'city',
        'state',
        'zip',
        'country',
        'phone',
        'invoice_title',
        'invoice_tax_number',
        'invoice_tax_office',
    ];

    protected $appends = ['full_name', 'state_name', 'country_name', 'city_title'];


    public function customer()
    {
        return $this->belongsTo(User::class);
    }


    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }


    public function getStateNameAttribute()
    {
        return State::name($this->country, $this->state);
    }


    public function getCountryNameAttribute()
    {
        return Country::name($this->country);
    }

    public function getCityTitleAttribute()
    {
        $name = $this->city;
        if ($name === null || $name === '') {
            return $name;
        }
        $mapUpperToLower = [
            'I' => 'ı', 'İ' => 'i', 'Ç' => 'ç', 'Ş' => 'ş', 'Ğ' => 'ğ', 'Ü' => 'ü', 'Ö' => 'ö',
        ];
        $s = strtr($name, $mapUpperToLower);
        $s = mb_strtolower($s, 'UTF-8');
        $parts = preg_split('/([\s\-]+)/u', $s, -1, PREG_SPLIT_DELIM_CAPTURE);
        $mapLowerToUpper = [
            'i' => 'İ', 'ı' => 'I', 'ç' => 'Ç', 'ş' => 'Ş', 'ğ' => 'Ğ', 'ü' => 'Ü', 'ö' => 'Ö',
        ];
        $res = '';
        foreach ($parts as $idx => $p) {
            if ($idx % 2 === 0) {
                if ($p === '') { $res .= $p; continue; }
                $first = mb_substr($p, 0, 1, 'UTF-8');
                $rest = mb_substr($p, 1, null, 'UTF-8');
                $firstU = $mapLowerToUpper[$first] ?? mb_strtoupper($first, 'UTF-8');
                $res .= $firstU . $rest;
            } else {
                $res .= $p;
            }
        }
        return $res;
    }
}
