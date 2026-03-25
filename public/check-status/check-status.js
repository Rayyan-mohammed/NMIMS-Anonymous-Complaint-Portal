document.addEventListener('DOMContentLoaded', function() {
    const statusForm = document.getElementById('statusForm');
    const statusResult = document.getElementById('statusResult');
    const statusText = document.getElementById('statusText');
    const dateSubmitted = document.getElementById('dateSubmitted');
    const dateUpdated = document.getElementById('dateUpdated');
    const errorMessage = document.getElementById('errorMessage');
    
    statusForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get reference number
        const referenceNumber = document.getElementById('referenceNumber').value.trim();
        
        if (!referenceNumber) {
            showError('Please enter a valid reference number');
            return;
        }
        
        // Reset UI
        hideError();
        statusResult.style.display = 'none';
        
        // Call API to check status
        checkStatus(referenceNumber);
    });
    
    function checkStatus(referenceNumber) {
        // In a real app, this would be an API call
        fetch(`/api/complaints/status/${referenceNumber}`)
            .then(response => {
                if (!response.ok) {
                    if (response.status === 404) {
                        throw new Error('Complaint not found. Please check your reference number.');
                    }
                    throw new Error('An error occurred while checking status. Please try again later.');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    displayStatus(data.complaint);
                } else {
                    showError(data.message || 'An error occurred. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError(error.message);
                
                // For demo, show sample data
                if (referenceNumber.startsWith('COMP-')) {
                    const demoData = {
                        status: getRandomStatus(),
                        dateCreated: new Date(Date.now() - Math.random() * 30 * 24 * 60 * 60 * 1000).toISOString(),
                        dateUpdated: new Date().toISOString()
                    };
                    displayStatus(demoData);
                }
            });
    }
    
    function displayStatus(complaint) {
        // Update UI with status information
        statusText.textContent = formatStatus(complaint.status);
        dateSubmitted.textContent = formatDate(complaint.dateCreated);
        dateUpdated.textContent = formatDate(complaint.dateUpdated);
        
        // Style based on status
        statusResult.className = 'status-result status-' + complaint.status.toLowerCase();
        
        // Show result
        statusResult.style.display = 'block';
    }
    
    function formatStatus(status) {
        // Format status for display
        const statusMap = {
            'pending': 'Pending Review',
            'inProgress': 'In Progress',
            'resolved': 'Resolved',
            'rejected': 'Not Approved'
        };
        
        return statusMap[status] || status;
    }
    
    function formatDate(dateString) {
        // Format date for display
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    }
    
    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.style.display = 'block';
    }
    
    function hideError() {
        errorMessage.textContent = '';
        errorMessage.style.display = 'none';
    }
    
    // Helper for demo only
    function getRandomStatus() {
        const statuses = ['pending', 'inProgress', 'resolved', 'rejected'];
        return statuses[Math.floor(Math.random() * statuses.length)];
    }
});