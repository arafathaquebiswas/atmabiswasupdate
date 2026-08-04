/* ATMABISWAS — Premium Error Pages (error.js) — vanilla JS, no dependencies */

document.addEventListener('DOMContentLoaded', function () {
    initQuote();
    initParticles();
    initParallax();
    initCountdown();
    initSearch();
});

var ERR_QUOTES = [
    "Every setback is a setup for a comeback.",
    "Even the best paths have a wrong turn now and then.",
    "Hope is the thing that helps us find our way back.",
    "A small detour doesn't change where you're headed.",
    "We're here to help — let's get you back on track.",
    "Good things take a moment to find. Thanks for your patience.",
    "Every problem has a door out — let's find yours."
];

function initQuote() {
    var el = document.getElementById('errQuote');
    if (!el) return;
    el.textContent = ERR_QUOTES[Math.floor(Math.random() * ERR_QUOTES.length)];
}

function prefersReducedMotion() {
    return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function initParticles() {
    var host = document.querySelector('.error-page');
    if (!host || prefersReducedMotion()) return;

    var count = window.innerWidth < 640 ? 10 : 18;
    for (var i = 0; i < count; i++) {
        var p = document.createElement('span');
        p.className = 'err-particle';
        p.style.left = (Math.random() * 100) + '%';
        p.style.setProperty('--drift', (Math.random() * 60 - 30) + 'px');
        p.style.animationDuration = (8 + Math.random() * 10) + 's';
        p.style.animationDelay = (Math.random() * 10) + 's';
        host.appendChild(p);
    }
}

function initParallax() {
    var page = document.querySelector('.error-page');
    var badge = document.querySelector('.err-icon-badge');
    if (!page || !badge || prefersReducedMotion()) return;

    page.addEventListener('mousemove', function (e) {
        var rect = page.getBoundingClientRect();
        var x = (e.clientX - rect.left) / rect.width - 0.5;
        var y = (e.clientY - rect.top) / rect.height - 0.5;
        badge.style.transform = 'translate(' + (x * 12) + 'px,' + (y * 12) + 'px)';
    });

    page.addEventListener('mouseleave', function () {
        badge.style.transform = '';
    });
}

function initCountdown() {
    var el = document.querySelector('[data-countdown]');
    if (!el) return;

    var seconds = parseInt(el.getAttribute('data-countdown'), 10) || 30;
    var url = el.getAttribute('data-redirect-url') || '/';
    var numEl = el.querySelector('.err-countdown-num');
    var cancelBtn = el.querySelector('.err-cancel');
    var cancelled = false;

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            cancelled = true;
            el.textContent = 'Auto-redirect cancelled.';
        });
    }

    var timer = setInterval(function () {
        if (cancelled) { clearInterval(timer); return; }
        seconds--;
        if (numEl) numEl.textContent = seconds;
        if (seconds <= 0) {
            clearInterval(timer);
            window.location.href = url;
        }
    }, 1000);
}

function initSearch() {
    var form = document.querySelector('.err-search');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var input = form.querySelector('input');
        var q = input ? input.value.trim() : '';
        if (!q) { if (input) input.focus(); return; }
        window.location.href = 'https://www.google.com/search?q=' +
            encodeURIComponent('site:atmabiswas.org ' + q);
    });
}
