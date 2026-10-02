/**
 * Cattalya — front-end behaviours.
 * Vanilla JS, no dependencies. Everything degrades gracefully without it.
 */

/* ------------------------------------------------------------------
 * Toast notifications
 * ------------------------------------------------------------------ */
const TOAST_STYLES = {
    success: {
        wrap: 'bg-white border-green-200',
        bar: 'bg-green-500',
        icon: 'text-green-600 bg-green-50',
        path: 'M5 13l4 4L19 7',
    },
    error: {
        wrap: 'bg-white border-red-200',
        bar: 'bg-red-500',
        icon: 'text-red-600 bg-red-50',
        path: 'M6 18L18 6M6 6l12 12',
    },
    warning: {
        wrap: 'bg-white border-amber-200',
        bar: 'bg-amber-500',
        icon: 'text-amber-600 bg-amber-50',
        path: 'M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z',
    },
    info: {
        wrap: 'bg-white border-[#e3d2a0]',
        bar: 'bg-[#8a6300]',
        icon: 'text-[#8a6300] bg-[#fdeee0]',
        path: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    },
};

function toastContainer() {
    let el = document.getElementById('toast-container');
    if (!el) {
        el = document.createElement('div');
        el.id = 'toast-container';
        el.className = 'fixed top-4 left-1/2 -translate-x-1/2 z-[100] flex flex-col items-center gap-2 w-[calc(100%-2rem)] max-w-sm pointer-events-none';
        el.setAttribute('role', 'status');
        el.setAttribute('aria-live', 'polite');
        document.body.appendChild(el);
    }
    return el;
}

function dismissToast(toast) {
    if (toast.dataset.leaving) return;
    toast.dataset.leaving = '1';
    toast.classList.add('toast-leaving');
    toast.addEventListener('animationend', () => toast.remove(), { once: true });
}

/**
 * Show a toast. Available globally as window.notify().
 * @param {string} message
 * @param {'success'|'error'|'warning'|'info'} type
 * @param {number} duration ms; 0 keeps it until dismissed
 */
function notify(message, type = 'success', duration = 4500) {
    if (!message) return;
    const style = TOAST_STYLES[type] ?? TOAST_STYLES.info;

    const toast = document.createElement('div');
    toast.className =
        `pointer-events-auto w-full ${style.wrap} border rounded-2xl shadow-lg overflow-hidden animate-toast-in`;
    toast.innerHTML = `
        <div class="flex items-start gap-3 px-4 py-3">
            <div class="w-8 h-8 shrink-0 rounded-full flex items-center justify-center ${style.icon}">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${style.path}"/>
                </svg>
            </div>
            <p class="flex-1 text-sm text-right leading-6 text-[#2b2620]"></p>
            <button type="button" aria-label="بستن" class="shrink-0 text-[#a8a096] hover:text-[#2b2620] transition-colors mt-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        ${duration ? `<div class="h-0.5 ${style.bar} toast-progress" style="animation-duration:${duration}ms"></div>` : ''}
    `;
    toast.querySelector('p').textContent = message;
    toast.querySelector('button').addEventListener('click', () => dismissToast(toast));

    toastContainer().appendChild(toast);

    if (duration) {
        let timer = setTimeout(() => dismissToast(toast), duration);
        toast.addEventListener('mouseenter', () => {
            clearTimeout(timer);
            toast.querySelector('.toast-progress')?.style.setProperty('animation-play-state', 'paused');
        });
        toast.addEventListener('mouseleave', () => {
            timer = setTimeout(() => dismissToast(toast), 1500);
            toast.querySelector('.toast-progress')?.style.setProperty('animation-play-state', 'running');
        });
    }
    return toast;
}

window.notify = notify;

/* Flash messages rendered server-side into <script type="application/json" id="flash-messages"> */
function flushServerFlashes() {
    const node = document.getElementById('flash-messages');
    if (!node) return;
    let messages = [];
    try {
        messages = JSON.parse(node.textContent || '[]');
    } catch {
        return;
    }
    messages.forEach((m, i) => setTimeout(() => notify(m.message, m.type), i * 180));
}

/* ------------------------------------------------------------------
 * Scroll reveal
 * ------------------------------------------------------------------ */
function initReveal() {
    const items = document.querySelectorAll('[data-reveal]');
    if (!items.length) return;

    if (!('IntersectionObserver' in window)) {
        items.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    let observerFired = false;

    const observer = new IntersectionObserver(
        (entries) => {
            observerFired = true;
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.05 }
    );

    // Failsafe: if the observer never reports (page never composited, odd
    // embedding), show everything rather than leaving a blank page.
    setTimeout(() => {
        if (observerFired) return;
        observer.disconnect();
        items.forEach((el) => el.classList.add('is-visible'));
    }, 1500);

    items.forEach((el) => {
        // Stagger siblings that opt in via data-reveal-stagger on the parent.
        const parent = el.parentElement;
        if (parent?.hasAttribute('data-reveal-stagger')) {
            const index = [...parent.children].indexOf(el);
            const step = Number(parent.dataset.revealStagger) || 60;
            el.style.setProperty('--reveal-delay', `${Math.min(index, 8) * step}ms`);
        }
        observer.observe(el);
    });
}

/* ------------------------------------------------------------------
 * Form submit → button busy state
 * ------------------------------------------------------------------ */
function initFormLoading() {
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement) || form.dataset.noLoading !== undefined) return;

        const button = form.querySelector('button[type="submit"]:not([data-no-loading])')
            || form.querySelector('button:not([type="button"]):not([data-no-loading])');
        if (!button || button.classList.contains('is-loading')) return;

        // Only spin on chunky buttons — icon buttons look broken with a spinner.
        if (button.offsetWidth < 64) return;

        button.dataset.originalWidth = `${button.offsetWidth}px`;
        button.style.width = button.dataset.originalWidth;
        button.classList.add('is-loading');
        button.setAttribute('aria-busy', 'true');
    });
}

/* ------------------------------------------------------------------
 * Header: shadow on scroll + cart badge bump
 * ------------------------------------------------------------------ */
function initHeader() {
    const header = document.getElementById('site-header');
    if (header) {
        const onScroll = () => {
            header.classList.toggle('shadow-md', window.scrollY > 8);
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    // Bump the cart count once per page load when it just changed.
    const badge = document.getElementById('cart-count');
    if (badge) {
        const count = badge.dataset.count;
        if (sessionStorage.getItem('cart-count') !== count) {
            sessionStorage.setItem('cart-count', count);
            badge.classList.add('animate-bump');
        }
    }
}

/* ------------------------------------------------------------------
 * Back-to-top button
 * ------------------------------------------------------------------ */
function initBackToTop() {
    const btn = document.getElementById('back-to-top');
    if (!btn) return;

    const onScroll = () => {
        const show = window.scrollY > 600;
        btn.classList.toggle('opacity-0', !show);
        btn.classList.toggle('translate-y-3', !show);
        btn.classList.toggle('pointer-events-none', !show);
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
    btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}

/* ------------------------------------------------------------------
 * Boot
 * ------------------------------------------------------------------ */
function boot() {
    document.documentElement.classList.remove('no-js');
    flushServerFlashes();
    initReveal();
    initFormLoading();
    initHeader();
    initBackToTop();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}

// Restoring from bfcache (back button) leaves buttons stuck in the busy state.
window.addEventListener('pageshow', (e) => {
    if (!e.persisted) return;
    document.querySelectorAll('.is-loading').forEach((btn) => {
        btn.classList.remove('is-loading');
        btn.removeAttribute('aria-busy');
        btn.style.width = '';
    });
});
