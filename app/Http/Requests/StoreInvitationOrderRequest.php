<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvitationOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'request_key' => ['required', 'uuid'],
            'template_id' => ['required', 'integer', Rule::exists('templates', 'id')->where('is_active', true)],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'regex:/^\\+?[0-9 ()-]{10,25}$/'],
            'event_type' => ['required', Rule::in(array_keys(config('store.event_types')))],
            'names' => ['required', 'string', 'max:160'],
            'hosts' => ['required', 'string', 'max:240'],
            'event_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'event_time' => ['required', 'date_format:H:i'],
            'restaurant_id' => ['nullable', 'integer', Rule::exists('restaurants', 'id')->where('status', 'active')],
            'venue_name' => ['required', 'string', 'max:160'],
            'venue_address' => ['required', 'string', 'max:255'],
            'language' => ['required', Rule::in(['ru', 'kk'])],
            'music_id' => ['nullable', 'integer', Rule::exists('music', 'id')->where('is_active', true)],
            'invitation_text' => ['nullable', 'string', 'max:2000'],
            'promo_code' => ['nullable', 'string', 'max:40', 'regex:/^[A-Za-z0-9-]+$/'],
        ];
    }

    public function messages(): array
    {
        return ['required' => 'Заполните это поле.', 'exists' => 'Выбранный вариант больше недоступен.',
            'event_date.after_or_equal' => 'Выберите сегодняшнюю или будущую дату.',
            'customer_phone.regex' => 'Укажите телефон, например +7 700 123 45 67.',
            'max' => 'Слишком длинное значение.', 'date_format' => 'Проверьте формат даты или времени.'];
    }
}
