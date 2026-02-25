@props([
    'headings',
])

@php
    $filteredHeadings = array_values(array_filter($headings, fn ($h) => $h['level'] <= 3 && $h['id']));
@endphp

@if (count($filteredHeadings) > 1)
    <div x-data="docsScrollSpy">
        <flux:navlist>
            <flux:navlist.group heading="On this page">
                @foreach ($filteredHeadings as $index => $heading)
                    <flux:navlist.item href="{{ '#' . $heading['id'] }}" x-bind:data-passed="activeIndex > {{ $index }} || undefined" x-bind:data-current="activeIndex === {{ $index }} || undefined" @class(['pl-3' => $heading['level'] === 3])>
                        {{ $heading['text'] }}
                    </flux:navlist.item>
                @endforeach
            </flux:navlist.group>
        </flux:navlist>
    </div>
@endif
