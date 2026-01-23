@props([
    'toc',
    'currentPath',
])

<nav class="space-y-2">
    @foreach ($toc as $section)
        @php
            $sectionContainsActive = collect($section['links'])->contains(fn ($link) => $link['path'] === $currentPath);
        @endphp

        <div x-data="{ open: {{ $sectionContainsActive ? 'true' : 'false' }} }">
            {{-- Clickable section header --}}
            <button @click="open = !open" class="flex w-full cursor-pointer items-center justify-between px-1 py-2 text-left text-sm font-semibold text-synth-text transition-colors dark:text-synth-text-light">
                <span>{{ $section['name'] }}</span>
            </button>

            {{-- Collapsible links list --}}
            <ul x-show="open" x-collapse class="mt-1 ml-1.5">
                @foreach ($section['links'] as $link)
                    @php
                        $isActive = $link['path'] === $currentPath;
                    @endphp

                    <li>
                        <a href="{{ route('docs.show', ['section' => explode('/', $link['path'])[0], 'page' => explode('/', $link['path'])[1]]) }}" @class(['block px-3 py-1.5 text-sm transition-colors', 'border-l border-neon-cyan-dark ' => $isActive, 'border-l border-synth-border text-synth-text-muted hover:bg-synth-bg-alt dark:text-synth-text-light-muted dark:hover:bg-synth-dark-alt dark:hover:text-neon-cyan' => ! $isActive])>
                            {{ $link['title'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</nav>
