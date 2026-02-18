@props([
    'toc',
    'currentPath',
])

<flux:navlist class="gap-6">
    @foreach ($toc as $key => $section)
        <flux:navlist.group heading="{{ $section['name'] }}">
            @foreach ($section['links'] as $link)
                <flux:navlist.item href="{{ '' === $link['path'] ? route('home') : route('docs.show', ['page' => $link['path']]) }}" wire:navigate :current="$link['path'] === $currentPath">
                    {{ $link['title'] }}
                </flux:navlist.item>
            @endforeach

            @if ($loop->last)
                <flux:navlist.item href="{{ route('cheat-sheet') }}" target="_blank">
                    <div class="flex items-center gap-2">
                        <span>Cheat Sheet</span>

                        <flux:icon.square-arrow-out-up-right class="size-3" />
                    </div>
                </flux:navlist.item>
            @endif
        </flux:navlist.group>
    @endforeach
</flux:navlist>
