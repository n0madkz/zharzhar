document.addEventListener('DOMContentLoaded', () => {
  const kk = document.documentElement.lang === 'kk';
  const copy = kk ? {
    copied: 'Көшірілді', copyFallback: 'Мәтінді белгілеп, көшіріңіз', applyHint: 'Жеңілдікті тексеру үшін «Қолдану» батырмасын басыңыз.',
    checking: 'Промокод тексерілуде…', promoError: 'Промокодты тексеру мүмкін болмады. Қайталап көріңіз.',
    applied: 'Промокод қолданылды.', noPromo: 'Промокодсыз баға.', saving: 'Сақталуда…',
  } : {
    copied: 'Скопировано', copyFallback: 'Выделите и скопируйте текст', applyHint: 'Нажмите «Применить», чтобы проверить скидку.',
    checking: 'Проверяем промокод…', promoError: 'Не удалось проверить промокод. Попробуйте ещё раз.',
    applied: 'Промокод применён.', noPromo: 'Цена без промокода.', saving: 'Сохраняем…',
  };
  document.querySelectorAll('[data-copy]').forEach(button => button.addEventListener('click', async () => {
    try { await navigator.clipboard.writeText(button.dataset.copy); button.textContent = copy.copied; }
    catch { button.textContent = copy.copyFallback; }
  }));
  document.querySelectorAll('[data-restaurant-picker]').forEach(picker => {
    const restaurantId = picker.querySelector('input[name="restaurant_id"]');
    const search = picker.querySelector('input[type="search"]');
    const suggestionList = search ? document.getElementById(search.getAttribute('list')) : null;
    const suggestions = Array.from(suggestionList?.querySelectorAll('option') || []);
    const hint = picker.querySelector('.restaurant-picker-hint');
    if (!restaurantId || !search) return;

    const findRestaurant = value => suggestions.find(option => option.value.localeCompare(value.trim(), undefined, { sensitivity: 'base' }) === 0);
    const syncRestaurant = fillVenue => {
      const match = findRestaurant(search.value);
      restaurantId.value = match?.dataset.id || '';
      picker.classList.toggle('restaurant-found', Boolean(match));
      if (hint) hint.textContent = match ? search.dataset.found : (search.value.trim() ? search.dataset.missing : hint.dataset.default || hint.textContent);
      if (match && fillVenue) {
        const venueName = document.querySelector('#venue_name');
        const venueAddress = document.querySelector('#venue_address');
        if (venueName) venueName.value = match.value;
        if (venueAddress) venueAddress.value = match.dataset.address || '';
      }
    };

    if (hint) hint.dataset.default = hint.textContent;
    search.addEventListener('input', () => syncRestaurant(true));
    search.addEventListener('change', () => syncRestaurant(true));
    syncRestaurant(false);
  });
  const music = document.querySelector('#music_id');
  const audio = document.querySelector('#music-preview');
  music?.addEventListener('change', () => {
    audio.pause();
    const url = music.selectedOptions[0].dataset.url;
    audio.hidden = !url;
    if (url) { audio.src = url; } else { audio.removeAttribute('src'); }
    audio.load();
  });
  const promo = document.querySelector('#promo_code');
  const apply = document.querySelector('#apply-promo');
  const result = document.querySelector('#promo-result');
  const money = value => new Intl.NumberFormat('ru-RU').format(value) + ' ₸';
  const resetPrice = () => {
    document.querySelector('#discount-value').textContent = '0 ₸';
    document.querySelector('#total-value').textContent = money(Number(apply.dataset.price));
    result.textContent = copy.applyHint;
  };
  promo?.addEventListener('input', resetPrice);
  apply?.addEventListener('click', async () => {
    apply.disabled = true;
    result.textContent = copy.checking;
    const code = promo.value;
    try {
      const response = await fetch(apply.dataset.url, { method: 'POST', headers: {
        'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }, body: JSON.stringify({ template_id: apply.dataset.template, promo_code: code }) });
      const data = await response.json();
      if (promo.value !== code) { return; }
      if (!response.ok) { throw new Error(data.errors?.promo_code?.[0] || copy.promoError); }
      document.querySelector('#discount-value').textContent = '−' + money(data.discount);
      document.querySelector('#total-value').textContent = money(data.total);
      result.textContent = code.trim() ? copy.applied : copy.noPromo;
    } catch (error) { resetPrice(); result.textContent = error.message; }
    finally { apply.disabled = false; }
  });
  document.querySelectorAll('form[data-submit-once]').forEach(form => form.addEventListener('submit', () => {
    const button = form.querySelector('button[type="submit"]');
    if (button) { button.disabled = true; button.textContent = copy.saving; }
  }));
});
