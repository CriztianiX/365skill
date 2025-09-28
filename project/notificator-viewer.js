const io = require('socket.io-client');
const socket = io('http://notificator-dispatcher:3000');

socket.on('reservation-update', (data) => {
    console.log('Received update:', data);
});

console.log("Event listener ready!");
