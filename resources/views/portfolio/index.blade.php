@use('Illuminate\Support\Number')

<x-layouts.app title="Portefeuille crypto">
    <header class="flex flex-wrap items-start justify-between gap-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Portefeuille crypto</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                Valorisé le {{ $summary->pricedAt->format('d/m/Y à H:i:s') }}
                · 1 {{ $summary->currency }} = {{ Number::format($summary->exchangeRate, maxPrecision: 5) }} {{ $summary->convertedCurrency }}
            </p>
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
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-500 dark:text-slate-400">
                            Aucune ligne dans le portefeuille.
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
