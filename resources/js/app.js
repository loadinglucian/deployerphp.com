import './bootstrap';

// ----
// Highlight.js - Syntax Highlighting
// ----

import hljs from 'highlight.js/lib/core';
import php from 'highlight.js/lib/languages/php';
import bash from 'highlight.js/lib/languages/bash';
import yaml from 'highlight.js/lib/languages/yaml';

// Extend bash with common CLI commands and parameter highlighting
const extendedBash = (hljs) => {
    const lang = bash(hljs);

    // Additional CLI commands to highlight as built-ins
    const cliCommands = [
        // Package managers
        'composer',
        'npm',
        'yarn',
        'pnpm',
        'bun',
        'pip',
        'gem',
        'cargo',
        // PHP tools
        'php',
        'artisan',
        'phpunit',
        'pest',
        'phpstan',
        'pint',
        // DeployerPHP
        'deployer',
        'dep',
        // Version control
        'git',
        'gh',
        // Containers
        'docker',
        'docker-compose',
        // Other common tools
        'node',
        'python',
        'ruby',
        'curl',
        'wget',
        'ssh',
        'scp',
        'rsync',
    ];

    // Common subcommands to highlight
    const subcommands = [
        // Composer
        'require',
        'install',
        'update',
        'remove',
        'dump-autoload',
        // npm/yarn/bun
        'add',
        'run',
        'build',
        'dev',
        'init',
        // Git
        'commit',
        'push',
        'pull',
        'clone',
        'checkout',
        'merge',
        'rebase',
        'stash',
        'fetch',
        'status',
        'diff',
        'log',
        'branch',
        'remote',
        // Docker
        'up',
        'down',
        'exec',
        'logs',
        'ps',
        // Artisan/general
        'make',
        'migrate',
        'serve',
        'test',
        'tinker',
        'queue',
        'schedule',
        'config',
        'cache',
        'route',
        'view',
        'storage',
        'key',
        'db',
        'env',
    ];

    // Append to existing built_in keywords
    lang.keywords.built_in = [...lang.keywords.built_in, ...cliCommands];
    lang.keywords.params = subcommands;

    // Pattern for flags: --flag, -f, --long-flag (preceded by whitespace)
    const FLAG_MODE = {
        scope: 'params',
        match: /\s--?[\w][\w-]*/,
    };

    // Pattern for colon-separated subcommands: server:add, site:dns:check
    const COLON_SUBCOMMAND_MODE = {
        scope: 'params',
        match: /[\w-]+(?::[\w-]+)+/,
    };

    // Add custom modes to the language's contains array (before existing patterns)
    lang.contains = [FLAG_MODE, COLON_SUBCOMMAND_MODE, ...lang.contains];

    return lang;
};

hljs.registerLanguage('php', php);
hljs.registerLanguage('bash', extendedBash);
hljs.registerLanguage('shell', extendedBash);
hljs.registerLanguage('sh', extendedBash);
hljs.registerLanguage('yaml', yaml);
hljs.registerLanguage('yml', yaml);

const DOCS_IMAGE_SELECTOR = '.docs-prose img';
const DOCS_IMAGE_TRIGGER_CLASS = 'docs-image-lightbox-trigger';

let docsImageLightbox = null;
let docsImageLightboxActiveElement = null;
let docsImageLightboxShouldRestoreFocus = false;

const closeDocsImageLightbox = ({ skipFocusRestore = false } = {}) => {
    if (!docsImageLightbox) {
        return;
    }

    docsImageLightbox.remove();
    docsImageLightbox = null;
    document.body.classList.remove('docs-image-lightbox-open');

    if (!skipFocusRestore && docsImageLightboxShouldRestoreFocus && docsImageLightboxActiveElement instanceof HTMLElement) {
        docsImageLightboxActiveElement.focus();
    }
    docsImageLightboxActiveElement = null;
    docsImageLightboxShouldRestoreFocus = false;
};

const openDocsImageLightbox = (sourceImage, { restoreFocus = false } = {}) => {
    closeDocsImageLightbox({ skipFocusRestore: true });

    docsImageLightboxActiveElement = sourceImage;
    docsImageLightboxShouldRestoreFocus = restoreFocus;

    const overlay = document.createElement('div');
    overlay.className = 'docs-image-lightbox';
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-modal', 'true');
    overlay.setAttribute('aria-label', 'Expanded documentation image');

    const frame = document.createElement('figure');
    frame.className = 'docs-image-lightbox__frame';

    const image = document.createElement('img');
    image.className = 'docs-image-lightbox__image';
    image.src = sourceImage.currentSrc || sourceImage.src;
    image.alt = sourceImage.alt || '';
    frame.append(image);

    if (sourceImage.alt?.trim()) {
        const caption = document.createElement('figcaption');
        caption.className = 'docs-image-lightbox__caption';
        caption.textContent = sourceImage.alt.trim();
        frame.append(caption);
    }

    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'docs-image-lightbox__close';
    closeButton.setAttribute('aria-label', 'Close image preview');
    closeButton.textContent = 'Close';

    overlay.append(frame, closeButton);
    overlay.addEventListener('click', (event) => {
        if (event.target === overlay || event.target === closeButton || event.target === image) {
            closeDocsImageLightbox();
        }
    });

    document.body.append(overlay);
    document.body.classList.add('docs-image-lightbox-open');
    closeButton.focus();

    docsImageLightbox = overlay;
};

