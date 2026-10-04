<?php

// Each catalog design has its own narrative sequence. The shared blocks retain RSVP, map,
// countdown, music and photo functionality while the page structure changes per design.
return [
    'sage-wedding' => ['intro', 'date', 'venue', 'countdown', 'hosts', 'rsvp'],
    'rose-wedding' => ['intro', 'hosts', 'date', 'countdown', 'venue', 'rsvp'],
    'gold-wedding' => ['intro', 'venue', 'date', 'hosts', 'countdown', 'rsvp'],
    'classic-anniversary' => ['intro', 'hosts', 'venue', 'date', 'countdown', 'rsvp'],
    'gold-anniversary' => ['intro', 'date', 'hosts', 'venue', 'countdown', 'rsvp'],
    'happy-birthday' => ['intro', 'countdown', 'date', 'venue', 'hosts', 'rsvp'],
    'ak-inju' => ['intro', 'date', 'countdown', 'hosts', 'venue', 'rsvp'],
    'royal-kesh' => ['intro', 'venue', 'hosts', 'date', 'countdown', 'rsvp'],
    'nazik-botanika' => ['intro', 'hosts', 'countdown', 'date', 'venue', 'rsvp'],
    'ak-zhibek' => ['intro', 'date', 'venue', 'hosts', 'countdown', 'rsvp'],
    'altyn-nomad' => ['intro', 'venue', 'countdown', 'date', 'hosts', 'rsvp'],
    'mereyli-shenber' => ['intro', 'hosts', 'date', 'venue', 'countdown', 'rsvp'],
    'aru-qyz-uzatu' => ['intro', 'hosts', 'venue', 'countdown', 'date', 'rsvp'],
    'dala-shattygy' => ['intro', 'countdown', 'hosts', 'date', 'venue', 'rsvp'],
    'mahabbat-hikayasy' => ['intro', 'photos', 'date', 'countdown', 'venue', 'hosts', 'rsvp'],
];
