<div class="mx-auto max-w-7xl px-6 py-4 sm:py-6 lg:px-8">
    <header class="mb-6">
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-50">Command Cheat Sheet</h1>
        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
            <strong class="font-semibold text-zinc-700 dark:text-zinc-200">{{ $commandCount }}</strong>
            commands organized by deployment workflow.
        </p>
    </header>

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-2.5 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400">
        <span>
            Prefix commands with
            <code class="rounded bg-zinc-200/60 px-1.5 py-0.5 font-mono text-xs text-fuchsia-600 dark:bg-zinc-700/60 dark:text-fuchsia-400">deployer</code>
            in your terminal.
        </span>
        <span class="flex flex-wrap items-center gap-1.5">
            Global options:
            <code class="rounded bg-zinc-200/60 px-1.5 py-0.5 font-mono text-xs text-fuchsia-600 dark:bg-zinc-700/60 dark:text-fuchsia-400">--env</code>
            <code class="rounded bg-zinc-200/60 px-1.5 py-0.5 font-mono text-xs text-fuchsia-600 dark:bg-zinc-700/60 dark:text-fuchsia-400">--inventory</code>
            <code class="rounded bg-zinc-200/60 px-1.5 py-0.5 font-mono text-xs text-fuchsia-600 dark:bg-zinc-700/60 dark:text-fuchsia-400">--quiet</code>
        </span>
    </div>

    @if ($sections !== [])
        <div class="space-y-10" aria-label="DeployerPHP commands grouped by related documentation area">
            @foreach ($sections as $section)
                @php
                    $sectionDescription = match ($section['name']) {
                        'Server & Site Operations' => 'Core commands for provisioning hosts, managing sites, and handling routine server maintenance.',
                        'Scheduling & Process Control' => 'Tools for defining scheduled jobs and managing long-running background workers.',
                        'Web Runtime Services' => 'Commands for configuring and controlling the web stack, including PHP and Nginx services.',
                        'Data Services' => 'Operational commands for databases and cache layers used by deployed applications.',
                        'Scaffolding' => 'Generators and setup helpers that bootstrap common deployment resources and project structure.',
                        'Cloud Providers' => 'Provider-specific commands for creating and managing infrastructure across supported cloud platforms.',
                        default => 'Supporting commands for specialized tasks that do not fit a primary operations category.',
                    };
                @endphp

                <section>
                    <header class="mb-4 border-b border-zinc-200 pb-2 dark:border-zinc-700">
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-50">{{ $section['name'] }}</h2>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $sectionDescription }}</p>
                    </header>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ($section['groups'] as $group)
                            <article class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                                <h3 class="mb-3 flex items-baseline gap-1.5 text-sm font-semibold text-zinc-900 dark:text-zinc-50">
                                    {{ $group['name'] }}
                                    <span class="text-xs font-normal text-zinc-400 dark:text-zinc-500">({{ $group['count'] }})</span>
                                </h3>

                                <ul class="space-y-1.5">
                                    @foreach ($group['commands'] as $command)
                                        <li title="{{ $command['description'] }}">
                                            <code class="rounded bg-zinc-100/80 px-1 py-0.5 font-mono text-sm text-fuchsia-600 dark:bg-zinc-800/80 dark:text-fuchsia-400">{{ $command['primary'] }}</code>
                                        </li>
                                    @endforeach
                                </ul>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @else
        <p class="text-sm text-zinc-500 dark:text-zinc-400">No commands were discovered for the configured docs path.</p>
    @endif

    <x-docs.footer />
</div>
