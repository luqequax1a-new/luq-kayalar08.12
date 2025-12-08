<?php

return [

    'master' => [
        'max_width' => 1800,
        'max_height' => 1800,
        'jpeg_quality' => 78,
        'png_compression_level' => 7,
        'convert_opaque_png_to_jpeg' => true,
        'jpeg_background_color' => [255, 255, 255],
        'fix_orientation' => true,
    ],

    'variants' => [
        'widths' => [
            'thumb' => 80,
            'grid' => 400,
            'detail' => 1000,
        ],
        'jpeg_quality' => 82,
        'webp_quality' => 78,
        'avif_quality' => 72,
        'enable_avif' => true,
    ],

];
