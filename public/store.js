document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-copy]').forEach(button => button.addEventListener('click', async () => {
    try { await navigator.clipboard.writeText(button.dataset.copy); button.textContent = 'Скопировано'; }
    catch { button.textContent = 'Выделите и скопируйте текст'; }
  }));
  const restaurant = document.querySelector('#restaurant_id');
  restaurant?.addEventListener('change', () => {
    const option = restaurant.selectedOptions[0];
    if (option.dataset.name) {
      document.querySelector('#venue_name').value = option.dataset.name;
      document.querySelector('#venue_address').value = option.dataset.address;
    }
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
    result.textContent = 'Нажмите «Применить», чтобы проверить скидку.';
  };
  promo?.addEventListener('input', resetPrice);
  apply?.addEventListener('click', async () => {
    apply.disabled = true;
    result.textContent = 'Проверяем промокод…';
    const code = promo.value;
    try {
      const response = await fetch(apply.dataset.url, { method: 'POST', headers: {
        'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }, body: JSON.stringify({ template_id: apply.dataset.template, promo_code: code }) });
      const data = await response.json();
      if (promo.value !== code) { return; }
      if (!response.ok) { throw new Error(data.errors?.promo_code?.[0] || 'Не удалось проверить промокод. Попробуйте ещё раз.'); }
      document.querySelector('#discount-value').textContent = '−' + money(data.discount);
      document.querySelector('#total-value').textContent = money(data.total);
      result.textContent = code.trim() ? 'Промокод применён.' : 'Цена без промокода.';
    } catch (error) { resetPrice(); result.textContent = error.message; }
    finally { apply.disabled = false; }
  });
  document.querySelectorAll('form[data-submit-once]').forEach(form => form.addEventListener('submit', () => {
    const button = form.querySelector('button[type="submit"]');
    if (button) { button.disabled = true; button.textContent = 'Сохраняем…'; }
  }));
});
