@push('footer-scripts')
<script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        tinymce.init({
            selector: '#descriptionInput',
            license_key: 'gpl',
            promotion: false,
            branding: false,
            height: 480,
            min_height: 300,
            menubar: false,
            entity_encoding: 'raw',
            verify_html: true,
            plugins: 'autolink code image link lists table visualblocks wordcount',
            toolbar: 'undo redo | blocks | bold italic underline strikethrough | bullist numlist blockquote | link image table hr | removeformat visualblocks code',
            paste_data_images: true,
            automatic_uploads: true,
            images_file_types: 'jpg,jpeg,png,webp',
            images_upload_handler: (blobInfo) => new Promise((resolve, reject) => {
                const body = new FormData();
                body.append('image', blobInfo.blob(), blobInfo.filename());
                body.append('draft_token', document.getElementById('editorUploadToken').value);

                fetch(@json(route('marketplace.editor-images.store')), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token()),
                    },
                    body,
                }).then(async (response) => {
                    const data = await response.json().catch(() => ({}));
                    if (! response.ok || ! data.location) {
                        const validationError = data.errors
                            ? Object.values(data.errors).flat()[0]
                            : null;
                        throw new Error(validationError || data.message || @json(trans('marketplace::messages.editor_images.upload_failed')));
                    }

                    resolve(data.location);
                }).catch((error) => reject(error.message));
            }),
            relative_urls: false,
            convert_urls: false,
            valid_elements: 'p,br,h2,h3,h4,strong/b,em/i,u,s,blockquote,ul,ol,li,a[href|title|target],img[src|alt|title|width|height],table,thead,tbody,tr,th[colspan|rowspan],td[colspan|rowspan],pre,code,hr',
            invalid_elements: 'script,style,iframe,object,embed,svg,math,form,input,button,textarea,select,option,template,noscript,xmp,plaintext,listing,frame,frameset,applet,audio,video,source,track,link,meta,base',
            allow_unsafe_link_target: false,
            link_default_target: '_blank',
            link_assume_external_targets: 'https',
            content_style: 'body { font-family: system-ui, sans-serif; font-size: 16px; } img { max-width: 100%; height: auto; }',
            @if(dark_theme())
            skin: 'oxide-dark',
            content_css: 'dark',
            @endif
        });
    });
</script>
@endpush
