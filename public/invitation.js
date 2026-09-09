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
});
