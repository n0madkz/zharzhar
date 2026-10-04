    <section class="invite-section rsvp-section" @if($isBesikToi) id="besik-rsvp" @endif data-reveal>
        <p class="invite-script">{{ $copy['rsvp'] }}</p>
        <p>{{ $copy['hint'] }}</p>
        @if($preview)
            <div class="rsvp-preview">
                <span>{{ $copy['name'] }}</span>
                <fieldset class="attendance-choice" disabled><legend>{{ $copy['answer'] }}</legend><label><input type="radio"><b>{{ $copy['yes'] }}</b></label><label><input type="radio"><b>{{ $copy['no'] }}</b></label></fieldset>
                @if($isPhotoStory)<label data-guest-count>{{ $copy['count'] }}<input type="number" min="1" max="20" value="1" disabled></label>@endif
                <button type="button" disabled>{{ $copy['send'] }}</button>
            </div>
            <p class="rsvp-note">{{ $copy['preview_form'] }}</p>
        @else
            @if($errors->any())<p class="error" role="alert">{{ $kk ? 'Өрістерді тексеріңіз.' : 'Проверьте поля формы.' }}</p>@endif
            <form method="POST" action="{{ route('store.rsvp', $invitation->slug) }}" class="invite-form" data-submit-once>
                @csrf
                <label>{{ $copy['name'] }}<input name="guest_name" value="{{ old('guest_name') }}" autocomplete="name" maxlength="120" required></label>
                <fieldset class="attendance-choice"><legend>{{ $copy['answer'] }}</legend>@foreach(['yes','no'] as $status)<label><input type="radio" name="attendance_status" value="{{ $status }}" @checked(old('attendance_status', 'yes') === $status) required><b>{{ $copy[$status] }}</b></label>@endforeach</fieldset>
                <label data-guest-count>{{ $copy['count'] }}<input type="number" name="guest_count" min="1" max="20" value="{{ old('guest_count', 1) }}" required></label>
                <label>{{ $copy['message'] }}<textarea name="message" maxlength="1000">{{ old('message') }}</textarea></label>
                <button type="submit">{{ $copy['send'] }}</button>
            </form>
        @endif
    </section>
