const express = require('express');
const cors = require('cors');
const http = require('http');
const socketIo = require('socket.io');
const Redis = require('ioredis');

const app = express();
app.use(cors());
const server = http.createServer(app);
const io = socketIo(server);

const REDIS_PORT = process.env.REDIS_PORT || 26379;
const REDIS_HOST = process.env.REDIS_HOST || '127.0.0.1'

const redis = new Redis({
    port: REDIS_PORT,
    host: REDIS_HOST,
});

redis.subscribe('reservation-events', (err, count) => {
    if (err) console.error(err);
});

redis.on('message', (channel, message) => {
    if (channel === 'reservation-events') {
        io.emit('reservation-update', JSON.parse(message));
    }
});

server.listen(3000, () => {
    console.log('Node.js service running on port 3000');
});
