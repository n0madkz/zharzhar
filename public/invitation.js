document.addEventListener('DOMContentLoaded', () => {
  const reveals = document.querySelectorAll('[data-reveal]');
  if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const observer = new IntersectionObserver(entries => entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      }
    }), { threshold: 0.12 });
    reveals.forEach(element => observer.observe(element));
  } else {
    reveals.forEach(element => element.classList.add('is-visible'));
  }

  const storyGallery = document.querySelector('[data-story-gallery]');
  if (storyGallery) {
    const photos = [...storyGallery.querySelectorAll('figure')];
    const previous = document.querySelector('[data-story-prev]');
    const next = document.querySelector('[data-story-next]');
    const current = document.querySelector('[data-story-current]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const activeIndex = () => Math.min(photos.length - 1, Math.max(0, Math.round(storyGallery.scrollLeft / storyGallery.clientWidth)));
    const updateGallery = () => {
      const index = activeIndex();
      if (current) current.textContent = String(index + 1).padStart(2, '0');
      if (previous) previous.disabled = index === 0;
      if (next) next.disabled = index === photos.length - 1;
    };
    const showPhoto = index => storyGallery.scrollTo({ left: photos[index].offsetLeft - photos[0].offsetLeft, behavior: reducedMotion ? 'auto' : 'smooth' });
    previous?.addEventListener('click', () => showPhoto(Math.max(0, activeIndex() - 1)));
    next?.addEventListener('click', () => showPhoto(Math.min(photos.length - 1, activeIndex() + 1)));
    storyGallery.addEventListener('scroll', updateGallery, { passive: true });
    updateGallery();
  }

  const attendanceChoices = document.querySelectorAll('input[name="attendance_status"]');
  const guestCountField = document.querySelector('[data-guest-count]');
  const guestCountInput = guestCountField?.querySelector('input');
  const syncGuestCount = () => {
    const selected = document.querySelector('input[name="attendance_status"]:checked');
    const isNotAttending = selected?.value === 'no';
    if (guestCountField) guestCountField.hidden = isNotAttending;
    if (guestCountInput && isNotAttending) guestCountInput.value = '1';
  };
  attendanceChoices.forEach(choice => choice.addEventListener('change', syncGuestCount));
  syncGuestCount();

  const countdown = document.querySelector('[data-countdown]');
  if (countdown) {
    const target = new Date(countdown.dataset.countdown).getTime();
    const renderCountdown = () => {
      const distance = Math.max(0, target - Date.now());
      const values = {
        days: Math.floor(distance / 86400000),
        hours: Math.floor(distance / 3600000) % 24,
        minutes: Math.floor(distance / 60000) % 60,
        seconds: Math.floor(distance / 1000) % 60,
      };
      Object.entries(values).forEach(([part, value]) => {
        const output = countdown.querySelector(`[data-countdown-part="${part}"]`);
        if (output) output.textContent = String(value).padStart(2, '0');
      });
    };
    renderCountdown();
    window.setInterval(renderCountdown, 1000);
  }

  const musicButton = document.querySelector('[data-invite-music]');
  const audio = document.querySelector('#invite-audio');
  let audioContext;
  let toneTimer;
  let toneStep = 0;

  const setMusicState = playing => {
    musicButton?.classList.toggle('is-playing', playing);
    musicButton?.classList.remove('has-error');
    if (musicButton) {
      musicButton.setAttribute('aria-label', playing ? musicButton.dataset.pauseLabel : musicButton.dataset.playLabel);
      musicButton.setAttribute('aria-pressed', String(playing));
    }
  };

  const playTone = () => {
    if (!audioContext) return;
    const melody = [261.63, 329.63, 392, 329.63, 293.66, 349.23, 440, 349.23];
    const now = audioContext.currentTime;
    const oscillator = audioContext.createOscillator();
    const gain = audioContext.createGain();
    oscillator.type = 'sine';
    oscillator.frequency.value = melody[toneStep % melody.length];
    gain.gain.setValueAtTime(0, now);
    gain.gain.linearRampToValueAtTime(0.055, now + 0.08);
    gain.gain.exponentialRampToValueAtTime(0.001, now + 1.15);
    oscillator.connect(gain).connect(audioContext.destination);
    oscillator.start(now);
    oscillator.stop(now + 1.2);
    toneStep += 1;
  };

  musicButton?.addEventListener('click', async () => {
    if (audio) {
      if (audio.paused) {
        try {
          await audio.play();
          setMusicState(true);
        } catch {
          setMusicState(false);
          musicButton.classList.add('has-error');
        }
      } else {
        audio.pause();
        setMusicState(false);
      }
      return;
    }

    if (audioContext) {
      window.clearInterval(toneTimer);
      await audioContext.close();
      audioContext = null;
      toneStep = 0;
      setMusicState(false);
      return;
    }

    const AudioContext = window.AudioContext || window.webkitAudioContext;
    if (!AudioContext) return;
    audioContext = new AudioContext();
    playTone();
    toneTimer = window.setInterval(playTone, 760);
    setMusicState(true);
  });

  audio?.addEventListener('play', () => setMusicState(true));
  audio?.addEventListener('pause', () => setMusicState(false));
  audio?.addEventListener('ended', () => setMusicState(false));
  audio?.addEventListener('error', () => {
    setMusicState(false);
    musicButton?.classList.add('has-error');
  });
});
