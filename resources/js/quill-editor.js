import Quill from 'quill';
import 'quill/dist/quill.snow.css';

window.setupEditor = function (content, uploadUrl = null) {
    let quill;

    return {
        content: content,
        uploadUrl: uploadUrl,

        init() {
            const element = this.$el.querySelector("#quill-editor");
            if (!element) {
                console.error("Quill editor element not found");
                return;
            }

            // Initialize Quill with expanded toolbar
            quill = new Quill(element, {
                theme: 'snow',
                modules: {
                    toolbar: {
                        container: [
                            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            ['blockquote', 'code-block'],
                            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                            [{ 'script': 'sub' }, { 'script': 'super' }],
                            [{ 'indent': '-1' }, { 'indent': '+1' }],
                            [{ 'color': [] }, { 'background': [] }],
                            [{ 'align': [] }],
                            ['link', 'image'],
                            ['clean']
                        ],
                        handlers: {
                            image: () => this.openMediaLibrary(quill)
                        }
                    }
                },
                placeholder: 'Write your content here...',
            });

            // Set initial content
            if (this.content) {
                quill.root.innerHTML = this.content;
            }

            // Update Alpine data on text change
            quill.on('text-change', () => {
                this.content = quill.root.innerHTML;
            });

            this.quill = quill;

            // Listen for media selection from picker
            window.addEventListener('mediaSelected', (event) => {
                const range = quill.getSelection(true) || { index: quill.getLength() };
                quill.insertEmbed(range.index, 'image', event.detail[0].url);
                quill.setSelection(range.index + 1);
            });

            // Watch for external content changes
            this.$watch('content', (newContent) => {
                // Avoid update loop
                if (newContent === quill.root.innerHTML) return;

                quill.root.innerHTML = newContent || '';
            });
        },

        openMediaLibrary(quill) {
            // Store quill instance for later use
            window.currentQuillInstance = quill;

            // Dispatch event to open media picker
            window.dispatchEvent(new CustomEvent('open-media-picker'));
        },

        destroy() {
            if (this.quill) {
                // Quill doesn't have a destroy method, but we can clean up
                this.quill = null;
            }
        },
    };
};

// Alias for backward compatibility
window.quillEditor = window.setupEditor;
