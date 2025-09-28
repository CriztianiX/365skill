const io = require('socket.io-client');
const Redis = require('ioredis');

describe('WebSocket Integration', () => {
    it('should receive event from Redis pub', (done) => {
        const socket = io('http://event-dispatcher:3000');
        const redis = new Redis({
            port: 6379,
            host: "redis",
        });

        socket.on('reservation-update', (data) => {
            expect(data.event).toBe('reservation.updated');
            socket.disconnect();
            done();
        });

        redis.publish('reservation-events', JSON.stringify({
            event: 'reservation.updated',
            data: { id: 'abc123', status: 'CONFIRMED' }
        }));
    });
});
