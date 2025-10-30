/**
 * Dashboard Functionality
 * Handles post creation and image preview
 */

document.addEventListener('DOMContentLoaded', function() {
    // Image preview functionality
    const postImageInput = document.getElementById('postImage');
    const imagePreview = document.getElementById('imagePreview');
    
    if (postImageInput && imagePreview) {
        postImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Only JPG, PNG and GIF images are allowed');
                    this.value = '';
                    imagePreview.style.display = 'none';
                    return;
                }
                
                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('Image size must be less than 5MB');
                    this.value = '';
                    imagePreview.style.display = 'none';
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(event) {
                    imagePreview.innerHTML = '<img src="' + event.target.result + '" alt="Preview" style="width: 100%; max-height: 300px; object-fit: cover; border-radius: 8px;">';
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.style.display = 'none';
                imagePreview.innerHTML = '';
            }
        });
    }
    
    // Post form validation
    const postForm = document.getElementById('postForm');
    if (postForm) {
        postForm.addEventListener('submit', function(e) {
            const content = document.getElementById('postContent').value.trim();
            
            if (content === '') {
                e.preventDefault();
                alert('Please enter some content for your post');
                return false;
            }
        });
    }
    
    // Character counter for post content
    const postContent = document.getElementById('postContent');
    if (postContent) {
        const maxChars = 5000;
        const counterDiv = document.createElement('div');
        counterDiv.style.cssText = 'text-align: right; color: var(--text-light); font-size: 12px; margin-top: 5px;';
        postContent.parentNode.insertBefore(counterDiv, postContent.nextSibling);
        
        postContent.addEventListener('input', function() {
            const remaining = maxChars - this.value.length;
            counterDiv.textContent = remaining + ' characters remaining';
            
            if (remaining < 100) {
                counterDiv.style.color = '#e74c3c';
            } else {
                counterDiv.style.color = 'var(--text-light)';
            }
            
            if (remaining < 0) {
                this.value = this.value.substring(0, maxChars);
            }
        });
        
        // Trigger initial counter update
        postContent.dispatchEvent(new Event('input'));
    }
    
    // Smooth scroll animation for new posts
    const postCards = document.querySelectorAll('.post-card');
    postCards.forEach((card, index) => {
        card.style.animationDelay = (index * 0.1) + 's';
    });
    
    // Image modal for full-size viewing
    const postImages = document.querySelectorAll('.post-image');
    postImages.forEach(img => {
        img.addEventListener('click', function() {
            const modal = document.createElement('div');
            modal.style.cssText = 'position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); display: flex; align-items: center; justify-content: center; cursor: pointer;';
            
            const modalImg = document.createElement('img');
            modalImg.src = this.src;
            modalImg.style.cssText = 'max-width: 90%; max-height: 90%; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.5);';
            
            modal.appendChild(modalImg);
            document.body.appendChild(modal);
            
            modal.addEventListener('click', function() {
                document.body.removeChild(modal);
            });
        });
    });
});