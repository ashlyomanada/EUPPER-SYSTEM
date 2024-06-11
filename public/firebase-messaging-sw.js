importScripts(
  "https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js"
);
importScripts(
  "https://www.gstatic.com/firebasejs/9.6.1/firebase-messaging-compat.js"
);

// Initialize the Firebase app in the service worker by passing in the messagingSenderId.
firebase.initializeApp({
  apiKey: "AIzaSyDneGCpRV3D1Ry5wVZOWFoMbWEE6-olmuk",
  authDomain: "fir-60c64.firebaseapp.com",
  projectId: "fir-60c64",
  storageBucket: "fir-60c64.appspot.com",
  messagingSenderId: "930998703827",
  appId: "1:930998703827:web:eae97595174b5757f9a626",
  measurementId: "G-F5FV8YV434",
});

// Retrieve an instance of Firebase Messaging so that it can handle background messages.
const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
  console.log("Received background message ", payload);
  const notificationTitle = payload.notification.title;
  const notificationOptions = {
    body: payload.notification.body,
    icon: payload.notification.icon,
  };

  self.registration.showNotification(notificationTitle, notificationOptions);
});
