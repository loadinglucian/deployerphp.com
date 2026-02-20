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
        </flux:navlist.group>
    @endforeach

    <flux:navlist.group heading="Index">
        <flux:navlist.item href="{{ route('command-index') }}" wire:navigate :current="$currentPath === 'command-index'">Command Index</flux:navlist.item>
    </flux:navlist.group>
</flux:navlist>
