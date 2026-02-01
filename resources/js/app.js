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

    // Pattern for colon-separated subcommands: server:add, site:deploy
    const COLON_SUBCOMMAND_MODE = {
        scope: 'params',
        match: /[\w-]+:[\w-]+/,
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

// Use livewire:navigated for SPA compatibility (fires on initial load AND after navigation)
document.addEventListener('livewire:navigated', () => {
    document.querySelectorAll('pre code:not(.hljs)').forEach((el) => {
        hljs.highlightElement(el);
    });
});

// ----
// Heading Scroll Spy
// ----

document.addEventListener('alpine:init', () => {
    Alpine.data('headingsSpy', (headingIds) => ({
        activeId: headingIds[0] ?? null,
        observer: null,
        initialized: false,

        init() {
            this.setupObserver();
        },

        setupObserver() {
            const headings = headingIds.map((id) => document.getElementById(id)).filter((el) => el !== null);

            if (0 === headings.length) {
                return;
            }

            // Read sticky header offset from CSS variable (matches scroll-margin-top on headings)
            // Create temp element to compute the CSS calc() value in pixels
            const temp = document.createElement('div');
            temp.style.position = 'absolute';
            temp.style.top = 'var(--docs-sticky-top, 100px)';
            document.body.appendChild(temp);
            const stickyTopPx = parseFloat(getComputedStyle(temp).top) || 100;
            temp.remove();

            // Trigger when heading crosses just below the sticky header
            const rootMargin = `-${stickyTopPx}px 0px -80% 0px`;

            this.observer = new IntersectionObserver(
                (entries) => {
                    for (const entry of entries) {
                        const idx = headingIds.indexOf(entry.target.id);

                        if (entry.isIntersecting) {
                            // Heading entered the detection zone
                            this.activeId = entry.target.id;
                            this.initialized = true;
                        } else if (this.initialized) {
                            // Only process exit events after first intersection
                            // (avoids activating wrong heading on initial page load)
                            if (entry.boundingClientRect.top < entry.rootBounds.top) {
                                // Heading exited through the TOP (scrolled down past it)
                                // Activate the NEXT heading (if exists)
                                if (idx < headingIds.length - 1) {
                                    this.activeId = headingIds[idx + 1];
                                }
                            } else {
                                // Heading exited through the BOTTOM (scrolled up to reveal it)
                                // Activate THIS heading (it's now the topmost)
                                this.activeId = entry.target.id;
                            }
                        }
                    }
                },
                { rootMargin, threshold: 0 },
            );

            headings.forEach((h) => this.observer.observe(h));
        },

        destroy() {
            this.observer?.disconnect();
            this.observer = null;
            this.initialized = false;
        },
    }));
});

// Reinitialize scroll spy after Livewire navigation
document.addEventListener('livewire:navigated', () => {
    // Dispatch event for Alpine components to reinitialize
    window.dispatchEvent(new CustomEvent('docs:navigated'));
});
