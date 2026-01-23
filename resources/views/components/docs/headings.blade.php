@props(['headings'])

@if (count($headings) > 1)
    <nav>
        <h4 class="mb-4 text-sm font-semibold text-slate-900 dark:text-white">
            On this page
        </h4>
        <ul class="space-y-2 text-sm">
            @foreach ($headings as $heading)
                @if ($heading['level'] <= 3 && $heading['id'])
                    <li @class(['pl-3' => $heading['level'] === 2, 'pl-6' => $heading['level'] === 3])>
                        <a
                            href="#{{ $heading['id'] }}"
                            class="block text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white transition-colors"
                        >
                            {{ $heading['text'] }}
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
    </nav>
@endif
