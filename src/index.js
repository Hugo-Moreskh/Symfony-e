const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const app = express();
const server = http.createServer(app);

const io = new Server(server, {
  cors: {
    origin: ['http://localhost:8000', 'http://127.0.0.1:8000', 'http://localhost:3000'],
    methods: ['GET', 'POST']
  }
});

// On garde une map userId -> socket.id pour les besoins d'inscription
const users = {};

io.on('connection', socket => {
  console.log('Un utilisateur est connecté :', socket.id);

  // Enregistre le userId côté serveur (optionnel, utile si besoin direct)
  socket.on('register', (userId) => {
    users[userId] = socket.id;
    console.log(`Utilisateur ${userId} enregistré avec socket id ${socket.id}`);
  });

  // Rejoindre une room (idRoom reçu côté client)
  socket.on('join_room', (idRoom) => {
    socket.join(idRoom);
    console.log(`Socket ${socket.id} a rejoint la room ${idRoom}`);
  });

  // Recevoir un message et l'émettre à la room concernée
  socket.on('send_message', ({ idRoom, from, content }) => {
    console.log(`Message reçu dans la room ${idRoom} de ${from}: ${content}`);
    // Émettre le message à tous les sockets dans la room (y compris émetteur)
    io.to(idRoom).emit('receive_message', { from, content });
  });

  socket.on('disconnect', () => {
    console.log(`Utilisateur déconnecté : ${socket.id}`);
    // Optionnel: nettoyer users en supprimant userId pointant sur ce socket.id
    for (const [userId, sid] of Object.entries(users)) {
      if (sid === socket.id) {
        delete users[userId];
        break;
      }
    }
  });
});

server.listen(3000, () => {
  console.log('Serveur Socket.IO démarré sur le port 3000');
});
