// ──────────────────────────────────────────────────────────
//  PharmacyLink — Firebase Configuration
//  Replace all values with your project's config from:
//  Firebase Console → Project Settings → General → Your Apps
//  databaseURL: Firebase Console → Realtime Database → copy URL
// ──────────────────────────────────────────────────────────
const firebaseConfig = {
  apiKey:            "YOUR_API_KEY",
  authDomain:        "YOUR_AUTH_DOMAIN",
  projectId:         "YOUR_PROJECT_ID",
  storageBucket:     "YOUR_STORAGE_BUCKET",
  messagingSenderId: "YOUR_SENDER_ID",
  appId:             "YOUR_APP_ID",
  databaseURL:       "YOUR_DATABASE_URL"
};

// ── Demo Mode ──────────────────────────────────────────────
// When config is still placeholder, the entire app runs in
// demo mode: auth is skipped, Firestore/RTDB calls are
// bypassed, all data comes from mock-data.js
// ──────────────────────────────────────────────────────────
const DEMO_MODE = firebaseConfig.apiKey === 'YOUR_API_KEY';

try {
  firebase.initializeApp(firebaseConfig);
} catch (e) {
  if (e.code !== 'app/duplicate-app') console.warn('Firebase init:', e.message);
}

const auth     = firebase.auth();
const db       = firebase.firestore();
const database = firebase.database();

// Firestore offline persistence (best-effort)
if (!DEMO_MODE) {
  db.enablePersistence({ synchronizeTabs: true }).catch(() => {});
}

// Service worker for FCM background push notifications
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('/sw.js')
    .catch(() => { /* SW unavailable on this origin — FCM background push disabled */ });
}
