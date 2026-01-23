@props([
    'headings',
])

@if (count($headings) > 1)
    <nav>
        <h4 class="mb-4 text-sm font-semibold text-synth-text dark:text-synth-text-light">On this page</h4>
        <ul class="space-y-2 text-sm">
            @foreach ($headings as $heading)
                @if ($heading['level'] <= 3 && $heading['id'])
                    <li @class(['pl-3' => $heading['level'] === 2, 'pl-6' => $heading['level'] === 3])>
                        <a href="#{{ $heading['id'] }}" class="block text-synth-text-muted transition-colors hover:text-neon-cyan dark:text-synth-text-light-muted dark:hover:text-neon-cyan">
                            {{ $heading['text'] }}
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
    </nav>
@endif
