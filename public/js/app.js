(() => {
    document.addEventListener('submit', (event) => {
        const form = event.target.closest('form[data-confirm]');

        if (form && !window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });

    const navLinks = document.querySelectorAll('[data-public-nav]');
    const header = document.querySelector('.site-header');

    if (navLinks.length > 0 && header) {
        const currentPath = window.location.pathname.replace(/\/+$/, '') || '/';
        const currentPageLinks = [...navLinks].filter((link) => {
            const linkUrl = new URL(link.href, window.location.href);

            return (linkUrl.pathname.replace(/\/+$/, '') || '/') === currentPath;
        });
        const routeLink = currentPageLinks.find((link) => new URL(link.href, window.location.href).hash === '');
        const sections = currentPageLinks
            .map((link) => {
                const linkUrl = new URL(link.href, window.location.href);

                return {
                    name: link.dataset.publicNav,
                    section: linkUrl.hash ? document.getElementById(decodeURIComponent(linkUrl.hash.slice(1))) : null,
                };
            })
            .filter(({ section }) => section);
        let scheduled = false;

        const updateActiveLink = () => {
            const scrollPosition = window.scrollY;
            const headerBottom = header.getBoundingClientRect().bottom;
            let activeName = scrollPosition < 100
                ? routeLink?.dataset.publicNav ?? 'home'
                : sections[0]?.name ?? routeLink?.dataset.publicNav ?? 'about';

            if (scrollPosition >= 100) {
                for (const { name, section } of sections) {
                    if (section.getBoundingClientRect().top <= headerBottom + 100) {
                        activeName = name;
                    }
                }
            }

            for (const link of currentPageLinks) {
                const isActive = link.dataset.publicNav === activeName;
                link.classList.toggle('active', isActive);

                if (isActive) {
                    link.setAttribute('aria-current', 'location');
                } else {
                    link.removeAttribute('aria-current');
                }
            }
        };

        const scheduleUpdate = () => {
            if (scheduled) {
                return;
            }

            scheduled = true;
            window.requestAnimationFrame(() => {
                updateActiveLink();
                scheduled = false;
            });
        };

        window.addEventListener('scroll', scheduleUpdate, { passive: true });
        window.addEventListener('resize', scheduleUpdate);
        window.addEventListener('hashchange', scheduleUpdate);
        if (currentPageLinks.length > 0) {
            updateActiveLink();
        }
    }

    const navigationToggle = document.querySelector('.nav-menu-toggle');
    const publicNavigation = document.querySelector('#public-navigation');

    if (navigationToggle && publicNavigation) {
        document.body.classList.add('nav-ready');

        const closeNavigation = () => {
            publicNavigation.classList.remove('is-open');
            navigationToggle.setAttribute('aria-expanded', 'false');
            navigationToggle.setAttribute('aria-label', 'Open navigation menu');
        };

        navigationToggle.addEventListener('click', () => {
            const isOpen = publicNavigation.classList.toggle('is-open');
            navigationToggle.setAttribute('aria-expanded', String(isOpen));
            navigationToggle.setAttribute('aria-label', isOpen ? 'Close navigation menu' : 'Open navigation menu');
        });

        publicNavigation.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', closeNavigation);
        });

        document.addEventListener('click', (event) => {
            if (! publicNavigation.contains(event.target) && ! navigationToggle.contains(event.target)) {
                closeNavigation();
            }

            publicNavigation.querySelectorAll('.nav-account[open]').forEach((accountMenu) => {
                if (! accountMenu.contains(event.target)) {
                    accountMenu.removeAttribute('open');
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                const wasNavigationOpen = publicNavigation.classList.contains('is-open');
                closeNavigation();
                publicNavigation.querySelector('.nav-account[open]')?.removeAttribute('open');

                if (wasNavigationOpen) {
                    navigationToggle.focus();
                }
            }
        });
    }

    const revealItems = document.querySelectorAll('[data-scroll-reveal]');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (revealItems.length > 0 && 'IntersectionObserver' in window && !prefersReducedMotion) {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.12,
            rootMargin: '0px 0px -36px 0px',
        });

        document.documentElement.classList.add('has-scroll-reveal');
        revealItems.forEach((item) => revealObserver.observe(item));
    }

    document.querySelectorAll('[data-gallery-carousel]').forEach((carousel) => {
        const track = carousel.querySelector('[data-gallery-track]');
        const controls = carousel.querySelector('[data-gallery-controls]');
        const previousButton = carousel.querySelector('[data-gallery-previous]');
        const nextButton = carousel.querySelector('[data-gallery-next]');
        const counter = carousel.querySelector('[data-gallery-counter]');
        const progress = carousel.querySelector('[data-gallery-progress]');
        const progressBar = progress?.parentElement;
        const cards = [...(track?.querySelectorAll('.gallery-card') ?? [])];

        if (! track || ! controls || ! previousButton || ! nextButton || !counter || !progress || !progressBar || cards.length === 0) {
            return;
        }

        const updateControls = () => {
            const hasOverflow = track.scrollWidth > track.clientWidth + 1;
            const activeIndex = cards.reduce((closestIndex, card, index) => {
                const trackLeft = track.getBoundingClientRect().left;
                const currentDistance = Math.abs(cards[closestIndex].getBoundingClientRect().left - trackLeft);
                const nextDistance = Math.abs(card.getBoundingClientRect().left - trackLeft);

                return nextDistance < currentDistance ? index : closestIndex;
            }, 0);
            controls.hidden = ! hasOverflow;
            previousButton.disabled = ! hasOverflow || track.scrollLeft <= 1;
            nextButton.disabled = ! hasOverflow
                || track.scrollLeft + track.clientWidth >= track.scrollWidth - 1;
            counter.textContent = `${String(activeIndex + 1).padStart(2, '0')} / ${String(cards.length).padStart(2, '0')}`;
            progress.style.width = `${((activeIndex + 1) / cards.length) * 100}%`;
            progressBar.setAttribute('aria-valuenow', String(activeIndex + 1));
        };

        const scrollByCard = (direction) => {
            const firstCard = track.querySelector('.gallery-card');

            if (! firstCard) {
                return;
            }

            const gap = Number.parseFloat(window.getComputedStyle(track).columnGap) || 0;
            track.scrollBy({
                left: direction * (firstCard.getBoundingClientRect().width + gap),
                behavior: prefersReducedMotion ? 'auto' : 'smooth',
            });
        };

        previousButton.addEventListener('click', () => scrollByCard(-1));
        nextButton.addEventListener('click', () => scrollByCard(1));
        track.addEventListener('scroll', updateControls, { passive: true });
        window.addEventListener('resize', updateControls);
        updateControls();
    });

    if (! document.querySelector('.booking-page')) {
        return;
    }

    const serviceOptions = document.querySelectorAll('.booking-service-choice input');
    const dogOptions = document.querySelectorAll('.booking-dog-choice input');
    const dateInput = document.querySelector('#date');
    const timeOptions = document.querySelectorAll('.booking-time-choice input');
    const summaryService = document.querySelector('#summary-service');
    const summaryDog = document.querySelector('#summary-dog');
    const summaryDate = document.querySelector('#summary-date');
    const summaryTime = document.querySelector('#summary-time');
    const summaryPrice = document.querySelector('#summary-price');
    const confirmForm = document.querySelector('#booking-confirm');
    const confirmButton = confirmForm?.querySelector('button[type="submit"]');
    const summaryNote = document.querySelector('.booking-summary-note');
    const hasAvailableTimes = Boolean(confirmForm);

    const requireTimeRefresh = () => {
        if (! hasAvailableTimes) {
            return;
        }

        timeOptions.forEach((option) => {
            option.disabled = true;
        });
        confirmButton.disabled = true;
        confirmButton.textContent = 'Update selections above';
        summaryTime.textContent = 'Refresh times to continue';
        summaryNote.textContent = 'Click “Update available times” after changing your selections.';
    };

    serviceOptions.forEach((option) => {
        option.addEventListener('change', () => {
            summaryService.textContent = option.dataset.name;
            summaryPrice.textContent = option.dataset.price;
            requireTimeRefresh();
        });
    });

    dogOptions.forEach((option) => {
        option.addEventListener('change', () => {
            summaryDog.textContent = option.dataset.name;
            requireTimeRefresh();
        });
    });

    dateInput?.addEventListener('change', () => {
        requireTimeRefresh();

        if (! dateInput.value) {
            summaryDate.textContent = 'Choose a date';

            return;
        }

        summaryDate.textContent = new Intl.DateTimeFormat('en', {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
            timeZone: 'UTC',
        }).format(new Date(`${dateInput.value}T00:00:00Z`));
    });

    timeOptions.forEach((option) => {
        option.addEventListener('change', () => {
            summaryTime.textContent = `${option.dataset.label} – ${option.dataset.endLabel}`;
        });

        if (option.checked) {
            summaryTime.textContent = `${option.dataset.label} – ${option.dataset.endLabel}`;
        }
    });
})();
