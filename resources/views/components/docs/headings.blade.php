@props([
    'headings',
])

@if (count($headings) > 1)
    <div x-data="headingsSpy(@js(collect($headings)->pluck('id')->filter()->values()->all()))" @docs:navigated.window="destroy(); $nextTick(() => { activeId = @js(collect($headings)->pluck('id')->filter()->first()); setupObserver(); })">
        <flux:navlist>
            <flux:navlist.group heading="On this page">
                @foreach ($headings as $heading)
                    @if ($heading['level'] <= 3 && $heading['id'])
                        <flux:navlist.item href="{{ '#' . $heading['id'] }}" ::data-current="activeId === '{{ $heading['id'] }}'" @class(['pl-3' => $heading['level'] === 2, 'pl-6' => $heading['level'] === 3])>
                            {{ $heading['text'] }}
                        </flux:navlist.item>
                    @endif
                @endforeach
            </flux:navlist.group>
        </flux:navlist>
    </div>
@endif
