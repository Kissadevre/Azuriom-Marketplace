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
            plugins: 'autolink code image link lists table visualblocks wordcount',
            toolbar: 'undo redo | blocks | bold italic underline strikethrough | bullist numlist blockquote | link image table hr | removeformat visualblocks code',
            paste_data_images: false,
            automatic_uploads: false,
            relative_urls: false,
            convert_urls: false,
            valid_elements: 'p,br,h2,h3,h4,strong/b,em/i,u,s,blockquote,ul,ol,li,a[href|title|target],img[src|alt|title|width|height],table,thead,tbody,tr,th[colspan|rowspan],td[colspan|rowspan],pre,code,hr',
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
