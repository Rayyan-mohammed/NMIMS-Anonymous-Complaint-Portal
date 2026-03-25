document.addEventListener('DOMContentLoaded', function () {
    const regRole = document.getElementById('regRole');
    const schoolContainer = document.getElementById('schoolContainer');
    const programChairContainer = document.getElementById('programChairContainer');
    const schoolDropdown = document.getElementById('school');
    const programChairSelect = document.getElementById('programChairSelect');

    // Role change event listener
    regRole.addEventListener('change', function () {
        if (regRole.value === 'programChair') {
            schoolContainer.style.display = 'block';
            programChairContainer.style.display = 'block';
        } else {
            schoolContainer.style.display = 'none';
            programChairContainer.style.display = 'none';
        }
    });

    // School change event listener
    schoolDropdown.addEventListener('change', function () {
        const selectedSchool = schoolDropdown.value;
        if (selectedSchool) {
            // Fetch program chairs dynamically based on the selected school
            fetchProgramChairs(selectedSchool);
        } else {
            // Clear program chair dropdown if no school is selected
            programChairSelect.innerHTML = '<option value="" selected disabled>Select program chair</option>';
        }
    });

    // Function to fetch program chairs
    function fetchProgramChairs(school) {
        // Clear existing options
        programChairSelect.innerHTML = '<option value="" selected disabled>Select program chair</option>';

        // Simulate fetching data (replace with actual data fetching logic)
        const programChairs = {
            STME: [
                "Prof. Chandrakanth Wani (STME Chair)",
                "Prof. Vinayak Mukkawar (STME Vice Chair)"
            ],
            SBM: [
                "Dr. Williams (SBM Chair)",
                "Dr. Brown (SBM Vice Chair)"
            ],
            SOL: [
                "Dr. Davis (SOL Chair)",
                "Dr. Miller (SOL Vice Chair)"
            ],
            SPTM: [
                "Dr. Wilson (SPTM Chair)",
                "Dr. Moore (SPTM Vice Chair)"
            ]
        };

        const chairs = programChairs[school] || [];
        chairs.forEach(chair => {
            const option = document.createElement('option');
            option.value = chair;
            option.textContent = chair;
            programChairSelect.appendChild(option);
        });
    }

    // Form submission
    const registrationForm = document.getElementById('registrationForm');
    registrationForm.addEventListener('submit', function (e) {
        e.preventDefault();

        // Get form values
        const firstName = document.getElementById('firstName').value;
        const lastName = document.getElementById('lastName').value;
        const email = document.getElementById('email').value;
        const phone = document.getElementById('phone').value;
        const role = document.getElementById('regRole').value;
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const school = role === 'programChair' ? schoolDropdown.value : null;
        const programChair = role === 'programChair' ? programChairSelect.value : null;

        // Reset error and success messages
        const registrationError = document.getElementById('registrationError');
        const registrationSuccess = document.getElementById('registrationSuccess');
        registrationError.style.display = 'none';
        registrationSuccess.style.display = 'none';

        // Basic validation
        if (!firstName || !lastName || !email || !phone || !role || !username || !password || !confirmPassword) {
            registrationError.textContent = 'Please fill in all fields';
            registrationError.style.display = 'block';
            return;
        }

        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            registrationError.textContent = 'Please enter a valid email address';
            registrationError.style.display = 'block';
            return;
        }

        // Password validation
        if (password !== confirmPassword) {
            registrationError.textContent = 'Passwords do not match';
            registrationError.style.display = 'block';
            return;
        }

        if (password.length < 8) {
            registrationError.textContent = 'Password must be at least 8 characters long';
            registrationError.style.display = 'block';
            return;
        }

        // Simulate successful registration
        registrationSuccess.textContent = 'Registration successful!';
        registrationSuccess.style.display = 'block';
        registrationForm.reset();

        // Hide success message after 3 seconds
        setTimeout(function () {
            registrationSuccess.style.display = 'none';
        }, 3000);
    });
});