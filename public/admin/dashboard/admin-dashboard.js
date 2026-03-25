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

// Fetch user role and complaints
document.addEventListener('DOMContentLoaded', function() {
    const user = auth.currentUser;

    if (user) {
        // Fetch user role from Firestore
        db.collection('users').doc(user.uid).get()
            .then((doc) => {
                if (doc.exists) {
                    const userData = doc.data();
                    const userRole = userData.role;

                    // Display user role
                    document.getElementById('adminRole').textContent = userRole;

                    // Fetch complaints based on role
                    fetchComplaints(userRole);
                } else {
                    console.error('User data not found');
                }
            })
            .catch((error) => {
                console.error('Error fetching user data:', error);
            });
    } else {
        window.location.href = '../login.php';
    }
});

// Fetch complaints based on role
function fetchComplaints(userRole) {
    let complaintsRef = db.collection('complaints');

    // Filter complaints based on role
    switch (userRole) {
        case 'Program Chair':
        case 'Vice Chair':
            complaintsRef = complaintsRef.where('school', '==', 'STME'); // Example: STME complaints
            break;
        case 'Deputy Registrar':
            complaintsRef = complaintsRef.where('type', '==', 'Administrative'); // Example: Administrative complaints
            break;
        case 'Campus Director':
            complaintsRef = complaintsRef.where('campus', '==', 'Mumbai'); // Example: Mumbai campus complaints
            break;
        case 'Hostel Rector':
        case 'Vice Chair - Hostel':
        case 'Hostel Warden':
        case 'Hostel Committee':
            complaintsRef = complaintsRef.where('type', '==', 'Hostel'); // Example: Hostel complaints
            break;
        default:
            complaintsRef = complaintsRef; // No filter for other roles
    }

    // Fetch and display complaints
    complaintsRef.get()
        .then((querySnapshot) => {
            const complaints = [];
            querySnapshot.forEach((doc) => {
                complaints.push({ id: doc.id, ...doc.data() });
            });
            displayComplaints(complaints);
        })
        .catch((error) => {
            console.error('Error fetching complaints:', error);
        });
}

// Display complaints
function displayComplaints(complaints) {
    const complaintsContainer = document.getElementById('complaints');
    complaintsContainer.innerHTML = '';

    complaints.forEach(complaint => {
        const complaintItem = document.createElement('div');
        complaintItem.className = 'complaint-item';

        complaintItem.innerHTML = `
            <h3>Complaint #${complaint.referenceNumber}</h3>
            <p><strong>School:</strong> ${complaint.school}</p>
            <p><strong>Type:</strong> ${complaint.type}</p>
            <p><strong>Subtype:</strong> ${complaint.subtype}</p>
            <p><strong>Details:</strong> ${complaint.details}</p>
            <p><strong>Status:</strong> <span class="status ${complaint.status.toLowerCase().replace(' ', '-')}">${complaint.status}</span></p>
            <p><strong>Submitted On:</strong> ${complaint.submittedOn}</p>
            <p><strong>Last Updated:</strong> ${complaint.lastUpdated}</p>
            <div class="actions">
                <button class="resolve" onclick="resolveComplaint('${complaint.id}')">Resolve</button>
                <button class="reject" onclick="rejectComplaint('${complaint.id}')">Reject</button>
            </div>
        `;

        complaintsContainer.appendChild(complaintItem);
    });
}

// Resolve a complaint
function resolveComplaint(complaintId) {
    db.collection('complaints').doc(complaintId).update({
        status: 'Resolved',
        lastUpdated: new Date().toISOString().split('T')[0]
    })
    .then(() => {
        alert('Complaint resolved successfully');
        location.reload(); // Refresh the page
    })
    .catch((error) => {
        console.error('Error resolving complaint:', error);
    });
}

// Reject a complaint
function rejectComplaint(complaintId) {
    db.collection('complaints').doc(complaintId).update({
        status: 'Rejected',
        lastUpdated: new Date().toISOString().split('T')[0]
    })
    .then(() => {
        alert('Complaint rejected successfully');
        location.reload(); // Refresh the page
    })
    .catch((error) => {
        console.error('Error rejecting complaint:', error);
    });
}