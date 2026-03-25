document.addEventListener('DOMContentLoaded', function () {
    const complaintForm = document.getElementById('complaintForm');
    const complaintType = document.getElementById('complaintType');
    const academicSubTypeContainer = document.getElementById('academicSubTypeContainer');
    const academicSubType = document.getElementById('academicSubType');
    const hostelSubTypeContainer = document.getElementById('hostelSubTypeContainer');
    const hostelSubType = document.getElementById('hostelSubType');
    const hostelContainer = document.getElementById('hostelContainer');
    const confirmationMessage = document.getElementById('confirmationMessage');
    const referenceNumberSpan = document.getElementById('referenceNumber');
    const newComplaintBtn = document.getElementById('newComplaintBtn');
    const schoolSelect = document.getElementById('school');
    const escalationSelect = document.getElementById('escalation');
    const programChairContainer = document.getElementById('programChairContainer');
    const programChairCheckboxes = document.getElementById('programChairCheckboxes');

    complaintType.addEventListener('change', function () {
        if (this.value === 'academic') {
            academicSubTypeContainer.style.display = 'block';
            hostelSubTypeContainer.style.display = 'none';
            hostelContainer.style.display = 'none';
            academicSubType.setAttribute('required', 'required');
            hostelSubType.removeAttribute('required');
        } else if (this.value === 'hostel') {
            academicSubTypeContainer.style.display = 'none';
            hostelSubTypeContainer.style.display = 'block';
            hostelContainer.style.display = 'block';
            hostelSubType.setAttribute('required', 'required');
            academicSubType.removeAttribute('required');
        } else {
            academicSubTypeContainer.style.display = 'none';
            hostelSubTypeContainer.style.display = 'none';
            hostelContainer.style.display = 'none';
            academicSubType.removeAttribute('required');
            hostelSubType.removeAttribute('required');
        }
    });

    escalationSelect.addEventListener('change', function () {
        if (this.value === 'programChair') {
            programChairContainer.style.display = 'block';
            updateProgramChairOptions(schoolSelect.value);
        } else {
            programChairContainer.style.display = 'none';
        }
    });

    schoolSelect.addEventListener('change', function () {
        if (escalationSelect.value === 'programChair') {
            updateProgramChairOptions(this.value);
        }
    });

    function updateProgramChairOptions(school) {
        const options = programChairCheckboxes.querySelectorAll('.program-chair-option');
        options.forEach((option) => {
            const schoolId = option.getAttribute('data-school-id');
            const checkbox = option.querySelector('input[type="checkbox"]');
            if (checkbox) {
                checkbox.checked = false;
            }

            if (school && schoolId === school) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
    }

    complaintForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        if (!validateForm()) {
            return;
        }

        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';

        try {
            const payload = new FormData(complaintForm);
            const response = await fetch('submit_complaint.php', {
                method: 'POST',
                body: payload,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('Submission failed with HTTP ' + response.status);
            }

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Complaint submission failed');
            }

            referenceNumberSpan.textContent = data.reference_number;
            complaintForm.style.display = 'none';
            confirmationMessage.style.display = 'block';
            complaintForm.reset();
            complaintType.dispatchEvent(new Event('change'));
            escalationSelect.dispatchEvent(new Event('change'));
        } catch (error) {
            alert(error.message || 'Error submitting complaint. Please try again.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Complaint';
        }
    });

    function validateForm() {
        if (!schoolSelect.value) {
            alert('Please select a school');
            return false;
        }

        if (!complaintType.value) {
            alert('Please select complaint type');
            return false;
        }

        if (complaintType.value === 'academic' && !academicSubType.value) {
            alert('Please select academic issue type');
            return false;
        }

        if (complaintType.value === 'hostel' && !hostelSubType.value) {
            alert('Please select hostel issue type');
            return false;
        }

        if (!escalationSelect.value) {
            alert('Please select authority to submit to');
            return false;
        }

        if (escalationSelect.value === 'programChair' && !document.querySelector('input[name="programChair[]"]:checked')) {
            alert('Please select at least one program chair');
            return false;
        }

        if (!document.getElementById('complaintDetails').value.trim()) {
            alert('Please provide complaint details');
            return false;
        }

        return true;
    }

    newComplaintBtn.addEventListener('click', function () {
        confirmationMessage.style.display = 'none';
        complaintForm.style.display = 'block';
    });
});