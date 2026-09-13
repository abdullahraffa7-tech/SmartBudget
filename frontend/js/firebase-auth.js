import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.12.5/firebase-app.js';
import {
  getAuth,
  GoogleAuthProvider,
  createUserWithEmailAndPassword,
  signInWithEmailAndPassword,
  signInWithPopup,
  signOut,
  updateProfile,
} from 'https://www.gstatic.com/firebasejs/10.12.5/firebase-auth.js';

const firebaseConfig = {
  apiKey: 'AIzaSyCOY00Yl5GQmbrEv2719-6uLloEE2cTGpo',
  authDomain: 'smartbudget-a5784.firebaseapp.com',
  projectId: 'smartbudget-a5784',
  storageBucket: 'smartbudget-a5784.firebasestorage.app',
  messagingSenderId: '855969011712',
  appId: '1:855969011712:web:f2d99aad2719eca9f960e5',
  measurementId: 'G-9T1BPCSYQD',
};

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const googleProvider = new GoogleAuthProvider();

function firebaseErrorMessage(error) {
  const messages = {
    'auth/email-already-in-use': 'Email sudah terdaftar di Firebase.',
    'auth/invalid-email': 'Format email tidak valid.',
    'auth/invalid-credential': 'Email atau password salah.',
    'auth/operation-not-allowed': 'Provider login belum aktif di Firebase Console.',
    'auth/popup-closed-by-user': 'Login Google dibatalkan.',
    'auth/unauthorized-domain': 'Domain aplikasi belum diizinkan di Firebase Authentication.',
    'auth/weak-password': 'Password minimal 6 karakter.',
  };
  return messages[error?.code] || error?.message || 'Autentikasi Firebase gagal.';
}

async function syncSession(user) {
  const idToken = await user.getIdToken();
  const res = await fetch('backend/auth.php?action=firebase_session', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      idToken,
      uid: user.uid,
      name: user.displayName || user.name || '',
      email: user.email || '',
    }),
  });
  return res.json();
}

async function loginWithEmail(email, password) {
  try {
    const credential = await signInWithEmailAndPassword(auth, email, password);
    return syncSession(credential.user);
  } catch (error) {
    return { success: false, message: firebaseErrorMessage(error) };
  }
}

async function registerWithEmail(name, email, password) {
  try {
    const credential = await createUserWithEmailAndPassword(auth, email, password);
    if (name) await updateProfile(credential.user, { displayName: name });
    return syncSession(credential.user);
  } catch (error) {
    return { success: false, message: firebaseErrorMessage(error) };
  }
}

async function loginWithGoogle() {
  try {
    const credential = await signInWithPopup(auth, googleProvider);
    return syncSession(credential.user);
  } catch (error) {
    return { success: false, message: firebaseErrorMessage(error) };
  }
}

window.financeFirebaseAuth = {
  loginWithEmail,
  registerWithEmail,
  loginWithGoogle,
  signOut: () => signOut(auth),
};

window.dispatchEvent(new Event('financeFirebaseAuthReady'));
