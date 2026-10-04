<?php

// Each design owns a composition: chapters group content into different visual scenes.
// Atomic sections keep map, countdown and RSVP behavior consistent across the catalog.
return [
    'sage-wedding' => [['kind' => 'letter', 'sections' => ['intro', 'hosts']], ['kind' => 'calendar', 'sections' => ['date']], ['kind' => 'destination', 'sections' => ['venue', 'countdown']], ['kind' => 'reply', 'sections' => ['rsvp']]],
    'rose-wedding' => [['kind' => 'bouquet', 'sections' => ['intro']], ['kind' => 'vow', 'sections' => ['hosts', 'countdown']], ['kind' => 'postcard', 'sections' => ['date', 'venue']], ['kind' => 'reply', 'sections' => ['rsvp']]],
    'gold-wedding' => [['kind' => 'evening', 'sections' => ['intro', 'venue']], ['kind' => 'ticket', 'sections' => ['date', 'countdown']], ['kind' => 'toast', 'sections' => ['hosts']], ['kind' => 'reply', 'sections' => ['rsvp']]],
    'classic-anniversary' => [['kind' => 'heritage', 'sections' => ['intro', 'hosts']], ['kind' => 'calendar', 'sections' => ['date', 'venue']], ['kind' => 'toast', 'sections' => ['countdown']], ['kind' => 'reply', 'sections' => ['rsvp']]],
    'gold-anniversary' => [['kind' => 'medal', 'sections' => ['intro', 'date']], ['kind' => 'toast', 'sections' => ['hosts', 'venue']], ['kind' => 'ticket', 'sections' => ['countdown']], ['kind' => 'reply', 'sections' => ['rsvp']]],
    'happy-birthday' => [['kind' => 'sky', 'sections' => ['intro', 'countdown']], ['kind' => 'ticket', 'sections' => ['date', 'venue']], ['kind' => 'signature', 'sections' => ['hosts']], ['kind' => 'reply', 'sections' => ['rsvp']]],
    'ak-inju' => [['kind' => 'pearl', 'sections' => ['intro']], ['kind' => 'calendar', 'sections' => ['date', 'countdown']], ['kind' => 'letter', 'sections' => ['hosts', 'venue']], ['kind' => 'reply', 'sections' => ['rsvp']]],
    'royal-kesh' => [['kind' => 'royal', 'sections' => ['intro', 'venue']], ['kind' => 'medal', 'sections' => ['hosts', 'date']], ['kind' => 'evening', 'sections' => ['countdown']], ['kind' => 'reply', 'sections' => ['rsvp']]],
    'nazik-botanika' => [['kind' => 'botanical', 'sections' => ['intro', 'hosts']], ['kind' => 'postcard', 'sections' => ['date', 'venue']], ['kind' => 'bouquet', 'sections' => ['countdown']], ['kind' => 'reply', 'sections' => ['rsvp']]],
    'ak-zhibek' => [['kind' => 'silk', 'sections' => ['intro', 'date']], ['kind' => 'letter', 'sections' => ['hosts', 'venue']], ['kind' => 'ribbon', 'sections' => ['countdown']], ['kind' => 'reply', 'sections' => ['rsvp']]],
    'altyn-nomad' => [['kind' => 'journey', 'sections' => ['intro', 'venue']], ['kind' => 'banner', 'sections' => ['countdown', 'date']], ['kind' => 'signature', 'sections' => ['hosts']], ['kind' => 'reply', 'sections' => ['rsvp']]],
    'mereyli-shenber' => [['kind' => 'family', 'sections' => ['intro', 'hosts']], ['kind' => 'calendar', 'sections' => ['date', 'countdown']], ['kind' => 'destination', 'sections' => ['venue']], ['kind' => 'reply', 'sections' => ['rsvp']]],
    'aru-qyz-uzatu' => [['kind' => 'farewell', 'sections' => ['intro', 'hosts']], ['kind' => 'ribbon', 'sections' => ['countdown', 'date']], ['kind' => 'destination', 'sections' => ['venue']], ['kind' => 'reply', 'sections' => ['rsvp']]],
    'dala-shattygy' => [['kind' => 'festival', 'sections' => ['intro', 'countdown']], ['kind' => 'banner', 'sections' => ['date', 'hosts']], ['kind' => 'destination', 'sections' => ['venue']], ['kind' => 'reply', 'sections' => ['rsvp']]],
    'mahabbat-hikayasy' => [['kind' => 'album', 'sections' => ['intro', 'photos']], ['kind' => 'calendar', 'sections' => ['date']], ['kind' => 'cinema', 'sections' => ['countdown']], ['kind' => 'letter', 'sections' => ['venue', 'hosts']], ['kind' => 'reply', 'sections' => ['rsvp']]],
];
