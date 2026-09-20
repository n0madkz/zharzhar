<?php

namespace Tests\Feature;

use App\Models\BrokerVenue;
use App\Models\Restaurant;
use App\Models\User;
use App\Support\BrokerDirectory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BrokerTest extends TestCase
{
    use RefreshDatabase;

    private function venue(array $attributes = []): BrokerVenue
    {
        return BrokerVenue::create(array_merge([
            'source_id' => '700000010001', 'name' => 'Ақ сарай', 'city' => 'Алматы',
            'district' => 'Бостандыкский район', 'address' => 'Абая, 1',
            'latitude' => 43.22, 'longitude' => 76.91, 'phone' => '77001234567',
            'two_gis_url' => 'https://2gis.kz/firm/700000010001',
        ], $attributes));
    }

    public function test_broker_requires_login_role_and_correct_domain(): void
    {
        $this->get('https://broker.zharzhar.kz/')->assertRedirect('/broker');
        $this->get('https://broker.zharzhar.kz/broker')->assertRedirect();
        $partner = User::factory()->create(['role' => 'partner']);
        $this->actingAs($partner)->get('https://broker.zharzhar.kz/broker')->assertForbidden();
        $broker = User::factory()->create(['role' => 'broker']);
        $this->actingAs($broker)->get('https://partner.zharzhar.kz/broker')->assertNotFound();
        $this->get('https://broker.zharzhar.kz/broker')->assertOk()->assertSee('Банкетные залы');
        $this->get('https://admin.zharzhar.kz/admin/brokers')->assertForbidden();
    }

    public function test_real_parser_schema_import_preserves_deals_and_handles_suffixes(): void
    {
        $venue = $this->venue(['completed_at' => now()]);
        $payload = [[
            'id' => $venue->source_id.'_sessionSuffix', 'name_ex' => ['primary' => 'Новое название'],
            'adm_div' => [['type' => 'city', 'name' => 'Алматы'], ['type' => 'district', 'name' => 'Район 1']],
            'address_name' => 'Абая, 2', 'point' => ['lat' => 43.2, 'lon' => 76.9],
            'contact_groups' => [['contacts' => [['type' => 'phone', 'value' => '77001112233']]]],
        ]];
        $broker = User::factory()->create(['role' => 'broker']);
        $this->actingAs($broker)->post('https://broker.zharzhar.kz/broker/import', [
            'city' => 'Алматы', 'file' => UploadedFile::fake()->createWithContent('halls.json', json_encode($payload)),
        ])->assertRedirect('/broker');
        $this->assertDatabaseCount('broker_venues', 1);
        $this->assertSame('Новое название', $venue->fresh()->name);
        $this->assertNotNull($venue->fresh()->completed_at);
        $this->assertSame(43.2, $venue->fresh()->latitude);
        $this->assertSame('Алматы', $broker->fresh()->broker_city);
        $this->post('https://broker.zharzhar.kz/broker/import', [
            'city' => 'Алматы', 'file' => UploadedFile::fake()->createWithContent('invalid.json', '{bad'),
        ])->assertSessionHasErrors('file');
    }

    public function test_registration_creates_real_partner_and_cannot_be_repeated(): void
    {
        $broker = User::factory()->create(['role' => 'broker']);
        $venue = $this->venue();
        $form = ['email' => 'hall@example.test', 'phone' => '8 (700) 123-45-67', 'password' => 'test-password', 'password_confirmation' => 'test-password'];
        $this->actingAs($broker)->post('https://broker.zharzhar.kz/broker/venues/'.$venue->id.'/register', $form)->assertSessionHasNoErrors()->assertRedirect();
        $partner = User::where('email', $form['email'])->firstOrFail();
        $this->assertSame('partner', $partner->role);
        $this->assertTrue(Hash::check($form['password'], $partner->password));
        $this->assertSame('+77001234567', $partner->phone);
        $restaurant = Restaurant::firstOrFail();
        $this->assertSame(3, $restaurant->slots()->count());
        $this->assertSame($restaurant->id, $venue->fresh()->restaurant_id);
        $this->assertNotNull($venue->fresh()->completed_at);
        $form['email'] = 'second@example.test';
        $this->post('https://broker.zharzhar.kz/broker/venues/'.$venue->id.'/register', $form)->assertSessionHasErrors('email');
        $this->assertDatabaseCount('restaurants', 1);
        $this->assertDatabaseMissing('users', ['email' => 'second@example.test']);
    }

    public function test_existing_partner_is_matched_without_cross_city_false_positive(): void
    {
        $venue = $this->venue();
        $partner = User::factory()->create(['role' => 'partner']);
        $restaurant = Restaurant::create(['name' => $venue->name, 'city' => $venue->city, 'address' => $venue->address, 'partner_user_id' => $partner->id]);
        $directory = new BrokerDirectory;
        $this->assertSame($restaurant->id, $directory->match($venue, Restaurant::all())?->id);
        $restaurant->update(['city' => 'Астана']);
        $this->assertNull($directory->match($venue, Restaurant::all()));
        $restaurant->update(['two_gis_url' => $venue->two_gis_url]);
        $this->assertSame($restaurant->id, $directory->match($venue, Restaurant::all())?->id);
    }

    public function test_city_is_saved_and_completion_is_explicit_and_idempotent(): void
    {
        $broker = User::factory()->create(['role' => 'broker']);
        $venue = $this->venue();
        $this->actingAs($broker)->post('https://broker.zharzhar.kz/broker/settings', ['city' => 'Алматы'])->assertRedirect('/broker');
        $this->assertSame('Алматы', $broker->fresh()->broker_city);
        $this->get('https://broker.zharzhar.kz/broker')->assertOk()->assertViewHas('venues', fn ($venues) => $venues->count() === 1);
        $url = 'https://broker.zharzhar.kz/broker/venues/'.$venue->id.'/complete';
        $this->postJson($url, ['completed' => true])->assertOk()->assertJson(['completed' => true]);
        $this->postJson($url, ['completed' => true])->assertOk()->assertJson(['completed' => true]);
        $this->assertSame($broker->id, $venue->fresh()->completed_by);
        $this->postJson($url, ['completed' => false])->assertOk()->assertJson(['completed' => false]);
        $this->assertNull($venue->fresh()->completed_by);
    }
}
