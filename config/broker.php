<?php

return [
    'parser_python' => env('BROKER_PARSER_PYTHON', PHP_OS_FAMILY === 'Windows'
        ? storage_path('app/broker-parser/Scripts/python.exe')
        : storage_path('app/broker-parser/bin/python')),
    'parser_marker' => storage_path('app/broker-parser/.installed'),
    'chrome_path_file' => storage_path('app/broker-parser/chrome-path'),
    'browser_directory' => storage_path('app/broker-browser'),
    'cities' => [
        'Алматы' => 'almaty', 'Астана' => 'astana', 'Шымкент' => 'shymkent',
        'Атырау' => 'atyrau', 'Актобе' => 'aktobe', 'Актау' => 'aktau',
        'Караганда' => 'karaganda', 'Костанай' => 'kostanay', 'Павлодар' => 'pavlodar',
        'Семей' => 'semey', 'Тараз' => 'taraz', 'Уральск' => 'uralsk',
        'Усть-Каменогорск' => 'ustkam', 'Кызылорда' => 'kyzylorda',
        'Кокшетау' => 'kokshetau', 'Петропавловск' => 'petropavlovsk',
    ],
];
