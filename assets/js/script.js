document.addEventListener('DOMContentLoaded', function () {

    const button = document.getElementById('mobileMenuBtn');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    console.log('Mobile menu JS berhasil dimuat');

    if (!button || !sidebar || !overlay) {
        console.log('Element mobile menu tidak ditemukan');
        return;
    }

    button.addEventListener('click', function () {

        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');

    });

    overlay.addEventListener('click', function () {

        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');

    });

});