@props([
    'headings',
])

@if (count($headings) > 1)
    <flux:navlist>
        <flux:navlist.group heading="On this page">
        @foreach ($headings as $heading)
            @if ($heading['level'] <= 3 && $heading['id'])
                <flux:navlist.item href="{{ '#' . $heading['id'] }}" @class(['pl-3' => $heading['level'] === 2, 'pl-6' => $heading['level'] === 3])>
                        {{ $heading['text'] }}
                </flux:navlist.item>
            @endif
        @endforeach
        </flux:navlist.group>
    </flux:navlist>
@endif
