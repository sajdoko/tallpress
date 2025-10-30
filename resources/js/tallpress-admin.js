// TallPress Package JavaScript - Admin Only
import './quill-editor';

// Livewire 3 already includes Alpine.js, so we don't need to import it separately
// The setupEditor and quillEditor functions are already available via window

// Sidebar toggle functionality
function initSidebar() {
    const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    const sidebarClose = document.querySelector('[data-sidebar-close]');
    const sidebar = document.querySelector('.admin-sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            // Remove 'hidden' class to show sidebar on mobile (CSS uses :not(.hidden))
            sidebar.classList.remove('hidden');
        });
    }

    if (sidebarClose && sidebar) {
        sidebarClose.addEventListener('click', () => {
            // Add 'hidden' class to hide sidebar on mobile
            sidebar.classList.add('hidden');
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        // Only on mobile (viewport width < 1024px)
        if (window.innerWidth >= 1024) return;

        // If sidebar is visible and click is outside sidebar and toggle button
        if (sidebar && !sidebar.classList.contains('hidden') &&
            !sidebar.contains(e.target) &&
            sidebarToggle && !sidebarToggle.contains(e.target)) {
            sidebar.classList.add('hidden');
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initSidebar);

// Re-initialize after Livewire navigation
document.addEventListener('livewire:navigated', initSidebar);


// Toast Manager for Alpine.js
window.toastManager = function() {
    return {
        toasts: [],
        nextId: 1,

        addToast(data) {
            const id = this.nextId++;
            const toast = {
                id: id,
                message: data.message || data[0]?.message || 'Notification',
                type: data.type || data[0]?.type || 'info',
                visible: true
            };

            this.toasts.push(toast);

            // Trigger animation
            setTimeout(() => {
                toast.visible = true;
            }, 10);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                this.removeToast(id);
            }, 5000);
        },

        removeToast(id) {
            const index = this.toasts.findIndex(t => t.id === id);
            if (index !== -1) {
                this.toasts[index].visible = false;
                // Remove from array after animation completes
                setTimeout(() => {
                    this.toasts.splice(index, 1);
                }, 300);
            }
        }
    }
};


// copyToClipboard function
window.copyToClipboard = function(text) {
    if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(text);
    } else {
        // Fallback for older browsers
        const textArea = document.createElement("textarea");
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand("copy");
        document.body.removeChild(textArea);
    }
};