const isDocsImage = (element) => element instanceof HTMLImageElement && element.matches(DOCS_IMAGE_SELECTOR);

const prepareDocsLightboxImages = () => {
    document.querySelectorAll(DOCS_IMAGE_SELECTOR).forEach((image) => {
        image.classList.add(DOCS_IMAGE_TRIGGER_CLASS);
        image.setAttribute('title', image.getAttribute('title') || 'Click to expand');

        if (!image.closest('a') && !image.hasAttribute('tabindex')) {
            image.tabIndex = 0;
        }
    });
};

document.addEventListener('click', (event) => {
    const image = event.target instanceof HTMLImageElement ? event.target : null;
    if (!isDocsImage(image)) {
        return;
    }

    event.preventDefault();
    openDocsImageLightbox(image, { restoreFocus: false });
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && docsImageLightbox) {
        event.preventDefault();
        closeDocsImageLightbox();
        return;
    }

    if (!['Enter', ' '].includes(event.key)) {
        return;
    }

    const activeElement = document.activeElement;
    if (!isDocsImage(activeElement)) {
        return;
    }

    event.preventDefault();
    openDocsImageLightbox(activeElement, { restoreFocus: true });
});

// ----
// Scroll Spy - Right Sidebar Progress Highlighting
// ----

document.addEventListener('alpine:init', () => {
    Alpine.data('docsScrollSpy', () => ({
        activeIndex: -1,
        headingIds: [],
        observer: null,
        scrollListener: null,

        init() {
            this.setup();

            // Re-initialize after Livewire SPA navigation (new page content)
            document.addEventListener('livewire:navigated', () => {
                this.teardown();

                this.$nextTick(() => this.setup());
            });
        },

        setup() {
            // Collect heading IDs from sidebar anchor links (Flux renders items as <a> tags directly)
            const links = this.$root.querySelectorAll('[data-flux-navlist-item][href^="#"]');
            this.headingIds = Array.from(links).map((link) => link.getAttribute('href').slice(1));

            if (this.headingIds.length === 0) {
                return;
            }

            // Track which headings have crossed into the top observation zone
            const visible = new Set();

            this.observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            visible.add(entry.target.id);
                        } else {
                            visible.delete(entry.target.id);
                        }
                    });

                    this.updateActiveFromVisible(visible);
                },
                {
                    // Top zone: below sticky header (~80px), observe top ~30% of viewport
                    rootMargin: '-80px 0px -66% 0px',
                },
            );

            this.headingIds.forEach((id) => {
                const el = document.getElementById(id);
                if (el) {
                    this.observer.observe(el);
                }
            });

            // Handle bottom-of-page: activate last heading when scrolled to end
            this.scrollListener = () => {
                const nearBottom = window.innerHeight + window.scrollY >= document.body.offsetHeight - 40;

                if (nearBottom && this.headingIds.length > 0) {
                    this.activeIndex = this.headingIds.length - 1;
                }
            };
            window.addEventListener('scroll', this.scrollListener, { passive: true });

            // Set initial state for headings already in view
            this.$nextTick(() => {
                const initialVisible = new Set();

                this.headingIds.forEach((id) => {
                    const el = document.getElementById(id);
                    if (!el) {
                        return;
                    }

                    const rect = el.getBoundingClientRect();
                    if (rect.top < window.innerHeight * 0.34) {
                        initialVisible.add(id);
                    }
                });

                this.updateActiveFromVisible(initialVisible);
            });
        },

        updateActiveFromVisible(visible) {
            // Find the last heading (by document order) that's in the observation zone
            let lastVisibleIndex = -1;

            for (let i = this.headingIds.length - 1; i >= 0; i--) {
                if (visible.has(this.headingIds[i])) {
                    lastVisibleIndex = i;
                    break;
                }
            }

            if (lastVisibleIndex !== -1) {
                this.activeIndex = lastVisibleIndex;
            } else if (visible.size === 0) {
                // Nothing visible in zone — find the last heading above the viewport top
                for (let i = this.headingIds.length - 1; i >= 0; i--) {
                    const el = document.getElementById(this.headingIds[i]);
                    if (el && el.getBoundingClientRect().top < 100) {
                        this.activeIndex = i;
                        return;
                    }
                }

                // Scrolled to very top — no heading active
                this.activeIndex = -1;
            }
        },

        teardown() {
            if (this.observer) {
                this.observer.disconnect();
                this.observer = null;
            }

            if (this.scrollListener) {
                window.removeEventListener('scroll', this.scrollListener);
                this.scrollListener = null;
            }

            this.activeIndex = -1;
            this.headingIds = [];
        },

        destroy() {
            this.teardown();
        },
    }));
});

// Use livewire:navigated for SPA compatibility (fires on initial load AND after navigation)
document.addEventListener('livewire:navigated', () => {
    prepareDocsLightboxImages();

    document.querySelectorAll('pre code:not(.hljs)').forEach((el) => {
        hljs.highlightElement(el);
    });
});
