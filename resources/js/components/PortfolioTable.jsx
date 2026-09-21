import { money, quantity, unitPrice } from '../format';

/**
 * The portfolio rows, already sorted by decreasing value by the API.
 */
export default function PortfolioTable({ holdings, total, meta, onEdit, onDelete, busyId }) {
    const base = meta.base_currency;
    const quote = meta.quote_currency;
    const baseKey = base.toLowerCase();
    const quoteKey = quote.toLowerCase();

    return (
        <div className="overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
            <table className="w-full text-sm">
                <caption className="sr-only">
                    Lignes du portefeuille, triées par valeur décroissante
                </caption>
                <thead className="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:text-slate-400">
                    <tr>
                        <th scope="col" className="px-5 py-3 text-left font-medium">Crypto</th>
                        <th scope="col" className="px-5 py-3 text-left font-medium">Plateforme</th>
                        <th scope="col" className="px-5 py-3 text-right font-medium">Quantité</th>
                        <th scope="col" className="px-5 py-3 text-right font-medium">Prix unitaire</th>
                        <th scope="col" className="px-5 py-3 text-right font-medium">Valeur {base}</th>
                        <th scope="col" className="px-5 py-3 text-right font-medium">Valeur {quote}</th>
                        <th scope="col" className="px-5 py-3 text-right font-medium">
                            <span className="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                    {holdings.length === 0 && (
                        <tr>
                            <td colSpan={7} className="px-5 py-12 text-center text-slate-500 dark:text-slate-400">
                                Aucune ligne dans le portefeuille.
                            </td>
                        </tr>
                    )}

                    {holdings.map((line) => (
                        <tr key={line.id} className={busyId === line.id ? 'opacity-40' : undefined}>
                            <td className="whitespace-nowrap px-5 py-3">
                                <span className="font-medium">{line.cryptocurrency.name}</span>
                                <span className="ml-1.5 text-xs text-slate-500 dark:text-slate-400">
                                    {line.cryptocurrency.symbol}
                                </span>
                            </td>
                            <td className="whitespace-nowrap px-5 py-3 text-slate-600 dark:text-slate-300">
                                {line.platform.name}
                            </td>
                            <td className="whitespace-nowrap px-5 py-3 text-right tabular-nums">
                                {quantity(line.quantity)}
                            </td>
                            <td className="whitespace-nowrap px-5 py-3 text-right tabular-nums text-slate-600 dark:text-slate-300">
                                {unitPrice(line.unit_price, base)}
                            </td>
                            <td className="whitespace-nowrap px-5 py-3 text-right font-medium tabular-nums">
                                {money(line.value[baseKey], base)}
                            </td>
                            <td className="whitespace-nowrap px-5 py-3 text-right tabular-nums text-slate-600 dark:text-slate-300">
                                {money(line.value[quoteKey], quote)}
                            </td>
                            <td className="whitespace-nowrap px-5 py-3 text-right text-xs">
                                <button
                                    type="button"
                                    onClick={() => onEdit(line)}
                                    disabled={busyId === line.id}
                                    className="font-medium text-slate-500 hover:text-slate-900 disabled:opacity-50 dark:text-slate-400 dark:hover:text-slate-100"
                                >
                                    Modifier
                                </button>
                                <button
                                    type="button"
                                    onClick={() => onDelete(line)}
                                    disabled={busyId === line.id}
                                    className="ml-3 font-medium text-red-600 hover:text-red-800 disabled:opacity-50 dark:text-red-400 dark:hover:text-red-300"
                                >
                                    Supprimer
                                </button>
                            </td>
                        </tr>
                    ))}
                </tbody>

                {holdings.length > 0 && (
                    <tfoot className="border-t-2 border-slate-200 dark:border-slate-800">
                        <tr>
                            <th scope="row" colSpan={4} className="px-5 py-4 text-left font-medium">
                                Total du portefeuille
                            </th>
                            <td className="whitespace-nowrap px-5 py-4 text-right text-base font-semibold tabular-nums">
                                {money(total[baseKey], base)}
                            </td>
                            <td className="whitespace-nowrap px-5 py-4 text-right text-base font-semibold tabular-nums">
                                {money(total[quoteKey], quote)}
                            </td>
                            <td className="px-5 py-4" />
                        </tr>
                    </tfoot>
                )}
            </table>
        </div>
    );
}
