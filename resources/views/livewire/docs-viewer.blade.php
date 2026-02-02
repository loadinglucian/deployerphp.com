<x-slot:sidebar>
    <x-docs.toc :toc="$toc" :current-path="$page" />
</x-slot:sidebar>

<x-docs.content :content="$content" />

<x-slot:aside>
    <x-docs.headings :headings="$headings" />
</x-slot:aside>
