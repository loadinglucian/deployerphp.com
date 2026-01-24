@props([
    'headings',
])

@if (count($headings) > 1)
    <nav>
        <h4 class="mb-4 text-sm font-normal text-zinc-900 dark:text-zinc-100">On this page</h4>
        <ul class="space-y-2 text-sm">
            @foreach ($headings as $heading)
                @if ($heading['level'] <= 3 && $heading['id'])
                    <li @class(['pl-3' => $heading['level'] === 2, 'pl-6' => $heading['level'] === 3])>
                        <a href="#{{ $heading['id'] }}" class="block text-zinc-500 transition-colors hover:text-accent dark:text-zinc-400 dark:hover:text-accent">
                            {{ $heading['text'] }}
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
    </nav>
@endif
