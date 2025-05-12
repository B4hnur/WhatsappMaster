// Main JavaScript file for WhatsApp Messaging System

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Contact selection for messaging
    const contactCheckboxes = document.querySelectorAll('.contact-checkbox');
    if (contactCheckboxes.length > 0) {
        const selectAllCheckbox = document.getElementById('select-all-contacts');
        
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                contactCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                updateSelectedCount();
            });
        }

        contactCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectedCount();
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = Array.from(contactCheckboxes).every(cb => cb.checked);
                }
            });
        });
    }

    // Update selected contacts count
    function updateSelectedCount() {
        const selectedCount = document.querySelectorAll('.contact-checkbox:checked').length;
        const countElement = document.getElementById('selected-count');
        if (countElement) {
            countElement.textContent = selectedCount;
        }
    }

    // Template selection
    const templateCards = document.querySelectorAll('.template-card');
    if (templateCards.length > 0) {
        templateCards.forEach(card => {
            card.addEventListener('click', function() {
                const templateId = this.getAttribute('data-template-id');
                const templateContent = document.querySelector(`#template-content-${templateId}`).textContent;
                
                document.getElementById('selected-template-id').value = templateId;
                const messageTextarea = document.getElementById('message-text');
                if (messageTextarea) {
                    messageTextarea.value = templateContent;
                }
                
                templateCards.forEach(c => c.classList.remove('border-primary'));
                this.classList.add('border-primary');
            });
        });
    }

    // Send WhatsApp message
    const sendMessageForm = document.getElementById('send-message-form');
    if (sendMessageForm) {
        sendMessageForm.addEventListener('submit', function(e) {
            const selectedContacts = document.querySelectorAll('.contact-checkbox:checked');
            if (selectedContacts.length === 0) {
                e.preventDefault();
                alert('Zəhmət olmasa ən azı bir əlaqə seçin.');
                return false;
            }
            
            const messageText = document.getElementById('message-text').value.trim();
            if (!messageText) {
                e.preventDefault();
                alert('Zəhmət olmasa mesaj mətni daxil edin.');
                return false;
            }
            
            // Show sending indicator
            document.getElementById('sending-indicator').classList.remove('d-none');
        });
    }

    // Search functionality for contacts
    const searchInput = document.getElementById('search-contacts');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const contactItems = document.querySelectorAll('.contact-item');
            
            contactItems.forEach(item => {
                const contactName = item.querySelector('.contact-name').textContent.toLowerCase();
                const contactPhone = item.querySelector('.contact-phone').textContent.toLowerCase();
                
                if (contactName.includes(searchTerm) || contactPhone.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // Message preview function
    const previewMessageBtn = document.getElementById('preview-message');
    if (previewMessageBtn) {
        previewMessageBtn.addEventListener('click', function() {
            const messageText = document.getElementById('message-text').value;
            const previewArea = document.getElementById('message-preview');
            
            if (previewArea) {
                previewArea.innerHTML = messageText.replace(/\n/g, '<br>');
                document.getElementById('preview-container').classList.remove('d-none');
            }
        });
    }

    // Import contacts from CSV
    const importForm = document.getElementById('import-contacts-form');
    if (importForm) {
        importForm.addEventListener('submit', function(e) {
            const fileInput = document.getElementById('csv-file');
            if (fileInput && fileInput.files.length === 0) {
                e.preventDefault();
                alert('Zəhmət olmasa CSV faylı seçin.');
                return false;
            }
        });
    }

    // Character counter for message textarea
    const messageTextarea = document.getElementById('message-text');
    if (messageTextarea) {
        const charCounter = document.getElementById('char-count');
        
        messageTextarea.addEventListener('input', function() {
            if (charCounter) {
                charCounter.textContent = this.value.length;
            }
        });
    }

    // Confirmation for delete actions
    const deleteButtons = document.querySelectorAll('.delete-confirm');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Bu elementi silmək istədiyinizə əminsiniz?')) {
                e.preventDefault();
            }
        });
    });
});

// Function to send WhatsApp message via URL scheme
function sendWhatsAppMessage(phone, message) {
    // Prepare the phone number (remove spaces, dashes, etc.)
    phone = phone.replace(/\D/g, '');
    
    // Ensure it starts with country code
    if (!phone.startsWith('+')) {
        if (!phone.startsWith('994')) {
            phone = '994' + phone;
        }
    }
    
    // Encode the message for URL
    const encodedMessage = encodeURIComponent(message);
    
    // Create WhatsApp URL
    const whatsappUrl = `https://api.whatsapp.com/send?phone=${phone}&text=${encodedMessage}`;
    
    // Open in new tab
    window.open(whatsappUrl, '_blank');
    
    return true;
}
