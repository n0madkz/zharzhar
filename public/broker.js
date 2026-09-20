(() => {
    'use strict';

    const {venues, csrf} = window.brokerData;
    const $ = id => document.getElementById(id);
    const text = (tag, value, cls) => {
        const element = document.createElement(tag);
        element.textContent = value;
        if (cls) element.className = cls;
        return element;
    };
    const digits = value => String(value || '').replace(/\D/g, '');
    const validPoint = venue => Number.isFinite(venue.lat) && Number.isFinite(venue.lng);
    const filterGroups = Object.fromEntries([...document.querySelectorAll('[data-filters]')].map(group => [group.dataset.filters, group]));
    let origin = null;
    let map;
    let markers;
    let startMarker;
    let venuePage = 1;
    let dealPage = 1;

    const distance = (a, b) => {
        const radians = number => number * Math.PI / 180;
        const value = Math.sin(radians(b.lat - a.lat) / 2) ** 2
            + Math.cos(radians(a.lat)) * Math.cos(radians(b.lat))
            * Math.sin(radians(b.lng - a.lng) / 2) ** 2;
        return 6371 * 2 * Math.asin(Math.sqrt(Math.min(1, value)));
    };

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
        const status = field(scope, 'status')?.value || '';
        const sort = field(scope, 'sort')?.value || 'nearest';
        const items = source.filter(venue => {
            const matchesQuery = !query
                || [venue.name, venue.address, venue.phone].some(value => String(value || '').toLocaleLowerCase().includes(query))
                || (phone.length > 0 && digits(venue.phone).includes(phone));
            return matchesQuery && (!district || venue.district === district) && (!status || venue.dealStatus === status);
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

    function updateRoute(items) {
        $('route').hidden = true;
        if (!origin) {
            $('route-note').textContent = 'Определите местоположение или выберите точку на карте.';
            return;
        }
        const district = field('map', 'district').value;
        if (!district) {
            $('route-note').textContent = 'Выберите район для маршрута объезда.';
            return;
        }
        const remaining = items.filter(venue => validPoint(venue) && venue.dealStatus === 'open' && !venue.partner);
        const stops = [];
        let current = origin;
        while (remaining.length && stops.length < 4) {
            remaining.sort((first, second) => distance(current, first) - distance(current, second));
            current = remaining.shift();
            stops.push(current);
        }
        if (!stops.length) {
            $('route-note').textContent = 'В этом районе нет незавершённых залов с координатами.';
            return;
        }
        const point = value => `${value.lat},${value.lng}`;
        const params = new URLSearchParams({api: '1', origin: point(origin), destination: point(stops.at(-1)), travelmode: 'driving'});
        if (stops.length > 1) params.set('waypoints', stops.slice(0, -1).map(point).join('|'));
        $('route').href = `https://www.google.com/maps/dir/?${params}`;
        $('route').hidden = false;
        $('route-note').textContent = `Маршрут: ${stops.map(venue => venue.name).join(' → ')}`;
    }

    function renderMap() {
        const items = filters('map');
        $('map-count').textContent = `На карте: ${items.length}`;
        if (markers) {
            markers.clearLayers();
            items.filter(validPoint).forEach((venue, index) => {
                const popup = text('div', '');
                popup.append(text('strong', venue.name), document.createElement('br'), text('span', venue.address || 'Адрес не указан'));
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
                }
                L.marker([venue.lat, venue.lng], {
                    icon: L.divIcon({className: `broker-pin${venue.partner ? ' connected' : ''}`, html: venue.partner ? '✓' : String(index + 1), iconSize: [28, 28]}),
                }).addTo(markers).bindPopup(popup);
            });
        }
        updateRoute(items);
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
    $('locate').onclick = () => locate(true);
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
