@props([
    'toc',
    'currentPath',
])

<nav class="">
    @foreach ($toc as $section)
        @php
            $sectionContainsActive = collect($section['links'])->contains(fn ($link) => $link['path'] === $currentPath);
        @endphp

        <div x-data="{ open: {{ $sectionContainsActive ? 'true' : 'false' }} }">
            {{-- Clickable section header --}}
            <button @click="open = !open" class="flex w-full cursor-pointer items-center justify-between px-0.5 py-2 text-left text-sm font-medium text-zinc-900 transition-colors dark:text-zinc-100">
                <span>{{ $section['name'] }}</span>
            </button>

            {{-- Collapsible links list --}}
            <ul x-show="open" x-collapse class="mt-1 ml-1">
                @foreach ($section['links'] as $link)
                    @php
                        [$section, $page] = explode('/', $link['path']);
                        $isActive = $link['path'] === $currentPath;
                    @endphp

                    <li>
                        <a href="{{ route('docs.show', ['section' => $section, 'page' => $page]) }}" @class(['block px-3 py-1.5 text-sm transition-colors', 'border-l-2 border-accent text-zinc-900 dark:text-white' => $isActive, 'border-l border-zinc-200 text-zinc-500 hover:border-zinc-300 hover:text-zinc-900 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-white' => ! $isActive])>
                            {{ $link['title'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</nav>
