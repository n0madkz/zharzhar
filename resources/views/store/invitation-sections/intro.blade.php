    <section class="invite-section invite-intro" data-reveal>
        @if($template->event_type === 'wedding')<span class="invite-wedding-rings invite-wedding-rings--section" aria-hidden="true"><i></i><i></i></span>@else<div class="orbit-mark" aria-hidden="true"><i></i><i></i></div>@endif
        <p class="invite-small-title">{{ $copy['intro'] }}</p>
        <p class="invite-message">{{ $invitationText }}</p>
    </section>
