
importScripts('https://www.gstatic.com/firebasejs/8.1.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.1.1/firebase-messaging.js');

let firebaseConfig = {
    apiKey: "AIzaSyA8PpO33196vFy5Fz62VssSFmcbVOgP6YU",
    authDomain: "vtiger-10.firebaseapp.com",
    databaseURL: "https://vtiger-10.firebaseio.com",
    projectId: "vtiger-10",
    storageBucket: "vtiger-10.appspot.com",
    messagingSenderId: "783416325030",
    appId: "1:783416325030:web:fec5c51acae215b288d567",
    measurementId: "G-9YN6Z4Q7LD"
};



firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

messaging.setBackgroundMessageHandler(function (payload) {
    const notification = JSON.parse(payload);
    const notificationOption = {
        body: notification.body,
        icon: notification.icon
    };
    return self.registration.showNotification(payload.notification.title, notificationOption);
});
