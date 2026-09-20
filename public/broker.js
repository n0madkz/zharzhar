(() => {
    'use strict';
    const {venues, csrf} = window.brokerData;
    const $ = id => document.getElementById(id);
    const validPoint = v => Number.isFinite(v.lat) && Number.isFinite(v.lng);
    const text = (tag, value, cls) => { const el = document.createElement(tag); el.textContent = value; if (cls) el.className = cls; return el; };
    const digits = value => value.replace(/\D/g, '');
    let origin = null, page = 1, map, markers, startMarker;
    const distance = (a, b) => {
        const rad = n => n * Math.PI / 180;
        const h = Math.sin(rad(b.lat-a.lat)/2)**2 + Math.cos(rad(a.lat))*Math.cos(rad(b.lat))*Math.sin(rad(b.lng-a.lng)/2)**2;
        return 6371 * 2 * Math.asin(Math.sqrt(Math.min(1,h)));
    };
    [...new Set(venues.map(v => v.district))].sort().forEach(d => { const o = text('option', d); o.value = d; $('district').append(o); });
    if (window.L) {
        map = L.map('map').setView([48, 67], 5);
        if (L.maplibreGL) {
            L.maplibreGL({style: 'https://tiles.openfreemap.org/styles/liberty'}).addTo(map);
        } else {
            $('map-error').hidden = false;
        }
        markers = L.layerGroup().addTo(map);
        const points = venues.filter(validPoint).map(v => [v.lat, v.lng]);
        if (points.length) map.fitBounds(points, {padding:[30,30], maxZoom:14});
        map.on('click', e => setOrigin(e.latlng.lat, e.latlng.lng));
    } else $('map-error').hidden = false;

    function setOrigin(lat, lng) {
        origin = {lat, lng}; page = 1;
        $('location-note').textContent = 'Начальная точка выбрана. Сначала ближайшие залы; расстояния по прямой. Нажмите на карту, чтобы изменить точку.';
        if (map) { if(startMarker) startMarker.remove(); startMarker = L.circleMarker([lat,lng], {radius:9, color:'#111827', fillOpacity:1}).addTo(map).bindTooltip('Начальная точка'); }
        render();
    }
    $('locate').onclick = () => {
        if (!navigator.geolocation) { $('location-note').textContent = 'Геолокация недоступна. Выберите начальную точку на карте.'; return; }
        $('locate').disabled = true;
        navigator.geolocation.getCurrentPosition(position => {
            $('locate').disabled = false;
            setOrigin(position.coords.latitude, position.coords.longitude);
        }, () => {
            $('locate').disabled = false;
            $('location-note').textContent = 'Не удалось определить местоположение. Разрешите геолокацию или выберите точку на карте.';
        }, {enableHighAccuracy:true, timeout:15000, maximumAge:60000});
    };
    function filtered() {
        const q = $('search').value.trim().toLocaleLowerCase(), phone = digits(q);
        let items = venues.filter(v => (!q || [v.name,v.address,v.phone].some(s => (s||'').toLocaleLowerCase().includes(q)) || (phone.length && digits(v.phone||'').includes(phone)))
            && (!$('district').value || v.district === $('district').value)
            && (!$('connection').value || ($('connection').value === 'partner' ? v.partner : $('connection').value === 'completed' ? v.completed : !v.partner)));
        items.sort((a,b) => {
            const da = origin && validPoint(a) ? distance(origin,a) : Infinity;
            const db = origin && validPoint(b) ? distance(origin,b) : Infinity;
            if ($('sort-order').value === 'name' || !origin) return a.name.localeCompare(b.name);
            return ($('sort-order').value === 'farthest' ? db-da : da-db) || a.name.localeCompare(b.name);
        });
        if ($('group-district').checked) {
            // Insertion order keeps the nearest district first and nearest venues first within it.
            const groups = new Map();
            items.forEach(v => { if (!groups.has(v.district)) groups.set(v.district, []); groups.get(v.district).push(v); });
            items = [...groups.values()].flat();
        }
        return items;
    }
    function render() {
        const items = filtered(), pages = Math.max(1, Math.ceil(items.length/10));
        page = Math.min(page,pages);
        const visible = items.slice((page-1)*10,page*10);
        $('count').textContent = `Найдено залов: ${items.length}`;
        $('page-label').textContent = `${page} / ${pages}`;
        $('prev').disabled = page===1; $('next').disabled = page===pages;
        $('venue-list').replaceChildren();
        if (!items.length) $('venue-list').append(text('p', venues.length ? 'По этим условиям залы не найдены.' : 'В этом городе пока нет залов. Нажмите «Обновить из 2GIS».', 'card'));
        let previousDistrict;
        visible.forEach(v => {
            if ($('group-district').checked && previousDistrict !== v.district) $('venue-list').append(text('h2', v.district, 'broker-district'));
            previousDistrict = v.district;
            const card = text('article','', 'broker-venue'); card.id = `venue-${v.id}`;
            card.append(text('h3', (v.partner ? '✓ ' : '') + v.name, v.partner ? 'broker-partner' : ''));
            card.append(text('small', `${v.district} · ${v.address || 'Адрес не указан'}`));
            if (origin && validPoint(v)) card.append(text('p', `${distance(origin,v).toFixed(1)} км от начальной точки`, 'broker-distance'));
            else if (!validPoint(v)) card.append(text('small', 'В данных 2GIS нет координат'));
            if (v.partner) {
                card.append(text('p', `Уже партнёр · ${v.partner.status === 'active' ? 'Активен' : 'Отключён'}`, 'broker-partner'));
                card.append(text('p', `${v.partner.phone || ''} · ${v.partner.email || ''}`));
            } else if (v.phone) card.append(text('p', v.phone));
            const actions = text('div', '', 'actions');
            const link = text('a', 'Открыть в 2GIS', 'button secondary'); link.href = v.url; link.target = '_blank'; link.rel = 'noopener noreferrer'; actions.append(link);
            if (!v.partner) {
                const button = text('button', 'Зарегистрировать', 'button'); button.type = 'button';
                button.onclick = () => {
                    $('registration-form').reset(); $('registration-form').action = `/broker/venues/${v.id}/register`;
                    $('registration-name').textContent = `${v.name} · ${v.address || ''}`;
                    $('registration-phone').value = v.phone || ''; $('registration-email').value = v.email || '';
                    $('registration').showModal();
                }; actions.append(button);
            }
            card.append(actions);
            const label = text('label','','broker-complete'), check = document.createElement('input'); check.type='checkbox'; check.checked=v.completed;
            check.onchange = async () => {
                check.disabled = true;
                try {
                    const response = await fetch(`/broker/venues/${v.id}/complete`, {method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf}, body:JSON.stringify({completed:check.checked})});
                    if (!response.ok) throw new Error();
                    v.completed = (await response.json()).completed; render();
                } catch { check.checked = v.completed; $('location-note').textContent = 'Не удалось сохранить отметку. Проверьте соединение и повторите.'; }
                finally { check.disabled = false; }
            };
            label.append(check, document.createTextNode('Сделка завершена')); card.append(label); $('venue-list').append(card);
        });
        if (markers) {
            markers.clearLayers();
            items.filter(validPoint).forEach((v, index) => {
                const popup = text('div', v.name + (v.partner ? ' · Уже партнёр' : ''));
                const focus = text('button', 'Показать в списке'); focus.type='button';
                focus.onclick=()=>{ page=Math.floor(items.indexOf(v)/10)+1; render(); document.getElementById(`venue-${v.id}`).scrollIntoView({block:'center',behavior:'smooth'}); };
                popup.append(document.createElement('br'),focus);
                L.marker([v.lat,v.lng], {icon:L.divIcon({className:`broker-pin${v.partner?' connected':''}`,html:v.partner?'✓':String(index+1),iconSize:[28,28]})}).addTo(markers).bindPopup(popup);
            });
        }
        updateRoute(items);
    }
    function updateRoute(items) {
        $('route').hidden = true;
        if (!origin) { $('route-note').textContent = 'Выберите начальную точку для маршрута.'; return; }
        if (!$('district').value) { $('route-note').textContent = 'Выберите район для маршрута объезда.'; return; }
        const remaining = items.filter(v => validPoint(v) && !v.completed), stops = [];
        let current = origin;
        // Keep the link within mobile Google Maps' three-waypoint limit.
        while (remaining.length && stops.length < 4) {
            remaining.sort((a,b)=>distance(current,a)-distance(current,b));
            current = remaining.shift(); stops.push(current);
        }
        if (!stops.length) { $('route-note').textContent='Нет незавершённых залов с координатами.'; return; }
        const point = p => `${p.lat},${p.lng}`;
        const params = new URLSearchParams({api:'1',origin:point(origin),destination:point(stops.at(-1)),travelmode:'driving'});
        if (stops.length>1) params.set('waypoints', stops.slice(0,-1).map(point).join('|'));
        $('route').href = `https://www.google.com/maps/dir/?${params}`; $('route').hidden = false;
        $('route-note').textContent = `Ближайший объезд: ${stops.map(v=>v.name).join(' → ')}. До 4 залов; дорожный маршрут рассчитает Google Maps.`;
    }
    ['search','district','connection','group-district','sort-order'].forEach(id => $(id).addEventListener(id==='search'?'input':'change',()=>{page=1;render();}));
    ['split','list','map'].forEach(mode => $(`view-${mode}`).onclick = () => {
        $('broker-workspace').dataset.view = mode;
        ['split','list','map'].forEach(item => $(`view-${item}`).classList.toggle('secondary', item !== mode));
        if (map && mode !== 'list') setTimeout(() => map.invalidateSize(), 0);
    });
    $('prev').onclick=()=>{page--;render();}; $('next').onclick=()=>{page++;render();};
    $('close-dialog').onclick=()=>$('registration').close();
    render();
})();
