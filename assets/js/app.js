(function () {
    'use strict';

    async function copyText(text) {
        if (navigator.clipboard && window.isSecureContext) {
            try {
                await navigator.clipboard.writeText(text);
                return true;
            } catch (error) {
                // Переходим к резервному способу.
            }
        }

        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'fixed';
        textarea.style.top = '-1000px';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        textarea.setSelectionRange(0, textarea.value.length);

        let copied = false;
        try {
            copied = document.execCommand('copy');
        } catch (error) {
            copied = false;
        }

        document.body.removeChild(textarea);
        return copied;
    }

    window.StroyCalc = window.StroyCalc || {};
    window.StroyCalc.copyText = copyText;

    const header = document.querySelector('[data-header]');
    const nav = document.querySelector('[data-nav]');
    const toggle = document.querySelector('[data-nav-toggle]');

    function closeNav() {
        if (!nav || !toggle) {
            return;
        }
        nav.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('nav-open');
    }

    if (nav && toggle) {
        toggle.addEventListener('click', function () {
            const isOpen = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            document.body.classList.toggle('nav-open', isOpen);
        });

        nav.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', closeNav);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeNav();
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 900) {
                closeNav();
            }
        });
    }

    if (header) {
        const onScroll = function () {
            header.classList.toggle('is-scrolled', window.scrollY > 8);
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    document.querySelectorAll('[data-copy-link]').forEach(function (button) {
        button.addEventListener('click', async function () {
            const url = button.getAttribute('data-copy-link');
            if (!url) {
                return;
            }

            const original = button.innerHTML;
            const copied = await copyText(url);

            button.textContent = copied ? 'Ссылка скопирована' : 'Не удалось скопировать';
            window.setTimeout(function () {
                button.innerHTML = original;
            }, 2200);
        });
    });
})();
