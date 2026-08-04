// ATMABISWAS — Bangla / English toggle
// Elements opt in with data-en="English text" data-bn="বাংলা text".
// The chosen language is remembered (localStorage) across pages, but only
// pages/includes that carry data-en/data-bn attributes actually translate.
(function () {
    var LANG_KEY = 'atma_lang';

    function getLang() {
        return localStorage.getItem(LANG_KEY) === 'bn' ? 'bn' : 'en';
    }

    function applyLang(lang) {
        document.querySelectorAll('[data-en]').forEach(function (el) {
            var text = lang === 'bn' ? (el.getAttribute('data-bn') || el.getAttribute('data-en')) : el.getAttribute('data-en');
            el.textContent = text;
        });

        document.querySelectorAll('[data-en-placeholder]').forEach(function (el) {
            var text = lang === 'bn'
                ? (el.getAttribute('data-bn-placeholder') || el.getAttribute('data-en-placeholder'))
                : el.getAttribute('data-en-placeholder');
            el.setAttribute('placeholder', text);
        });

        document.documentElement.setAttribute('data-lang', lang);

        document.querySelectorAll('.lang-toggle-btn').forEach(function (btn) {
            btn.textContent = lang === 'bn' ? 'English' : 'বাংলা';
            btn.setAttribute('aria-label', lang === 'bn' ? 'Switch to English' : 'Switch to Bangla');
        });

        window.dispatchEvent(new CustomEvent('atma:langchange', { detail: { lang: lang } }));
    }

    function toggleLang() {
        var next = getLang() === 'bn' ? 'en' : 'bn';
        localStorage.setItem(LANG_KEY, next);
        applyLang(next);
    }

    window.atmaLang = { get: getLang, apply: applyLang, toggle: toggleLang };

    document.addEventListener('DOMContentLoaded', function () {
        applyLang(getLang());
        document.querySelectorAll('.lang-toggle-btn').forEach(function (btn) {
            btn.addEventListener('click', toggleLang);
        });
    });
}());
