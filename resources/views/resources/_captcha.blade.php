@php($captchaType = setting('captcha.type'))
@php($captchaEnabled = $captchaType && setting('captcha.site_key') && setting('captcha.secret_key'))

@if($captchaEnabled)
    @if($captchaType === 'recaptcha')
        @once
            @push('scripts')
                <script>
                    window.marketplaceRecaptchaReady = () => {
                        document.querySelectorAll('[data-marketplace-recaptcha]').forEach((element) => {
                            const form = document.getElementById(element.dataset.formId);

                            if (! form) {
                                return;
                            }

                            const widgetId = grecaptcha.render(element, {
                                sitekey: element.dataset.sitekey,
                                size: 'invisible',
                                callback: () => form.submit(),
                            });

                            form.addEventListener('submit', (event) => {
                                if (grecaptcha.getResponse(widgetId) === '') {
                                    event.preventDefault();
                                    grecaptcha.execute(widgetId);
                                }
                            });
                        });
                    };
                </script>
                <script src="https://www.recaptcha.net/recaptcha/api.js?onload=marketplaceRecaptchaReady&amp;render=explicit&amp;hl={{ app()->getLocale() }}" async defer></script>
            @endpush
        @endonce

        <div data-marketplace-recaptcha data-form-id="{{ $formId }}" data-sitekey="{{ setting('captcha.site_key') }}"></div>
    @elseif($captchaType === 'hcaptcha')
        @once
            @push('scripts')
                <script src="https://hcaptcha.com/1/api.js?hl={{ app()->getLocale() }}" async defer></script>
            @endpush
        @endonce

        <div class="h-captcha mb-3 text-center" data-sitekey="{{ setting('captcha.site_key') }}" data-theme="{{ dark_theme() ? 'dark' : 'light' }}"></div>
    @elseif($captchaType === 'turnstile')
        @once
            @push('scripts')
                <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
            @endpush
        @endonce

        <div class="cf-turnstile mb-3 text-center" data-sitekey="{{ setting('captcha.site_key') }}" data-theme="{{ dark_theme() ? 'dark' : 'light' }}"></div>
    @endif
@endif
