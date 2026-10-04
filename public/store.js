let storefrontNavigationAbort;
const initStorePage = () => {
  storefrontNavigationAbort?.abort();
  storefrontNavigationAbort = new AbortController();
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
  const catalogFilters = document.querySelector('[data-catalog-filters]');
  const catalogGrid = document.querySelector('[data-catalog-grid]');
  if (catalogFilters && catalogGrid) {
    const filterLinks = Array.from(catalogFilters.querySelectorAll('[data-event-filter]'));
    const cards = Array.from(catalogGrid.querySelectorAll('[data-event-type]'));
    const emptyState = catalogGrid.querySelector('[data-catalog-empty]');
    const pagination = document.querySelector('[data-catalog-pagination]');
    const paginationNumbers = pagination?.querySelector('[data-page-numbers]');
    const previousPage = pagination?.querySelector('[data-page-action="previous"]');
    const nextPage = pagination?.querySelector('[data-page-action="next"]');
    const pageSize = 8;
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const mobileNav = document.querySelector('[data-mobile-store-nav]');
    const categorySheet = document.querySelector('[data-category-sheet]');
    const categoryOpen = mobileNav?.querySelector('[data-category-open]');
    const categoryChoices = Array.from(categorySheet?.querySelectorAll('[data-mobile-event-filter]') || []);
    const mobileTabs = Array.from(mobileNav?.querySelectorAll('[data-mobile-tab]') || []);
    const setMobileTab = name => {
      mobileTabs.forEach(tab => {
        if (tab.dataset.mobileTab === name) tab.setAttribute('aria-current', 'page');
        else tab.removeAttribute('aria-current');
      });
    };


    const applyCatalogFilter = (eventType, requestedPage = 1, updateHistory = false) => {
      const validFilter = filterLinks.some(link => link.dataset.eventFilter === eventType) ? eventType : '';
      const matchingCards = cards.filter(card => !validFilter || !card.dataset.eventType || card.dataset.eventType === validFilter);
      const totalPages = Math.max(1, Math.ceil(matchingCards.length / pageSize));
      const currentPage = Math.min(Math.max(Number(requestedPage) || 1, 1), totalPages);
      const firstVisibleIndex = (currentPage - 1) * pageSize;
      const visibleCards = matchingCards.slice(firstVisibleIndex, firstVisibleIndex + pageSize);

      categoryChoices.forEach(choice => {
        if (choice.dataset.mobileEventFilter === validFilter) choice.setAttribute('aria-current', 'true');
        else choice.removeAttribute('aria-current');
      });

      filterLinks.forEach(link => {
        const active = link.dataset.eventFilter === validFilter;
        link.classList.toggle('active', active);
        if (active) link.setAttribute('aria-current', 'true');
        else link.removeAttribute('aria-current');
      });

      cards.forEach(card => {
        const visible = visibleCards.includes(card);
        card.hidden = !visible;
        if (visible) {
          if (!reduceMotion && typeof card.animate === 'function') {
            card.animate([
              { opacity: 0, transform: 'translateY(10px)' },
              { opacity: 1, transform: 'translateY(0)' },
            ], { duration: 260, easing: 'cubic-bezier(.2,.75,.3,1)' });
          }
        }
      });

      if (emptyState) emptyState.hidden = matchingCards.length !== 0;
      if (pagination) pagination.hidden = matchingCards.length <= pageSize;
      if (previousPage) previousPage.disabled = currentPage === 1;
      if (nextPage) nextPage.disabled = currentPage === totalPages;
      if (paginationNumbers) {
        paginationNumbers.replaceChildren();
        for (let page = 1; page <= totalPages; page += 1) {
          const button = document.createElement('button');
          button.type = 'button';
          button.dataset.catalogPage = String(page);
          button.textContent = String(page);
          button.className = 'catalog-page-number';
          if (page === currentPage) {
            button.classList.add('active');
            button.setAttribute('aria-current', 'page');
          }
          paginationNumbers.append(button);
        }
      }

      if (updateHistory) {
        const url = new URL(window.location.href);
        if (validFilter) url.searchParams.set('event', validFilter);
        else url.searchParams.delete('event');
        if (currentPage > 1) url.searchParams.set('catalog_page', String(currentPage));
        else url.searchParams.delete('catalog_page');
        url.hash = 'designs';
        window.history.pushState({ eventType: validFilter, catalogPage: currentPage }, '', url);
      }
    };

    catalogFilters.addEventListener('click', event => {
      const link = event.target.closest('[data-event-filter]');
      if (!link || !catalogFilters.contains(link)) return;
      event.preventDefault();
      applyCatalogFilter(link.dataset.eventFilter || '', 1, true);
    });

    categoryOpen?.addEventListener('click', () => {
      if (!categorySheet?.open) categorySheet?.showModal();
      setMobileTab('categories');
    });
    categorySheet?.querySelector('[data-category-close]')?.addEventListener('click', () => categorySheet.close());
    categorySheet?.addEventListener('click', event => {
      if (event.target === categorySheet) categorySheet.close();
      const choice = event.target.closest?.('[data-mobile-event-filter]');
      if (!choice || !categorySheet.contains(choice)) return;
      event.preventDefault();
      applyCatalogFilter(choice.dataset.mobileEventFilter || '', 1, true);
      categorySheet.close();
      setMobileTab('categories');
      document.querySelector('#designs')?.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
    });
    categorySheet?.addEventListener('close', () => categoryOpen?.focus({ preventScroll: true }));
    mobileNav?.addEventListener('click', event => {
      const tab = event.target.closest('[data-mobile-tab]');
      if (tab && tab.tagName === 'A') setMobileTab(tab.dataset.mobileTab);
    });
    if (window.location.hash === '#faq') setMobileTab('faq');
    else if (window.location.hash === '#designs') setMobileTab('designs');

    pagination?.addEventListener('click', event => {
      const button = event.target.closest('button');
      if (!button || button.disabled) return;
      const url = new URL(window.location.href);
      const activeFilter = url.searchParams.get('event') || '';
      const currentPage = Number(url.searchParams.get('catalog_page')) || 1;
      const requestedPage = button.dataset.catalogPage
        ? Number(button.dataset.catalogPage)
        : currentPage + (button.dataset.pageAction === 'previous' ? -1 : 1);
      applyCatalogFilter(activeFilter, requestedPage, true);
      document.querySelector('#designs')?.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
    });

    window.addEventListener('hashchange', () => {
      if (window.location.hash === '#faq') setMobileTab('faq');
      else if (window.location.hash === '#designs') setMobileTab('designs');
      else setMobileTab('home');
    }, { signal: storefrontNavigationAbort.signal });

    window.addEventListener('popstate', () => {
      const url = new URL(window.location.href);
      applyCatalogFilter(url.searchParams.get('event') || '', Number(url.searchParams.get('catalog_page')) || 1);
    }, { signal: storefrontNavigationAbort.signal });

    const initialUrl = new URL(window.location.href);
    applyCatalogFilter(initialUrl.searchParams.get('event') || '', Number(initialUrl.searchParams.get('catalog_page')) || 1);
  }
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
  document.querySelectorAll('[data-photo-input]').forEach(input => {
    const preview = document.querySelector('[data-photo-preview]');
    let previewUrls = [];
    input.addEventListener('change', () => {
      previewUrls.forEach(url => URL.revokeObjectURL(url));
      previewUrls = [];
      if (preview) preview.replaceChildren();
      const files = Array.from(input.files || []);
      const photoLabels = (input.dataset.photoLabels || '').split('|');
      input.setCustomValidity(files.length > 3 ? input.dataset.maxMessage : '');
      files.slice(0, 3).forEach((file, index) => {
        const url = URL.createObjectURL(file);
        previewUrls.push(url);
        const figure = document.createElement('figure');
        const image = document.createElement('img');
        image.src = url;
        image.alt = photoLabels[index] || `${index + 1}`;
        figure.append(image);
        if (photoLabels[index]) {
          const caption = document.createElement('figcaption');
          caption.textContent = photoLabels[index];
          figure.append(caption);
        }
        preview?.append(figure);
      });
    });
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
};

document.addEventListener('DOMContentLoaded', initStorePage);

let languageRequestPending = false;
const setLanguageThumb = locale => {
  const control = document.querySelector('[data-language-switch]');
  if (!control) return;
  control.dataset.locale = locale;
  control.querySelectorAll('[data-language-button]').forEach(button => {
    const active = button.dataset.languageButton === locale;
    button.classList.toggle('active', active);
    button.setAttribute('aria-pressed', String(active));
  });
};

const rememberFields = () => Array.from(document.querySelectorAll('#main input, #main select, #main textarea')).map(field => ({
  key: field.name || field.id,
  type: field.type,
  value: field.value,
  checked: field.checked,
  files: field.type === 'file' ? field.files : null,
}));
const restoreFields = saved => {
  const fields = Array.from(document.querySelectorAll('#main input, #main select, #main textarea'));
  fields.forEach((field, index) => {
    const previous = saved[index];
    if (!previous || previous.key !== (field.name || field.id) || previous.type !== field.type) return;
    if (field.type === 'file') {
      if (previous.files?.length) {
        try { field.files = previous.files; field.dispatchEvent(new Event('change', { bubbles: true })); } catch { /* Browser disallows restoring files. */ }
      }
    } else if (field.type === 'checkbox' || field.type === 'radio') field.checked = previous.checked;
    else if (field.type !== 'hidden') field.value = previous.value;
  });
};

document.addEventListener('submit', async event => {
  const form = event.target.closest?.('form[data-language-form]');
  if (!form) return;
  event.preventDefault();
  if (languageRequestPending) return;
  const requestedLocale = form.dataset.languageLocale;
  const previousLocale = document.documentElement.lang;
  if (requestedLocale === previousLocale) return;

  languageRequestPending = true;
  setLanguageThumb(requestedLocale);
  const control = document.querySelector('.language-control');
  control?.setAttribute('aria-busy', 'true');
  const sliderAnimation = new Promise(resolve => setTimeout(resolve, 220));
  try {
    const response = await fetch(form.action, {
      method: 'POST', body: new FormData(form), credentials: 'same-origin',
      headers: { 'Accept': 'text/html' }, referrer: window.location.href,
    });
    if (!response.ok) throw new Error('Language update failed');
    const nextPage = new DOMParser().parseFromString(await response.text(), 'text/html');
    if (nextPage.documentElement.lang !== requestedLocale || !nextPage.querySelector('#main') || !nextPage.querySelector('.store-header')) {
      throw new Error('Unexpected language response');
    }
    await sliderAnimation;
    const savedFields = rememberFields();
    const scrollPosition = window.scrollY;
    const updatePage = () => {
      document.title = nextPage.title;
      document.documentElement.lang = requestedLocale;
      document.body.className = nextPage.body.className;
      const description = nextPage.querySelector('meta[name="description"]');
      if (description) document.querySelector('meta[name="description"]')?.setAttribute('content', description.content);
      document.querySelector('.skip-link')?.replaceWith(nextPage.querySelector('.skip-link'));
      document.querySelector('.store-header')?.replaceWith(nextPage.querySelector('.store-header'));
      document.querySelector('#main')?.replaceWith(nextPage.querySelector('#main'));
      const nextFooter = nextPage.querySelector('.store-footer');
      if (nextFooter) document.querySelector('.store-footer')?.replaceWith(nextFooter);
      initStorePage();
      restoreFields(savedFields);
      window.scrollTo(0, scrollPosition);
      document.querySelector('[data-language-button="' + requestedLocale + '"]')?.focus({ preventScroll: true });
    };
    if (document.startViewTransition && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      document.startViewTransition(updatePage);
    } else updatePage();
  } catch {
    setLanguageThumb(previousLocale);
    const status = document.querySelector('[data-language-status]');
    if (status) status.textContent = previousLocale === 'kk' ? 'Тілді ауыстыру мүмкін болмады. Қайталап көріңіз.' : 'Не удалось сменить язык. Попробуйте ещё раз.';
  } finally {
    languageRequestPending = false;
    document.querySelector('.language-control')?.removeAttribute('aria-busy');
  }
});
