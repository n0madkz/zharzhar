(() => {
    'use strict';

    const validPoint = venue => Number.isFinite(venue?.lat) && Number.isFinite(venue?.lng)
        && Math.abs(venue.lat) <= 90 && Math.abs(venue.lng) <= 180;

    const distance = (a, b) => {
        const radians = number => number * Math.PI / 180;
        const value = Math.sin(radians(b.lat - a.lat) / 2) ** 2
            + Math.cos(radians(a.lat)) * Math.cos(radians(b.lat))
            * Math.sin(radians(b.lng - a.lng) / 2) ** 2;
        return 6371 * 2 * Math.asin(Math.sqrt(Math.min(1, value)));
    };

    function planRoute(origin, venues, excludedIds = new Set(), limit = 5) {
        if (!validPoint(origin)) return [];
        const remaining = venues.filter(venue => validPoint(venue)
            && !venue.partner && venue.dealStatus === 'open' && !excludedIds.has(venue.id));
        const stops = [];
        let current = origin;

        while (remaining.length && stops.length < limit) {
            remaining.sort((first, second) => distance(current, first) - distance(current, second)
                || first.name.localeCompare(second.name) || first.id - second.id);
            const next = remaining.shift();
            stops.push({venue: next, distanceKm: distance(current, next)});
            current = next;
        }

        return stops;
    }

    globalThis.BrokerRoute = {distance, planRoute, validPoint};
})();
