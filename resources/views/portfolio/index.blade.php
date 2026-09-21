@use('Illuminate\Support\Number')

<x-layouts.app title="Portefeuille crypto">
    @if (session('status'))
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300">
            {{ session('status') }}
        </div>
    @endif

    <header class="flex flex-wrap items-start justify-between gap-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Portefeuille crypto</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                Valorisé le {{ $summary->pricedAt->format('d/m/Y à H:i:s') }}
                · 1 {{ $summary->currency }} = {{ Number::format($summary->exchangeRate, maxPrecision: 5) }} {{ $summary->convertedCurrency }}
            </p>
            <a
                href="{{ route('holdings.create') }}"
                class="mt-3 inline-flex items-center rounded-lg bg-slate-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200"
            >
                + Add a holding
            </a>
        </div>

        <dl class="flex flex-wrap gap-4">
            <div class="rounded-xl border border-slate-200 bg-white px-5 py-4 dark:border-slate-800 dark:bg-slate-900">
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    Total {{ $summary->currency }}
                </dt>
                <dd class="mt-1 text-2xl font-semibold tabular-nums">
                    {{ Number::currency($summary->total, $summary->currency) }}
                </dd>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white px-5 py-4 dark:border-slate-800 dark:bg-slate-900">
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    Total {{ $summary->convertedCurrency }}
                </dt>
                <dd class="mt-1 text-2xl font-semibold tabular-nums">
                    {{ Number::currency($summary->convertedTotal, $summary->convertedCurrency) }}
                </dd>
            </div>
        </dl>
    </header>

    <div class="mt-8 overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <table class="w-full text-sm">
            <caption class="sr-only">
                Lignes du portefeuille, triées par valeur décroissante
            </caption>
            <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:text-slate-400">
                <tr>
                    <th scope="col" class="px-5 py-3 text-left font-medium">Crypto</th>
                    <th scope="col" class="px-5 py-3 text-left font-medium">Plateforme</th>
                    <th scope="col" class="px-5 py-3 text-right font-medium">Quantité</th>
                    <th scope="col" class="px-5 py-3 text-right font-medium">Prix unitaire</th>
                    <th scope="col" class="px-5 py-3 text-right font-medium">Valeur {{ $summary->currency }}</th>
                    <th scope="col" class="px-5 py-3 text-right font-medium">Valeur {{ $summary->convertedCurrency }}</th>
                    <th scope="col" class="px-5 py-3 text-right font-medium">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($summary->lines as $line)
                    <tr>
                        <td class="whitespace-nowrap px-5 py-3">
                            <span class="font-medium">{{ $line->cryptocurrencyName }}</span>
                            <span class="ml-1.5 text-xs text-slate-500 dark:text-slate-400">{{ $line->symbol }}</span>
                        </td>
                        <td class="whitespace-nowrap px-5 py-3 text-slate-600 dark:text-slate-300">
                            {{ $line->platformName }}
                        </td>
                        <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums">
                            {{ Number::format($line->quantity, maxPrecision: 8) }}
                        </td>
                        <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums text-slate-600 dark:text-slate-300">
                            {{ Number::currency($line->unitPrice, $summary->currency, precision: $line->unitPrice < 1 ? 6 : 2) }}
                        </td>
                        <td class="whitespace-nowrap px-5 py-3 text-right font-medium tabular-nums">
                            {{ Number::currency($line->value, $summary->currency) }}
                        </td>
                        <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums text-slate-600 dark:text-slate-300">
                            {{ Number::currency($line->convertedValue, $summary->convertedCurrency) }}
                        </td>
                        <td class="whitespace-nowrap px-5 py-3 text-right text-xs">
                            <a
                                href="{{ route('holdings.edit', $line->holdingId) }}"
                                class="font-medium text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100"
                            >
                                Edit
                            </a>
                            <form
                                method="POST"
                                action="{{ route('holdings.destroy', $line->holdingId) }}"
                                class="inline"
                                onsubmit="return confirm('Remove this holding from the portfolio?');"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ml-3 font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-500 dark:text-slate-400">
                            Aucune ligne dans le portefeuille.
                            <a href="{{ route('holdings.create') }}" class="ml-1 font-medium underline underline-offset-2">
                                Ajouter une ligne.
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if ($summary->lines !== [])
                <tfoot class="border-t-2 border-slate-200 dark:border-slate-800">
                    <tr>
                        <th scope="row" colspan="4" class="px-5 py-4 text-left font-medium">
                            Total du portefeuille
                        </th>
                        <td class="whitespace-nowrap px-5 py-4 text-right text-base font-semibold tabular-nums">
                            {{ Number::currency($summary->total, $summary->currency) }}
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-right text-base font-semibold tabular-nums">
                            {{ Number::currency($summary->convertedTotal, $summary->convertedCurrency) }}
                        </td>
                        <td class="px-5 py-4"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <p class="mt-4 text-xs text-slate-500 dark:text-slate-400">
        Les mêmes données au format JSON :
        <a href="{{ route('api.portfolio') }}"
           class="font-medium underline underline-offset-2 hover:text-slate-900 dark:hover:text-slate-100">
            {{ route('api.portfolio') }}
        </a>
    </p>
</x-layouts.app>
