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
            <button @click="open = !open" class="flex w-full items-center justify-between rounded-md px-3 py-2 text-left text-sm font-semibold text-slate-900 transition-colors hover:bg-slate-50 dark:text-white dark:hover:bg-slate-800">
                <span>{{ $section['name'] }}</span>
                {{-- Chevron indicator --}}
                <svg :class="{ 'rotate-90': open }" class="size-4 text-slate-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                            @class(['block rounded-md px-3 py-1.5 text-sm transition-colors', 'bg-slate-100 font-medium text-slate-900 dark:bg-slate-800 dark:text-white' => $isActive, 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white' => ! $isActive])
                        >
                            {{ $link['title'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</nav>
