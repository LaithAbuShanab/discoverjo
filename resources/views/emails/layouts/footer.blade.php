<!-- Footer -->
<div class="email-footer">
    <a href="https://instagram.com" target="_blank">
        <img src="{{ url('assets/images/instagram_icon.png') }}" alt="Instagram">

    </a>
    <a href="https://facebook.com" target="_blank">
        <img src="{{ url('assets/images/facebook_icon.png') }}" alt="Facebook">
    </a>
    <a href="https://linkedin.com" target="_blank">
        <img src="{{ url('assets/images/linkedin_icon.png') }}" alt="LinkedIn">
    </a>
    <p>{{ __('app.allReserved', [], $user->lang ?? app()->getLocale()) }} ©{{ date('Y') }} .</p>
</div>
