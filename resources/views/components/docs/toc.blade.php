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
            <button @click="open = !open" class="flex w-full items-center justify-between rounded-md px-3 py-2 text-left text-sm font-semibold text-synth-text transition-colors hover:bg-synth-bg-alt dark:text-synth-text-light dark:hover:bg-synth-dark-alt">
                <span>{{ $section['name'] }}</span>
                {{-- Chevron indicator --}}
                <svg :class="{ 'rotate-90': open }" class="size-4 text-synth-text-muted transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            {{-- Collapsible links list --}}
            <ul x-show="open" x-collapse class="mt-1 space-y-1">
                @foreach ($section['links'] as $link)
                    @php
                        $isActive = $link['path'] === $currentPath;
                    @endphp

                    <li>
                        <a
                            href="{{ route('docs.show', ['section' => explode('/', $link['path'])[0], 'page' => explode('/', $link['path'])[1]]) }}"
                            @class(['block rounded-md px-3 py-1.5 text-sm transition-colors', 'bg-neon-cyan/10 font-medium text-neon-cyan-dark dark:bg-neon-cyan/15 dark:text-neon-cyan' => $isActive, 'text-synth-text-muted hover:bg-synth-bg-alt hover:text-neon-cyan dark:text-synth-text-light-muted dark:hover:bg-synth-dark-alt dark:hover:text-neon-cyan' => ! $isActive])
                        >
                            {{ $link['title'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</nav>
