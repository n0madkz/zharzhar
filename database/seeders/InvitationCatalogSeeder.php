<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class InvitationCatalogSeeder extends Seeder
{
    public function run(): void
    {
        Template::whereIn('slug', [
            'sage-wedding', 'rose-wedding', 'gold-wedding',
            'classic-anniversary', 'gold-anniversary', 'happy-birthday',
        ])->update(['is_active' => false]);
        foreach ([
            'sage-wedding' => 'Жұмсақ жасыл', 'rose-wedding' => 'Қызғылт бақ',
            'gold-wedding' => 'Кешкі алтын', 'classic-anniversary' => 'Дәстүрлі мерейтой',
            'gold-anniversary' => 'Алтын мерейтой', 'happy-birthday' => 'Шаттықты туған күн',
        ] as $slug => $name) {
            Template::where('slug', $slug)->update(['name' => $name]);
        }

        $base = [
            'event_date' => '2026-11-08',
            'event_time' => '18:00',
            'date_title' => 'Той салтанаты',
            'program_title' => 'Той бағдарламасы',
            'welcome_text' => 'Қонақтардың жиналуы',
            'ceremony_text' => 'Салтанатты рәсім',
            'celebration_text' => 'Мерекелік кеш',
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
                'date_title' => 'Мерейтой кеші', 'ceremony_text' => 'Мерейтой иесін қарсы алу',
                'hosts_title' => 'Шақырушы отбасы', 'hosts_name' => 'Балалары мен немерелері',
                'rsvp_hint' => 'Мерейтойға қатысуыңызды растауыңызды сұраймыз.',
            ]],
            ['Ару қыз ұзату', 'aru-qyz-uzatu', 'qyz_uzatu', 'qyz', 10990, '/invitation-assets/qyz-ethno.webp', [
                'title' => 'Ару қыз ұзату', 'event_label' => 'ҚЫЗ ҰЗАТУ',
                'intro_title' => 'АҚ БОСАҒАДАН АҚ ЖОЛМЕН',
                'invitation_text' => 'Аяулы қызымыздың жаңа өмірге қадам басар қыз ұзату тойына арналған ақ дастарханымызға шақырамыз.',
                'date_title' => 'Қыз ұзату салтанаты', 'ceremony_text' => 'Қыз ұзату рәсімі',
                'venue_name' => 'Ақ Босаға мейрамханасы', 'hosts_title' => 'Қыздың ата-анасы',
                'hosts_name' => 'Ақ тілек иелері', 'closing_text' => 'Ақ жол тілеп, қуанышымызға ортақ болыңыз!',
            ]],
            ['Дала шаттығы', 'dala-shattygy', 'birthday', 'birthday', 8990, '/invitation-assets/birthday-ethno.webp', [
                'title' => 'Дала шаттығы', 'event_label' => 'ТУҒАН КҮН',
                'intro_title' => 'ШАТТЫҚҚА ТОЛЫ ЕРЕКШЕ КҮН',
                'invitation_text' => 'Кең даладай көңілімізді қуанышқа бөлеген туған күн кешінің қадірлі қонағы болыңыз.',
                'date_title' => 'Туған күн кеші', 'ceremony_text' => 'Туған күн иесін қарсы алу',
                'venue_name' => 'Көк Жайлау мейрамханасы', 'hosts_title' => 'Шақырушы',
                'hosts_name' => 'Туған күн иесі', 'rsvp_hint' => 'Кешке қатысуыңызды растауыңызды сұраймыз.',
                'closing_text' => 'Шаттықты күнімізді бірге қарсы алайық!',
            ]],
        ] as [$name, $slug, $event, $theme, $price, $image, $content]) {
            $content = array_replace($base, $content);
            Template::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'category' => $event,
                'event_type' => $event,
                'price' => $price,
                'preview_image' => $image,
                'config_json' => ['theme' => $theme, 'sample_names' => $name, 'content_kk' => $content],
                'is_active' => true,
            ]);
        }
    }
}
