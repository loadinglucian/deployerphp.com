@props([
    'toc',
    'currentPath',
])

<flux:navlist>
    <flux:navlist.group heading="Guides">
        @foreach ($toc as $section)
            @foreach ($section['links'] as $link)
                <flux:navlist.item href="{{ '' === $link['path'] ? route('docs.show') : route('docs.show', ['page' => $link['path']]) }}" wire:navigate :current="$link['path'] === $currentPath">
                    {{ $link['title'] }}
                </flux:navlist.item>
            @endforeach
        @endforeach
    </flux:navlist.group>
</flux:navlist>
