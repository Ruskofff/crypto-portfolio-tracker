import { money } from '../format';

/**
 * The two portfolio totals, in the base and the quote currency.
 */
export default function SummaryCards({ total, meta }) {
    const cards = [
        { currency: meta.base_currency, amount: total[meta.base_currency.toLowerCase()] },
        { currency: meta.quote_currency, amount: total[meta.quote_currency.toLowerCase()] },
    ];

    return (
        <dl className="flex flex-wrap gap-4">
            {cards.map((card) => (
                <div
                    key={card.currency}
                    className="rounded-xl border border-slate-200 bg-white px-5 py-4 dark:border-slate-800 dark:bg-slate-900"
                >
                    <dt className="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Total {card.currency}
                    </dt>
                    <dd className="mt-1 text-2xl font-semibold tabular-nums">
                        {money(card.amount, card.currency)}
                    </dd>
                </div>
            ))}
        </dl>
    );
}
