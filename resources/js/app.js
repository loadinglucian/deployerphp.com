import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

// ----
// Highlight.js - Syntax Highlighting
// ----

import hljs from 'highlight.js/lib/core';
import php from 'highlight.js/lib/languages/php';
import bash from 'highlight.js/lib/languages/bash';
import yaml from 'highlight.js/lib/languages/yaml';

hljs.registerLanguage('php', php);
hljs.registerLanguage('bash', bash);
hljs.registerLanguage('shell', bash);
hljs.registerLanguage('sh', bash);
hljs.registerLanguage('yaml', yaml);
hljs.registerLanguage('yml', yaml);

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('pre code').forEach((el) => {
        hljs.highlightElement(el);
    });
});

// ----
// Alpine.js
// ----

Alpine.plugin(collapse);

Alpine.store('darkMode', {
    on: localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches),

    toggle() {
        this.on = !this.on;
        localStorage.setItem('darkMode', this.on);
    },
});

window.Alpine = Alpine;
Alpine.start();
