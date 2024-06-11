// src/firebase.js
import { initializeApp } from "firebase/app";
import { getMessaging, getToken, onMessage } from "firebase/messaging";

const firebaseConfig = {
  apiKey: "AIzaSyDneGCpRV3D1Ry5wVZOWFoMbWEE6-olmuk",
  authDomain: "fir-60c64.firebaseapp.com",
  projectId: "fir-60c64",
  storageBucket: "fir-60c64.appspot.com",
  messagingSenderId: "930998703827",
  appId: "1:930998703827:web:eae97595174b5757f9a626",
  measurementId: "G-F5FV8YV434",
};
// Initialize Firebase
const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

export { messaging, getToken, onMessage };
