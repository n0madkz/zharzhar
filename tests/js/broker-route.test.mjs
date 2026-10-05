import assert from 'node:assert/strict';
import test from 'node:test';
import '../../public/broker-route.js';

const {planRoute} = globalThis.BrokerRoute;
const venue = (id, lng, extra = {}) => ({id, name: `Ресторан ${id}`, lat: 0, lng, dealStatus: 'open', partner: null, ...extra});

test('each next stop is chosen from the previous restaurant, up to five stops', () => {
    const places = [venue(1, .01), venue(2, -.015), venue(3, .032), venue(4, .05), venue(5, .065), venue(6, .08)];
    const route = planRoute({lat: 0, lng: 0}, places);

    assert.deepEqual(route.map(stop => stop.venue.id), [1, 3, 4, 5, 6]);
    assert.ok(route.every(stop => stop.distanceKm > 0));
});

test('removed, registered, closed and unlocated restaurants are skipped', () => {
    const places = [
        venue(1, .01),
        venue(2, .02, {partner: {name: 'Партнёр'}}),
        venue(3, .03, {dealStatus: 'closed'}),
        venue(4, .04, {lat: null}),
        venue(5, .05),
    ];

    assert.deepEqual(planRoute({lat: 0, lng: 0}, places, new Set([1])).map(stop => stop.venue.id), [5]);
    assert.deepEqual(planRoute(null, places), []);
});
