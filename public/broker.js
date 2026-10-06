(() => {
    'use strict';

    const {venues, city, csrf} = window.brokerData;
    const {distance, planRoute, validPoint} = window.BrokerRoute;
    const $ = id => document.getElementById(id);
    const text = (tag, value, cls) => {
        const element = document.createElement(tag);
        element.textContent = value;
        if (cls) element.className = cls;
        return element;
    };
    const digits = value => String(value || '').replace(/\D/g, '');
    const filterGroups = Object.fromEntries([...document.querySelectorAll('[data-filters]')].map(group => [group.dataset.filters, group]));
    let origin = null;
    let map;
    let markers;
    let startMarker;
    let routeLine;
    let routeRequest;
    let routeTimer;
    let routeGeneration = 0;
    let venuePage = 1;
    let dealPage = 1;
    const routeStorageKey = `broker-route-excluded:${city}`;
    let excludedRouteIds = new Set();
    try {
        const saved = JSON.parse(localStorage.getItem(routeStorageKey));
        if (Array.isArray(saved)) excludedRouteIds = new Set(saved.filter(Number.isInteger));
    } catch {}

    function field(scope, name) {
        return filterGroups[scope].querySelector(`[data-field="${name}"]`);
    }

    const districts = [...new Set(venues.map(venue => venue.district))].sort();
    document.querySelectorAll('[data-field="district"]').forEach(select => {
        districts.forEach(district => {
            const option = text('option', district);
            option.value = district;
            select.append(option);
        });
    });

    function filters(scope, source = venues) {
        const query = field(scope, 'search').value.trim().toLocaleLowerCase();
        const phone = digits(query);
        const district = field(scope, 'district')?.value || '';
        const registration = field(scope, 'registration')?.value || '';
        const status = field(scope, 'status')?.value || '';
        const sort = field(scope, 'sort')?.value || 'nearest';
        const items = source.filter(venue => {
            const matchesQuery = !query
                || [venue.name, venue.address, venue.phone].some(value => String(value || '').toLocaleLowerCase().includes(query))
                || (phone.length > 0 && digits(venue.phone).includes(phone));
            return matchesQuery && (!district || venue.district === district)
                && (!registration || Boolean(venue.partner) === (registration === 'yes'))
                && (!status || venue.dealStatus === status);
        });
        items.sort((first, second) => {
            const firstDistance = origin && validPoint(first) ? distance(origin, first) : Infinity;
            const secondDistance = origin && validPoint(second) ? distance(origin, second) : Infinity;
            if (sort === 'name' || !origin) return first.name.localeCompare(second.name);
            return (sort === 'farthest' ? secondDistance - firstDistance : firstDistance - secondDistance)
                || first.name.localeCompare(second.name);
        });
        return items;
    }

    function setLocationNote(message) {
        document.querySelectorAll('[data-location-note]').forEach(note => {
            note.textContent = message;
        });
    }

    function setOrigin(lat, lng) {
        origin = {lat, lng};
        venuePage = 1;
        dealPage = 1;
        try {
            localStorage.setItem('broker-origin', JSON.stringify(origin));
        } catch {}
        setLocationNote('Сначала показаны ближайшие залы. Нажмите на карту, чтобы изменить начальную точку.');
        if (map) {
            if (startMarker) startMarker.remove();
            startMarker = L.circleMarker([lat, lng], {radius: 9, color: '#0f172a', fillOpacity: 1})
                .addTo(map)
                .bindTooltip('Вы находитесь здесь');
        }
        renderAll();
    }

    function locate(interactive = true) {
        if (!navigator.geolocation) {
            if (interactive) setLocationNote('Геолокация недоступна. Выберите начальную точку на карте.');
            return;
        }
        $('locate').disabled = true;
        navigator.geolocation.getCurrentPosition(position => {
            $('locate').disabled = false;
            setOrigin(position.coords.latitude, position.coords.longitude);
        }, () => {
            $('locate').disabled = false;
            if (interactive) setLocationNote('Не удалось определить местоположение. Разрешите геолокацию или выберите точку на карте.');
        }, {enableHighAccuracy: true, timeout: 15000, maximumAge: 60000});
    }

    function switchScreen(name) {
        document.querySelectorAll('[data-screen]').forEach(screen => {
            const active = screen.dataset.screen === name;
            screen.hidden = !active;
            screen.classList.toggle('is-active', active);
        });
        document.querySelectorAll('[data-tab]').forEach(button => button.classList.toggle('is-active', button.dataset.tab === name));
        history.replaceState(null, '', `#${name}`);
        if (name === 'map' && map) setTimeout(() => map.invalidateSize(), 0);
        window.scrollTo({top: 0, behavior: 'smooth'});
    }

    function updateHallFields() {
        const count = Math.max(1, Math.min(20, Number($('halls-count').value) || 1));
        $('halls-count').value = count;
        const previous = [...$('hall-fields').querySelectorAll('input')].map(input => input.value);
        $('hall-fields').replaceChildren();
        for (let index = 0; index < count; index += 1) {
            const label = text('label', `Зал ${index + 1} — количество мест`);
            const input = document.createElement('input');
            input.type = 'number';
            input.name = `halls[${index}][max_seats]`;
            input.min = '1';
            input.max = '10000';
            input.required = true;
            input.value = previous[index] || '';
            label.append(input);
            $('hall-fields').append(label);
        }
    }

    function openRegistration(venue) {
        $('registration-form').reset();
        $('registration-form').action = `/broker/venues/${venue.id}/register`;
        $('registration-name').textContent = `${venue.name} · ${venue.address || ''}`;
        $('registration-phone').value = venue.phone || '';
        $('registration-email').value = venue.email || '';
        $('halls-count').value = 1;
        updateHallFields();
        $('registration').showModal();
    }

    async function saveStatus(venue, select) {
        select.disabled = true;
        try {
            const response = await fetch(`/broker/venues/${venue.id}/complete`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf},
                body: JSON.stringify({status: select.value}),
            });
            if (!response.ok) throw new Error();
            venue.dealStatus = (await response.json()).status;
            renderAll();
        } catch {
            select.value = venue.dealStatus;
            setLocationNote('Не удалось сохранить состояние сделки. Проверьте соединение и повторите.');
        } finally {
            select.disabled = false;
        }
    }

    function venueCard(venue, completed = false) {
        const card = document.createElement('details');
        card.className = 'broker-venue';
        card.id = `venue-${venue.id}`;
        const summary = document.createElement('summary');
        const heading = text('div', '');
        heading.append(text('h3', venue.name, venue.partner ? 'broker-partner' : ''));
        heading.append(text('small', `${venue.district} · ${venue.address || 'Адрес не указан'}`));
        if (origin && validPoint(venue)) heading.append(text('strong', `${distance(origin, venue).toFixed(1)} км`, 'broker-distance'));
        summary.append(heading, text('span', completed ? 'Подключён' : 'Открыть', 'broker-card-state'));
        card.append(summary);

        const body = text('div', '', 'broker-venue-body');
        if (venue.partner) {
            body.append(text('p', `Партнёр: ${venue.partner.name} · ${venue.partner.status === 'active' ? 'активен' : 'отключён'}`, 'broker-partner'));
            body.append(text('p', [venue.partner.phone, venue.partner.email].filter(Boolean).join(' · ')));
        } else if (venue.phone) {
            body.append(text('p', venue.phone));
        }
        const actions = text('div', '', 'actions');
        const link = text('a', 'Открыть в 2GIS', 'button secondary');
        link.href = venue.url;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        actions.append(link);
        if (!venue.partner) {
            const register = text('button', 'Зарегистрировать партнёра', 'button');
            register.type = 'button';
            register.onclick = () => openRegistration(venue);
            actions.append(register);
            if (venue.dealStatus === 'open' && validPoint(venue)) actions.append(routeToggle(venue));
        }
        body.append(actions);
        if (!completed) {
            const statusLabel = text('label', 'Состояние сделки', 'broker-complete');
            const status = document.createElement('select');
            [['open', 'В работе'], ['closed', 'Сделка закрыта'], ['failed', 'Сделка не состоялась']].forEach(([value, name]) => {
                const option = text('option', name);
                option.value = value;
                option.selected = venue.dealStatus === value;
                status.append(option);
            });
            status.onchange = () => saveStatus(venue, status);
            statusLabel.append(status);
            body.append(statusLabel);
        }
        card.append(body);
        return card;
    }

    function renderVenues() {
        let items = filters('venues', venues.filter(venue => !venue.partner));
        if ($('group-district').checked) {
            const groups = new Map();
            items.forEach(venue => {
                if (!groups.has(venue.district)) groups.set(venue.district, []);
                groups.get(venue.district).push(venue);
            });
            items = [...groups.values()].flat();
        }
        const pages = Math.max(1, Math.ceil(items.length / 10));
        venuePage = Math.min(venuePage, pages);
        const visible = items.slice((venuePage - 1) * 10, venuePage * 10);
        $('venue-count').textContent = `Найдено ресторанов: ${items.length}`;
        $('page-label').textContent = `${venuePage} / ${pages}`;
        $('prev').disabled = venuePage === 1;
        $('next').disabled = venuePage === pages;
        $('venue-list').replaceChildren();
        if (!visible.length) $('venue-list').append(text('p', venues.length ? 'По этим условиям рестораны не найдены.' : 'Список пуст. Обновите город из 2GIS в настройках.', 'card'));
        let previousDistrict;
        visible.forEach(venue => {
            if ($('group-district').checked && previousDistrict !== venue.district) $('venue-list').append(text('h2', venue.district, 'broker-district'));
            previousDistrict = venue.district;
            $('venue-list').append(venueCard(venue));
        });
    }

    function renderDeals() {
        const items = filters('deals', venues.filter(venue => venue.partner));
        const pages = Math.max(1, Math.ceil(items.length / 10));
        dealPage = Math.min(dealPage, pages);
        const visible = items.slice((dealPage - 1) * 10, dealPage * 10);
        $('deal-count').textContent = `Подключено ресторанов: ${items.length}`;
        $('deal-page-label').textContent = `${dealPage} / ${pages}`;
        $('deal-prev').disabled = dealPage === 1;
        $('deal-next').disabled = dealPage === pages;
        $('deal-list').replaceChildren();
        if (!visible.length) $('deal-list').append(text('p', 'Подключённые рестораны не найдены.', 'card'));
        visible.forEach(venue => $('deal-list').append(venueCard(venue, true)));
    }

    function saveRouteExclusions() {
        try { localStorage.setItem(routeStorageKey, JSON.stringify([...excludedRouteIds])); } catch {}
    }

    function toggleRouteVenue(venue) {
        if (excludedRouteIds.has(venue.id)) excludedRouteIds.delete(venue.id);
        else excludedRouteIds.add(venue.id);
        saveRouteExclusions();
        document.querySelectorAll(`[data-route-toggle="${venue.id}"]`).forEach(button => {
            button.textContent = excludedRouteIds.has(venue.id) ? 'Вернуть в маршрут' : 'Убрать из маршрута';
        });
        renderMap();
    }

    function refreshRouteButtons() {
        document.querySelectorAll('[data-route-toggle]').forEach(button => {
            button.textContent = excludedRouteIds.has(Number(button.dataset.routeToggle)) ? 'Вернуть в маршрут' : 'Убрать из маршрута';
        });
    }

    function routeToggle(venue) {
        const button = text('button', excludedRouteIds.has(venue.id) ? 'Вернуть в маршрут' : 'Убрать из маршрута', 'button secondary broker-route-toggle');
        button.type = 'button';
        button.dataset.routeToggle = venue.id;
        button.onclick = () => toggleRouteVenue(venue);
        return button;
    }

    function directionsUrl(from, to, waypoints = []) {
        const point = value => `${value.lat},${value.lng}`;
        const params = new URLSearchParams({api: '1', origin: point(from), destination: point(to), travelmode: 'driving'});
        if (waypoints.length) params.set('waypoints', waypoints.map(point).join('|'));
        return `https://www.google.com/maps/dir/?${params}`;
    }

    function updateRoute(items, roadResult = null, routeError = '') {
        $('route').hidden = true;
        $('route-attribution').hidden = !roadResult;
        $('route-stops').replaceChildren();
        $('route-reset').hidden = excludedRouteIds.size === 0;
        if (routeLine && map) {
            map.removeLayer(routeLine);
            routeLine = null;
        }
        if (!origin) {
            $('route-note').textContent = 'Определите местоположение или выберите точку на карте.';
            return [];
        }
        const fallbackStops = planRoute(origin, items, excludedRouteIds);
        const byId = new Map(items.map(venue => [venue.id, venue]));
        const stops = roadResult ? roadResult.stops.map(stop => ({venue: byId.get(stop.id), distanceKm: stop.distanceKm})).filter(stop => stop.venue) : fallbackStops;
        if (!stops.length) {
            $('route-note').textContent = 'По выбранным фильтрам нет незарегистрированных ресторанов с открытой сделкой и координатами.';
            return [];
        }
        stops.forEach(({venue, distanceKm}, index) => {
            const item = text('li', '', 'broker-route-stop');
            const number = text('span', String(index + 1), 'broker-route-number');
            const details = text('div', '', 'broker-route-details');
            details.append(text('strong', venue.name), text('small', `${venue.address || venue.district} · ${distanceKm.toFixed(1)} км ${roadResult ? 'по дорогам' : 'по прямой'}`));
            const navigate = text('a', 'Ехать ↗', 'broker-route-navigate');
            navigate.href = directionsUrl(index ? stops[index - 1].venue : origin, venue);
            navigate.target = '_blank';
            navigate.rel = 'noopener noreferrer';
            navigate.setAttribute('aria-label', `Построить маршрут к ресторану ${venue.name}`);
            const remove = text('button', 'Убрать', 'broker-route-remove');
            remove.type = 'button';
            remove.setAttribute('aria-label', `Убрать ${venue.name} из маршрута`);
            remove.onclick = () => toggleRouteVenue(venue);
            item.append(number, details, navigate, remove);
            $('route-stops').append(item);
        });
        const points = stops.map(stop => stop.venue);
        $('route').href = directionsUrl(origin, points.at(-1), points.slice(0, -1));
        $('route').hidden = false;
        $('route-note').textContent = roadResult
            ? `${stops.length} из 5 остановок · порядок и линия построены по дорогам${roadResult.limited ? ' среди 19 ближайших ресторанов' : ''}. На телефоне открывайте остановки по одной кнопкой «Ехать».`
            : routeError || 'Рассчитываем маршрут по дорогам…';
        if (map && roadResult) routeLine = L.polyline(roadResult.geometry.map(([lng, lat]) => [lat, lng]), {
            color: '#2563eb', weight: 5, opacity: .9, interactive: false,
        }).addTo(map);
        return stops;
    }

    function renderMap(roadResult = null, routeError = '') {
        clearTimeout(routeTimer);
        if (routeRequest) routeRequest.abort();
        const generation = ++routeGeneration;
        const items = filters('map');
        $('map-count').textContent = `На карте: ${items.length}`;
        const routeStops = updateRoute(items, roadResult, routeError);
        const routePositions = new Map(routeStops.map(({venue}, index) => [venue.id, index + 1]));
        if (markers) {
            markers.clearLayers();
            items.filter(validPoint).forEach(venue => {
                const popup = text('div', '');
                popup.append(text('strong', venue.name), document.createElement('br'), text('span', venue.address || 'Адрес не указан'));
                popup.append(document.createElement('br'), text('small', venue.partner ? 'Зарегистрирован' : 'Не зарегистрирован'));
                const twoGis = text('a', 'Открыть в 2GIS ↗');
                twoGis.href = venue.url;
                twoGis.target = '_blank';
                twoGis.rel = 'noopener noreferrer';
                popup.append(document.createElement('br'), twoGis);
                if (!venue.partner) {
                    const open = text('button', 'Открыть карточку');
                    open.type = 'button';
                    open.onclick = () => {
                        field('venues', 'search').value = venue.name;
                        venuePage = 1;
                        renderVenues();
                        switchScreen('venues');
                        setTimeout(() => document.getElementById(`venue-${venue.id}`)?.scrollIntoView({block: 'center', behavior: 'smooth'}), 50);
                    };
                    popup.append(document.createElement('br'), open);
                    if (venue.dealStatus === 'open') popup.append(document.createElement('br'), routeToggle(venue));
                }
                L.marker([venue.lat, venue.lng], {
                    icon: L.divIcon({className: `broker-pin${venue.partner ? ' connected' : ''}${routePositions.has(venue.id) ? ' route-stop' : ''}`, html: routePositions.get(venue.id) || (venue.partner ? '✓' : '·'), iconSize: [28, 28]}),
                }).addTo(markers).bindPopup(popup);
            });
        }
        if (origin && !roadResult && !routeError) {
            const eligible = items.filter(venue => validPoint(venue) && !venue.partner
                && venue.dealStatus === 'open' && !excludedRouteIds.has(venue.id));
            const candidates = eligible.sort((a, b) => distance(origin, a) - distance(origin, b)).slice(0, 19);
            if (candidates.length) routeTimer = setTimeout(async () => {
                routeRequest = new AbortController();
                try {
                    const response = await fetch('/broker/route', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf},
                        body: JSON.stringify({city, origin, ids: candidates.map(venue => venue.id)}),
                        signal: routeRequest.signal,
                    });
                    const result = await response.json();
                    if (generation !== routeGeneration) return;
                    if (!response.ok) throw new Error(result.message || 'Не удалось построить маршрут по дорогам.');
                    result.limited = eligible.length > 19;
                    renderMap(result);
                } catch (error) {
                    if (generation !== routeGeneration || error.name === 'AbortError') return;
                    renderMap(null, error.message || 'Не удалось построить маршрут по дорогам.');
                }
            }, 250);
        }
    }

    function renderAll() {
        renderMap();
        renderVenues();
        renderDeals();
    }

    if (window.L) {
        map = L.map('map').setView([48, 67], 5);
        if (L.maplibreGL) L.maplibreGL({style: 'https://tiles.openfreemap.org/styles/liberty'}).addTo(map);
        else $('map-error').hidden = false;
        markers = L.layerGroup().addTo(map);
        const points = venues.filter(validPoint).map(venue => [venue.lat, venue.lng]);
        if (points.length) map.fitBounds(points, {padding: [30, 30], maxZoom: 14});
        map.on('click', event => setOrigin(event.latlng.lat, event.latlng.lng));
    } else {
        $('map-error').hidden = false;
    }

    Object.entries(filterGroups).forEach(([scope, group]) => {
        group.querySelectorAll('input, select').forEach(input => input.addEventListener(input.type === 'search' ? 'input' : 'change', () => {
            if (scope === 'venues') venuePage = 1;
            if (scope === 'deals') dealPage = 1;
            renderAll();
        }));
    });
    document.querySelectorAll('[data-tab]').forEach(button => button.onclick = () => switchScreen(button.dataset.tab));
    document.querySelectorAll('[data-parser-form]').forEach(form => form.addEventListener('submit', () => {
        const button = form.querySelector('button');
        button.disabled = true;
        button.textContent = 'Загрузка из 2GIS…';
    }));
    $('locate').onclick = () => locate(true);
    $('route-reset').onclick = () => {
        excludedRouteIds.clear();
        saveRouteExclusions();
        refreshRouteButtons();
        renderMap();
    };
    $('group-district').onchange = () => {
        venuePage = 1;
        renderVenues();
    };
    $('prev').onclick = () => {
        venuePage -= 1;
        renderVenues();
        window.scrollTo({top: 0, behavior: 'smooth'});
    };
    $('next').onclick = () => {
        venuePage += 1;
        renderVenues();
        window.scrollTo({top: 0, behavior: 'smooth'});
    };
    $('deal-prev').onclick = () => {
        dealPage -= 1;
        renderDeals();
        window.scrollTo({top: 0, behavior: 'smooth'});
    };
    $('deal-next').onclick = () => {
        dealPage += 1;
        renderDeals();
        window.scrollTo({top: 0, behavior: 'smooth'});
    };
    $('halls-count').oninput = updateHallFields;
    $('close-dialog').onclick = () => $('registration').close();
    updateHallFields();
    renderAll();

    try {
        const saved = JSON.parse(localStorage.getItem('broker-origin'));
        if (saved && Number.isFinite(saved.lat) && Number.isFinite(saved.lng)) setOrigin(saved.lat, saved.lng);
    } catch {}
    locate(false);
    const initialScreen = ['map', 'venues', 'deals', 'settings'].includes(location.hash.slice(1)) ? location.hash.slice(1) : 'map';
    switchScreen(initialScreen);
})();
