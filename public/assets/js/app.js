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
    const copyReferenceBtn = document.getElementById('copyReferenceBtn');
    const copyStatus = document.getElementById('copyStatus');
    const newComplaintBtn = document.getElementById('newComplaintBtn');
    const schoolSelect = document.getElementById('school');
    const anonymousCheck = document.getElementById('anonymousCheck');
    const studentIdentityContainer = document.getElementById('studentIdentityContainer');
    const studentName = document.getElementById('studentName');
    const sapId = document.getElementById('sapId');
    const studyYear = document.getElementById('studyYear');
    const yearHint = document.getElementById('yearHint');
    const submitBtn = document.getElementById('submitBtn');
    const escalationSelect = document.getElementById('escalation');
    const programChairContainer = document.getElementById('programChairContainer');
    const programChairCheckboxes = document.getElementById('programChairCheckboxes');
    const formSummary = document.getElementById('formSummary');
    const complaintDetails = document.getElementById('complaintDetails');

    const submitButtonDefaultText = submitBtn ? submitBtn.textContent : 'Submit Complaint';
    const copyButtonDefaultText = copyReferenceBtn ? copyReferenceBtn.textContent : 'Copy Reference Number';

    function getOrCreateFieldError(fieldId) {
        const field = document.getElementById(fieldId);
        if (!field) {
            return null;
        }

        const errorId = fieldId + 'Error';
        let errorNode = document.getElementById(errorId);
        if (!errorNode) {
            errorNode = document.createElement('p');
            errorNode.id = errorId;
            errorNode.className = 'field-error';
            errorNode.setAttribute('aria-live', 'polite');
            field.insertAdjacentElement('afterend', errorNode);
        }

        const existingDescribedBy = (field.getAttribute('aria-describedby') || '').trim();
        if (!existingDescribedBy.includes(errorId)) {
            field.setAttribute('aria-describedby', [existingDescribedBy, errorId].filter(Boolean).join(' '));
        }

        return errorNode;
    }

    function setFieldError(fieldId, message) {
        const field = document.getElementById(fieldId);
        const errorNode = getOrCreateFieldError(fieldId);
        if (!field || !errorNode) {
            return;
        }

        if (message) {
            field.classList.add('is-invalid');
            field.setAttribute('aria-invalid', 'true');
            errorNode.textContent = message;
            errorNode.style.display = 'block';
        } else {
            field.classList.remove('is-invalid');
            field.removeAttribute('aria-invalid');
            errorNode.textContent = '';
            errorNode.style.display = 'none';
        }
    }

    function showSummary(messages, titleText) {
        if (!formSummary) {
            return;
        }

        if (!Array.isArray(messages) || messages.length === 0) {
            formSummary.hidden = true;
            formSummary.innerHTML = '';
            return;
        }

        const title = titleText || 'Please review the highlighted fields.';
        const listItems = messages.map((msg) => '<li>' + msg + '</li>').join('');
        formSummary.innerHTML = '<strong>' + title + '</strong><ul>' + listItems + '</ul>';
        formSummary.classList.remove('is-success');
        formSummary.classList.add('is-error');
        formSummary.hidden = false;
        formSummary.focus();
    }

    function showSuccessSummary(message) {
        if (!formSummary) {
            return;
        }

        formSummary.innerHTML = '<strong>' + message + '</strong>';
        formSummary.classList.remove('is-error');
        formSummary.classList.add('is-success');
        formSummary.hidden = false;
    }

    function validateSchool() {
        if (!schoolSelect.value) {
            setFieldError('school', 'Please select your school.');
            return 'School is required.';
        }

        setFieldError('school', '');
        return null;
    }

    function validateComplaintType() {
        if (!complaintType.value) {
            setFieldError('complaintType', 'Please select whether this is an academic or hostel complaint.');
            return 'Complaint type is required.';
        }

        setFieldError('complaintType', '');
        return null;
    }

    function validateComplaintSubType() {
        if (complaintType.value === 'academic' && !academicSubType.value) {
            setFieldError('academicSubType', 'Please choose an academic issue type.');
            return 'Academic issue type is required.';
        }

        if (complaintType.value === 'hostel' && !hostelSubType.value) {
            setFieldError('hostelSubType', 'Please choose a hostel issue type.');
            return 'Hostel issue type is required.';
        }

        setFieldError('academicSubType', '');
        setFieldError('hostelSubType', '');
        return null;
    }

    function validateEscalation() {
        if (!escalationSelect.value) {
            setFieldError('escalation', 'Please select who should receive this complaint.');
            return 'Escalation authority is required.';
        }

        setFieldError('escalation', '');

        if (escalationSelect.value === 'programChair' && !document.querySelector('input[name="programChair[]"]:checked')) {
            setFieldError('escalation', 'Please select at least one program chair for the selected school.');
            return 'At least one program chair must be selected.';
        }

        return null;
    }

    function validateIdentityFields() {
        const issues = [];

        if (anonymousCheck.checked) {
            setFieldError('studentName', '');
            setFieldError('sapId', '');
            setFieldError('studyYear', '');
            return issues;
        }

        if (!studentName.value.trim()) {
            setFieldError('studentName', 'Name is required when anonymous mode is off.');
            issues.push('Student name is required when not anonymous.');
        } else {
            setFieldError('studentName', '');
        }

        if (!sapId.value.trim()) {
            setFieldError('sapId', 'SAP ID is required when anonymous mode is off.');
            issues.push('SAP ID is required when not anonymous.');
        } else {
            setFieldError('sapId', '');
        }

        if (!studyYear.value) {
            setFieldError('studyYear', 'Please select your study year.');
            issues.push('Study year is required when not anonymous.');
        } else {
            setFieldError('studyYear', '');
        }

        return issues;
    }

    function validateYearRange() {
        const selectedSchool = schoolSelect.options[schoolSelect.selectedIndex];
        const maxYear = selectedSchool ? parseInt(selectedSchool.getAttribute('data-max-year'), 10) : 0;
        const chosenYear = studyYear.value ? parseInt(studyYear.value, 10) : null;

        if (chosenYear !== null && (!maxYear || chosenYear < 1 || chosenYear > maxYear)) {
            setFieldError('studyYear', 'Selected year is outside the allowed range for this school.');
            return 'Selected year is invalid for the chosen school.';
        }

        if (!anonymousCheck.checked && studyYear.value) {
            setFieldError('studyYear', '');
        }

        return null;
    }

    function validateComplaintDetails() {
        const details = complaintDetails.value.trim();
        if (!details) {
            setFieldError('complaintDetails', 'Please provide your complaint details.');
            return 'Complaint details are required.';
        }

        if (details.length < 10) {
            setFieldError('complaintDetails', 'Please provide at least 10 characters.');
            return 'Complaint details must be at least 10 characters.';
        }

        setFieldError('complaintDetails', '');
        return null;
    }

    function validateForm(showInlineSummary) {
        const issues = [];

        const validators = [
            validateSchool,
            validateComplaintType,
            validateComplaintSubType,
            validateEscalation,
            validateYearRange,
            validateComplaintDetails,
        ];

        validators.forEach((validator) => {
            const issue = validator();
            if (issue) {
                issues.push(issue);
            }
        });

        validateIdentityFields().forEach((issue) => issues.push(issue));

        if (showInlineSummary) {
            if (issues.length > 0) {
                showSummary(issues);
            } else {
                showSummary([]);
            }
        }

        return issues.length === 0;
    }

    function setSubmitButtonState(state) {
        if (!submitBtn) {
            return;
        }

        submitBtn.classList.remove('is-loading', 'is-success');
        if (state === 'loading') {
            submitBtn.disabled = true;
            submitBtn.classList.add('is-loading');
            submitBtn.textContent = 'Submitting...';
            return;
        }

        if (state === 'success') {
            submitBtn.disabled = true;
            submitBtn.classList.add('is-success');
            submitBtn.textContent = 'Submitted';
            return;
        }

        submitBtn.disabled = false;
        submitBtn.textContent = submitButtonDefaultText;
    }

    function setCopyButtonState(state) {
        if (!copyReferenceBtn) {
            return;
        }

        copyReferenceBtn.classList.remove('is-loading', 'is-success');
        if (state === 'loading') {
            copyReferenceBtn.disabled = true;
            copyReferenceBtn.classList.add('is-loading');
            copyReferenceBtn.textContent = 'Copying...';
            return;
        }

        if (state === 'success') {
            copyReferenceBtn.disabled = false;
            copyReferenceBtn.classList.add('is-success');
            copyReferenceBtn.textContent = 'Copied';
            return;
        }

        copyReferenceBtn.disabled = false;
        copyReferenceBtn.textContent = copyButtonDefaultText;
    }

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
        updateYearOptions();
        if (escalationSelect.value === 'programChair') {
            updateProgramChairOptions(this.value);
        }

        validateSchool();
        validateYearRange();
    });

    anonymousCheck.addEventListener('change', function () {
        const isAnonymous = this.checked;
        if (isAnonymous) {
            studentIdentityContainer.style.display = 'none';
            studentName.removeAttribute('required');
            sapId.removeAttribute('required');
            studyYear.removeAttribute('required');
            studentName.value = '';
            sapId.value = '';
            studyYear.value = '';
        } else {
            studentIdentityContainer.style.display = 'block';
            studentName.setAttribute('required', 'required');
            sapId.setAttribute('required', 'required');
            studyYear.setAttribute('required', 'required');
        }

        updateSubmitAvailability();
        validateIdentityFields();
    });

    studentName.addEventListener('input', updateSubmitAvailability);
    sapId.addEventListener('input', updateSubmitAvailability);
    studyYear.addEventListener('change', updateSubmitAvailability);

    complaintType.addEventListener('change', function () {
        validateComplaintType();
        validateComplaintSubType();
    });
    academicSubType.addEventListener('change', validateComplaintSubType);
    hostelSubType.addEventListener('change', validateComplaintSubType);
    escalationSelect.addEventListener('change', validateEscalation);
    complaintDetails.addEventListener('input', validateComplaintDetails);
    studentName.addEventListener('input', validateIdentityFields);
    sapId.addEventListener('input', validateIdentityFields);
    studyYear.addEventListener('change', function () {
        validateIdentityFields();
        validateYearRange();
    });

    function updateYearOptions() {
        studyYear.innerHTML = '<option value="" selected>Select year</option>';

        const selected = schoolSelect.options[schoolSelect.selectedIndex];
        const maxYear = selected ? parseInt(selected.getAttribute('data-max-year'), 10) : 0;

        if (!maxYear || Number.isNaN(maxYear)) {
            yearHint.textContent = '';
            return;
        }

        for (let i = 1; i <= maxYear; i += 1) {
            const option = document.createElement('option');
            option.value = String(i);
            option.textContent = 'Year ' + i;
            studyYear.appendChild(option);
        }

        yearHint.textContent = 'This school allows Year 1 to Year ' + maxYear + '.';
        updateSubmitAvailability();
    }

    function updateSubmitAvailability() {
        if (!submitBtn) {
            return;
        }

        const identityReady = anonymousCheck.checked
            || (studentName.value.trim() !== '' && sapId.value.trim() !== '' && studyYear.value !== '');

        submitBtn.disabled = !identityReady;
        submitBtn.title = identityReady
            ? ''
            : 'Fill Name, SAP ID, and Year or select anonymous to continue.';
    }

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

        if (!validateForm(true)) {
            return;
        }

        setSubmitButtonState('loading');
        showSummary([]);

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
            showSuccessSummary('Complaint submitted successfully. Please save your reference number.');
            setSubmitButtonState('success');
            complaintForm.style.display = 'none';
            confirmationMessage.style.display = 'block';
            complaintForm.reset();
            complaintType.dispatchEvent(new Event('change'));
            escalationSelect.dispatchEvent(new Event('change'));
        } catch (error) {
            setSubmitButtonState('default');
            showSummary([error.message || 'Error submitting complaint. Please try again.'], 'Submission failed.');
        } finally {
            if (confirmationMessage.style.display !== 'block') {
                setSubmitButtonState('default');
            }
        }
    });

    newComplaintBtn.addEventListener('click', function () {
        confirmationMessage.style.display = 'none';
        complaintForm.style.display = 'block';
        setSubmitButtonState('default');
        showSummary([]);
        if (copyStatus) {
            copyStatus.textContent = '';
        }
    });

    if (copyReferenceBtn) {
        copyReferenceBtn.addEventListener('click', async function () {
            const reference = referenceNumberSpan ? referenceNumberSpan.textContent.trim() : '';
            if (!reference) {
                if (copyStatus) {
                    copyStatus.textContent = 'Reference number is not available yet.';
                }
                return;
            }

            try {
                setCopyButtonState('loading');
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(reference);
                } else {
                    const fallbackInput = document.createElement('input');
                    fallbackInput.value = reference;
                    document.body.appendChild(fallbackInput);
                    fallbackInput.select();
                    document.execCommand('copy');
                    document.body.removeChild(fallbackInput);
                }

                if (copyStatus) {
                    copyStatus.textContent = 'Reference number copied to clipboard.';
                }
                setCopyButtonState('success');
                window.setTimeout(function () {
                    setCopyButtonState('default');
                }, 1600);
            } catch (error) {
                if (copyStatus) {
                    copyStatus.textContent = 'Could not copy automatically. Please copy it manually.';
                }
                setCopyButtonState('default');
            }
        });
    }

    updateYearOptions();
    anonymousCheck.dispatchEvent(new Event('change'));
    updateSubmitAvailability();
    validateForm(false);
});