<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class InvitationCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $base = [
            'event_date' => '2026-11-08',
            'event_time' => '18:00',
            'date_title' => 'Той салтанаты',
            'venue_title' => 'Мекенжайымыз',
            'venue_name' => 'Салтанат сарайы',
            'venue_address' => 'Алматы қаласы, Абай даңғылы, 50',
            'countdown_title' => 'Салтанатқа дейін',
            'hosts_title' => 'Той иелері',
            'hosts_name' => 'Қуаныш иелері',
            'rsvp_title' => 'Сізді күтеміз!',
            'rsvp_hint' => 'Тойға қатысуыңызды растауыңызды сұраймыз.',
            'closing_text' => 'Қуанышымызға ортақ болыңыз!',
        ];

        foreach ([
            ['Жұмсақ жасыл', 'sage-wedding', 'wedding', 'sage-modern', 7990, '/invitation-assets/modern-sage-wedding.webp', [
                'title' => 'Жұмсақ жасыл', 'event_label' => 'ҮЙЛЕНУ ТОЙЫ',
                'intro_title' => 'ЖАҢА ӨМІРДІҢ ЖАРҚЫН БАСТАУЫ',
                'invitation_text' => 'Ақ ниет пен нәзік сезім тоғысқан тойымыздың қадірлі қонағы болыңыз.',
                'date_title' => 'Үйлену салтанаты',
                'venue_name' => 'Жасыл Сарай', 'hosts_name' => 'Ақ тілек иелері',
            ]],
            ['Қызғылт бақ', 'rose-wedding', 'wedding', 'rose-modern', 8990, '/invitation-assets/modern-rose-wedding.webp', [
                'title' => 'Қызғылт бақ', 'event_label' => 'ҮЙЛЕНУ ТОЙЫ',
                'intro_title' => 'ГҮЛДЕЙ ҚҰЛПЫРҒАН МАХАББАТ',
                'invitation_text' => 'Қызғылт гүлдердей жайнаған қуанышымызды бірге бөлісуге шын жүректен шақырамыз.',
                'date_title' => 'Махаббат салтанаты',
                'venue_name' => 'Гүлзар мейрамханасы', 'hosts_name' => 'Қуаныш иелері',
            ]],
            ['Кешкі алтын', 'gold-wedding', 'wedding', 'evening-modern', 10990, '/invitation-assets/modern-evening-wedding.webp', [
                'title' => 'Кешкі алтын', 'event_label' => 'ҮЙЛЕНУ ТОЙЫ',
                'intro_title' => 'АЛТЫН ШУАҚТЫ САЛТАНАТТЫ КЕШ',
                'invitation_text' => 'Шырақ сәулесі мен алтын өрнекке бөленген ерекше кешіміздің қадірлі қонағы болыңыз.',
                'date_title' => 'Салтанатты кеш',
                'venue_name' => 'Алтын Шаңырақ', 'hosts_name' => 'Той иелері',
            ]],
            ['Дәстүрлі мерейтой', 'classic-anniversary', 'anniversary', 'heritage-modern', 8990, '/invitation-assets/modern-traditional-anniversary.webp', [
                'title' => 'Дәстүрлі мерейтой', 'event_label' => 'МЕРЕЙТОЙ',
                'intro_title' => 'ДӘСТҮР МЕН БЕРЕКЕ ТОҒЫСҚАН МЕРЕЙ',
                'invitation_text' => 'Ғибратты ғұмырдың мерейлі белесін ұлттық дәстүр мен ақ дастархан басында бірге атап өтуге шақырамыз.',
                'date_title' => 'Мерейтой салтанаты',
                'venue_name' => 'Мирас салтанат сарайы', 'hosts_title' => 'Шақырушы отбасы',
                'hosts_name' => 'Балалары мен немерелері', 'rsvp_hint' => 'Мерейтойға қатысуыңызды растауыңызды сұраймыз.',
            ]],
            ['Алтын мерейтой', 'gold-anniversary', 'anniversary', 'golden-jubilee', 11990, '/invitation-assets/modern-gold-anniversary.webp', [
                'title' => 'Алтын мерейтой', 'event_label' => 'АЛТЫН МЕРЕЙТОЙ', 'jubilee_number' => '50',
                'intro_title' => 'АЛТЫН БЕЛЕС — АЙШЫҚТЫ ӨМІР',
                'invitation_text' => 'Елу жылдық өнегелі жолдың қуанышын жақындарымызбен бірге бөлісіп, ақ тілегіңізді қабыл алуға шақырамыз.',
                'date_title' => 'Алтын мерейтой кеші',
                'venue_name' => 'Алтын Ғасыр залы', 'hosts_title' => 'Шақырушы отбасы',
                'hosts_name' => 'Балалары мен немерелері', 'rsvp_hint' => 'Мерейтойға қатысуыңызды растауыңызды сұраймыз.',
            ]],
            ['Шаттықты туған күн', 'happy-birthday', 'birthday', 'sky-birthday', 7990, '/invitation-assets/modern-birthday.webp', [
                'title' => 'Шаттықты туған күн', 'event_label' => 'ТУҒАН КҮН',
                'intro_title' => 'ҚУАНЫШҚА ТОЛЫ ЖАҢА ЖАС',
                'invitation_text' => 'Ашық аспандай жарқын көңіл мен шат күлкіге толы туған күн кешінің қонағы болыңыз.',
                'date_title' => 'Туған күн кеші',
                'venue_name' => 'Шаттық залы', 'hosts_title' => 'Шақырушы',
                'hosts_name' => 'Туған күн иесі', 'rsvp_hint' => 'Кешке қатысуыңызды растауыңызды сұраймыз.',
                'closing_text' => 'Шаттықты күнімізді бірге қарсы алайық!',
            ]],
            ['Ақ інжу', 'ak-inju', 'wedding', 'pearl', 7990, '/invitation-assets/pearl-ethno.webp', [
                'title' => 'Ақ інжу', 'event_label' => 'ҮЙЛЕНУ ТОЙЫ',
                'intro_title' => 'АҚ ТІЛЕКПЕН БАСТАЛҒАН ӘСЕМ КҮН',
                'invitation_text' => 'Ақ інжудей таза сезімнің куәсі болып, қуанышымызды бірге бөлісуге шақырамыз.',
            ]],
            ['Алтын салтанат', 'royal-kesh', 'wedding', 'royal', 8990, '/invitation-assets/royal-ethno.webp', [
                'title' => 'Алтын салтанат', 'event_label' => 'ҮЙЛЕНУ ТОЙЫ',
                'intro_title' => 'ДОМБЫРА ҮНІМЕН ӨРІЛГЕН ҚУАНЫШ',
                'invitation_text' => 'Алтын өрнекті ақ дастархан басында өтетін салтанатты тойымыздың қадірлі қонағы болыңыз.',
                'venue_name' => 'Алтын Орда сарайы',
            ]],
            ['Нәзік гүлдер', 'nazik-botanika', 'wedding', 'botanical', 9990, '/invitation-assets/botanical-ethno.webp', [
                'title' => 'Нәзік гүлдер', 'event_label' => 'ҮЙЛЕНУ ТОЙЫ',
                'intro_title' => 'ДАЛА ГҮЛІНДЕЙ НӘЗІК СЕЗІМ',
                'invitation_text' => 'Көктем гүліндей құлпырған қуанышымызға ортақ болып, ақ тілегіңізді арнауға шақырамыз.',
                'venue_name' => 'Жасыл бақ мейрамханасы',
            ]],
            ['Ақ жібек', 'ak-zhibek', 'wedding', 'silk', 10990, '/invitation-assets/silk-ethno.webp', [
                'title' => 'Ақ жібек', 'event_label' => 'ҮЙЛЕНУ ТОЙЫ',
                'intro_title' => 'АҚ ЖІБЕКТЕЙ АЯУЛЫ СӘТ',
                'invitation_text' => 'Ұлттық нақыш пен ақ тілектің үйлескен кешінде сізді құрметті қонағымыз ретінде күтеміз.',
                'venue_name' => 'Ақ Отау мейрамханасы',
            ]],
            ['Дала мұрасы', 'altyn-nomad', 'wedding', 'nomad', 11990, '/invitation-assets/nomad-horse.webp', [
                'title' => 'Дала мұрасы', 'event_label' => 'ҮЙЛЕНУ ТОЙЫ',
                'intro_title' => 'ҰЛЫ ДАЛА РУХЫМЕН ӨРІЛГЕН ТОЙ',
                'invitation_text' => 'Тұлпар тұяғының дүбіріндей қуанышты күнімізге келіп, ақ баталарыңызды беріңіздер.',
                'venue_name' => 'Ұлы Дала салтанат сарайы',
            ]],
            ['Мерейлі шеңбер', 'mereyli-shenber', 'anniversary', 'jubilee', 9990, '/invitation-assets/jubilee-ethno.webp', [
                'title' => 'Мерейлі шеңбер', 'event_label' => 'МЕРЕЙТОЙ',
                'intro_title' => 'МЕРЕЙЛІ ЖАС — МӘНДІ ҒҰМЫР',
                'invitation_text' => 'Өмір жолының мерейлі белесін бірге атап өтіп, ақ тілегіңізді білдіруге шақырамыз.',
                'date_title' => 'Мерейтой кеші',
                'hosts_title' => 'Шақырушы отбасы', 'hosts_name' => 'Балалары мен немерелері',
                'rsvp_hint' => 'Мерейтойға қатысуыңызды растауыңызды сұраймыз.',
            ]],
            ['Ару қыз ұзату', 'aru-qyz-uzatu', 'qyz_uzatu', 'qyz-modern', 10990, '/invitation-assets/modern-qyz-uzatu.webp', [
                'title' => 'Ару қыз ұзату', 'event_label' => 'ҚЫЗ ҰЗАТУ',
                'intro_title' => 'АҚ БОСАҒАДАН АҚ ЖОЛМЕН',
                'invitation_text' => 'Аяулы қызымыздың жаңа өмірге қадам басар қыз ұзату тойына арналған ақ дастарханымызға шақырамыз.',
                'date_title' => 'Қыз ұзату салтанаты',
                'venue_name' => 'Ақ Босаға мейрамханасы', 'hosts_title' => 'Қыздың ата-анасы',
                'hosts_name' => 'Ақ тілек иелері', 'closing_text' => 'Ақ жол тілеп, қуанышымызға ортақ болыңыз!',
            ]],
            ['Дала шаттығы', 'dala-shattygy', 'birthday', 'birthday', 8990, '/invitation-assets/birthday-ethno.webp', [
                'title' => 'Дала шаттығы', 'event_label' => 'ТУҒАН КҮН',
                'intro_title' => 'ШАТТЫҚҚА ТОЛЫ ЕРЕКШЕ КҮН',
                'invitation_text' => 'Кең даладай көңілімізді қуанышқа бөлеген туған күн кешінің қадірлі қонағы болыңыз.',
                'date_title' => 'Туған күн кеші',
                'venue_name' => 'Көк Жайлау мейрамханасы', 'hosts_title' => 'Шақырушы',
                'hosts_name' => 'Туған күн иесі', 'rsvp_hint' => 'Кешке қатысуыңызды растауыңызды сұраймыз.',
                'closing_text' => 'Шаттықты күнімізді бірге қарсы алайық!',
            ]],
            ['Махаббат хикаясы', 'mahabbat-hikayasy', 'wedding', 'photo-story', 12990, '/invitation-assets/wedding-hands.webp', [
                'title' => 'Махаббат хикаясы', 'event_label' => 'ҮЙЛЕНУ ТОЙЫ',
                'intro_title' => 'ЕКІ ЖҮРЕКТІҢ БІР ХИКАЯСЫ',
                'invitation_text' => 'Өміріміздің ең әдемі тарауын сіздермен бірге бастап, қуанышымыздың қадірлі қонағы болуға шақырамыз.',
                'date_title' => 'Біздің ерекше күніміз', 'gallery_title' => 'Біздің ерекше сәттеріміз',
                'venue_name' => 'Ақ Отау мейрамханасы', 'hosts_title' => 'Той иелері',
                'hosts_name' => 'Қуаныш иелері', 'closing_text' => 'Махаббат хикаямызға ортақ болыңыз!',
                '_settings' => [
                    'supports_photos' => true,
                    'sample_photos' => [
                        '/invitation-assets/wedding-hands.webp',
                        '/invitation-assets/botanical-wedding.webp',
                        '/invitation-assets/modern-evening-wedding.webp',
                    ],
                ],
            ]],
        ] as [$name, $slug, $event, $theme, $price, $image, $content]) {
            $settings = $content['_settings'] ?? [];
            unset($content['_settings']);
            $content = array_replace($base, $content);
            Template::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'category' => $event,
                'event_type' => $event,
                'price' => $price,
                'preview_image' => $image,
                'config_json' => array_replace(['theme' => $theme, 'sample_names' => $name, 'content_kk' => $content], $settings),
                'is_active' => true,
            ]);
        }
    }
}
