<?php

return [
    'public_url' => env('APP_URL', 'https://zharzhar.kz'),
    'admin_domain' => env('STORE_ADMIN_DOMAIN', 'admin.zharzhar.kz'),
    'kaspi_phone' => env('STORE_KASPI_PHONE', '+7 778 736 78 50'),
    'whatsapp_phone' => env('STORE_WHATSAPP_PHONE', '+7 778 736 78 50'),
    'admin_login' => env('STORE_ADMIN_LOGIN', 'Admin1601'),
    'admin_email' => env('STORE_ADMIN_EMAIL', 'admin@zharzhar.local'),
    'event_types' => ['wedding' => 'Свадьба / Үйлену той', 'qyz_uzatu' => 'Қыз ұзату', 'anniversary' => 'Юбилей / Мерейтой', 'birthday' => 'День рождения / Туған күн'],
    'music_categories' => ['wedding' => 'Свадьба', 'qyz_uzatu' => 'Қыз ұзату', 'anniversary' => 'Юбилей', 'birthday' => 'День рождения'],
    'themes' => [
        'pearl' => 'Ақ інжу',
        'royal' => 'Royal кеш',
        'botanical' => 'Нәзік ботаника',
        'silk' => 'Ақ жібек',
        'nomad' => 'Алтын Nomad',
        'jubilee' => 'Мерейлі шеңбер',
        'qyz' => 'Қыз ұзату — Ару',
        'sage' => 'Нежный шалфей',
        'rose' => 'Розовый сад',
        'midnight' => 'Вечернее золото',
        'sand' => 'Тёплая классика',
    ],
];
