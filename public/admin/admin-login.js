document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('loginForm');
    const loginError = document.getElementById('loginError');

    // Firebase configuration
    const firebaseConfig = {
        apiKey: "AIzaSyCNSwOqFgXS7bt_IGjMlSlRmNkjplBXG1M",
        authDomain: "nmims-anoncomplaints.firebaseapp.com",
        projectId: "nmims-anoncomplaints",
        storageBucket: "nmims-anoncomplaints.appspot.com",
        messagingSenderId: "110748879243",
        appId: "1:110748879243:web:8e8f4207cba05f2a0d478e",
        measurementId: "G-L83VRLLKJC"
    };

    // Initialize Firebase
    const app = firebase.initializeApp(firebaseConfig);
    const auth = firebase.auth(app);
    const db = firebase.firestore(app);

    loginForm.addEventListener('submit', function (e) {
        e.preventDefault();

        // Get form values
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const role = document.getElementById('role').value;

        // Reset error message
        loginError.style.display = 'none';

        // Sign in with Firebase Authentication
        auth.signInWithEmailAndPassword(email, password)
            .then((userCredential) => {
                const user = userCredential.user;

                // Fetch user role from Firestore
                return db.collection('users').doc(user.uid).get();
            })
            .then((doc) => {
                if (doc.exists()) {
                    const userData = doc.data();
                    const userRole = userData.role;

                    // Redirect based on role
                    if (userRole === role) {
                        window.location.href = `${role}-dashboard.html`;
                    } else {
                        loginError.textContent = 'Role mismatch. Please select the correct role.';
                        loginError.style.display = 'block';
                    }
                } else {
                    loginError.textContent = 'User data not found. Please contact support.';
                    loginError.style.display = 'block';
                }
            })
            .catch((error) => {
                loginError.textContent = error.message;
                loginError.style.display = 'block';
            });
    });
});