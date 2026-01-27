@props(['language' => null, 'modifiers' => null])

@php
    $modifierList = $modifiers ? explode(' ', $modifiers) : [];
    $showCopy = ! in_array('nocopy', $modifierList, true);
@endphp

<div class="group relative" x-data="{ copied: false }">
    @if ($language)
        <span class="pointer-events-none absolute top-2 left-3 font-mono text-[0.625rem] tracking-wide text-zinc-400 uppercase dark:text-zinc-500">{{ $language }}</span>
    @endif

    @if ($showCopy)
        <button
            type="button"
            class="absolute top-2 right-2 rounded bg-zinc-200 px-2 py-1 font-sans text-xs text-zinc-500 opacity-0 transition-all group-hover:opacity-100 hover:bg-zinc-300 hover:text-zinc-700 focus:opacity-100 dark:bg-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-600 dark:hover:text-zinc-300"
            @click="navigator.clipboard.writeText($root.querySelector('code').textContent); copied = true; setTimeout(() => copied = false, 2000)"
            x-text="copied ? 'Copied!' : 'Copy'"
            aria-label="Copy code to clipboard"
        ></button>
    @endif

    {!! $slot !!}
</div>
