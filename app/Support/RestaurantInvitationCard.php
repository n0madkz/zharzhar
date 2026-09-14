<?php

namespace App\Support;

use App\Models\Restaurant;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class RestaurantInvitationCard
{
    public function message(Restaurant $restaurant): string
    {
        return implode("\n", [
            'Здравствуйте! Я забронировал(а) мероприятие в ресторане «'.$restaurant->name.'» и хочу заказать онлайн-приглашение.',
            '',
            'Ресторан: '.$restaurant->name,
            'Код ресторана: #'.$restaurant->getKey(),
        ]);
    }

    public function whatsappUrl(Restaurant $restaurant): string
    {
        $phone = preg_replace('/\D+/', '', (string) config('store.partner_invitation_whatsapp_phone'));

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($this->message($restaurant));
    }

    public function qrDataUrl(Restaurant $restaurant): string
    {
        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'outputBase64' => true,
            'eccLevel' => EccLevel::M,
            'addQuietzone' => true,
            'connectPaths' => true,
        ]);

        return (new QRCode($options))->render($this->whatsappUrl($restaurant));
    }
}
