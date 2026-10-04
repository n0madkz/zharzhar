    @if($supportsPhotos && $photoGallery)
        <section class="invite-section invite-photo-story" data-reveal>
            @if($isPhotoStory)<p class="story-chapter-label">{{ $copy['story_moment'] }}</p>@endif
            <p class="invite-overline">{{ $galleryTitle }}</p>
            <div class="invite-photo-grid" data-photo-count="{{ count($photoGallery) }}" @if($isPhotoStory) id="story-gallery" data-story-gallery tabindex="0" aria-label="{{ $galleryTitle }}" @endif>
                @foreach($photoGallery as $photo)
                    <figure><img src="{{ $photo }}" alt="{{ $kk ? 'Шақыру фотосы' : 'Фотография приглашения' }} {{ $loop->iteration }}" loading="lazy">@if($isPhotoStory)<span class="story-photo-index" aria-hidden="true">0{{ $loop->iteration }}</span>@endif</figure>
                @endforeach
            </div>
            @if($isPhotoStory && count($photoGallery) > 1)
                <div class="story-gallery-controls" aria-controls="story-gallery">
                    <button type="button" data-story-prev aria-label="{{ $kk ? 'Алдыңғы фото' : 'Предыдущее фото' }}">←</button>
                    <span class="story-gallery-count" aria-live="polite"><strong data-story-current>01</strong><span aria-hidden="true">/</span><span>{{ str_pad(count($photoGallery), 2, '0', STR_PAD_LEFT) }}</span></span>
                    <button type="button" data-story-next aria-label="{{ $kk ? 'Келесі фото' : 'Следующее фото' }}">→</button>
                </div>
            @endif
        </section>
    @endif
