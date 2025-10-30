/**
 * Messaging Functionality
 * Handles message form validation and animations
 */

document.addEventListener('DOMContentLoaded', function() {
    const messageForm = document.getElementById('messageForm');
    const recipientInput = document.getElementById('recipient');
    const messageContent = document.getElementById('messageContent');
    
    // Message form validation
    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {
            if (recipientInput.value.trim() === '') {
                e.preventDefault();
                alert('Please enter a recipient username');
                recipientInput.focus();
                return false;
            }
            
            if (messageContent.value.trim() === '') {
                e.preventDefault();
                alert('Please enter a message');
                messageContent.focus();
                return false;
            }
        });
    }
    
    // Character counter for message
    if (messageContent) {
        const maxChars = 1000;
        const counterDiv = document.createElement('div');
        counterDiv.style.cssText = 'text-align: right; color: var(--text-light); font-size: 12px; margin-top: 5px;';
        messageContent.parentNode.insertBefore(counterDiv, messageContent.nextSibling);
        
        messageContent.addEventListener('input', function() {
            const remaining = maxChars - this.value.length;
            counterDiv.textContent = remaining + ' characters remaining';
            
            if (remaining < 50) {
                counterDiv.style.color = '#e74c3c';
            } else {
                counterDiv.style.color = 'var(--text-light)';
            }
            
            if (remaining < 0) {
                this.value = this.value.substring(0, maxChars);
            }
        });
        
        // Trigger initial counter
        messageContent.dispatchEvent(new Event('input'));
    }
    
    // Animate message cards
    const messageCards = document.querySelectorAll('.message-card');
    messageCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Add hover effects to message cards
    messageCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 10px 30px rgba(0,0,0,0.2)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });
});