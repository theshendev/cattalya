<script>
document.querySelectorAll('[data-toggle-password]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = button.parentElement.querySelector('input');
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        button.querySelector('[data-password-hidden]').classList.toggle('hidden', isHidden);
        button.querySelector('[data-password-visible]').classList.toggle('hidden', !isHidden);
        button.setAttribute('aria-label', isHidden ? 'پنهان کردن رمز عبور' : 'نمایش رمز عبور');
    });
});
</script>
