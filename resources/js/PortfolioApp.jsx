import { useCallback, useEffect, useState } from 'react';
import { api, ValidationError } from './api';
import { dateTime } from './format';
import HoldingForm from './components/HoldingForm';
import PortfolioTable from './components/PortfolioTable';
import SummaryCards from './components/SummaryCards';

export default function PortfolioApp() {
    const [portfolio, setPortfolio] = useState(null);
    const [cryptocurrencies, setCryptocurrencies] = useState([]);
    const [platforms, setPlatforms] = useState([]);

    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [busyId, setBusyId] = useState(null);

    const [editing, setEditing] = useState(null);
    const [formErrors, setFormErrors] = useState({});
    const [notice, setNotice] = useState(null);
    const [error, setError] = useState(null);

    /**
     * Reload the portfolio. Prices are recomputed server side on every call,
     * so this doubles as the refresh action.
     */
    const loadPortfolio = useCallback(async () => {
        const response = await api.portfolio();
        setPortfolio(response);
    }, []);

    useEffect(() => {
        async function loadEverything() {
            try {
                const [, cryptos, plats] = await Promise.all([
                    loadPortfolio(),
                    api.cryptocurrencies(),
                    api.platforms(),
                ]);

                setCryptocurrencies(cryptos.data);
                setPlatforms(plats.data);
            } catch (caught) {
                setError(caught.message);
            } finally {
                setLoading(false);
            }
        }

        loadEverything();
    }, [loadPortfolio]);

    async function handleRefresh() {
        setRefreshing(true);
        setError(null);

        try {
            await loadPortfolio();
        } catch (caught) {
            setError(caught.message);
        } finally {
            setRefreshing(false);
        }
    }

    async function handleSubmit(values) {
        setSubmitting(true);
        setFormErrors({});
        setError(null);

        try {
            if (editing?.id) {
                await api.updateHolding(editing.id, values);
                setNotice('Ligne mise à jour.');
            } else {
                await api.createHolding(values);
                setNotice('Ligne ajoutée au portefeuille.');
            }

            setEditing(null);
            await loadPortfolio();
        } catch (caught) {
            if (caught instanceof ValidationError) {
                setFormErrors(caught.errors);
            } else {
                setError(caught.message);
            }
        } finally {
            setSubmitting(false);
        }
    }

    async function handleDelete(line) {
        if (!window.confirm(`Supprimer ${line.cryptocurrency.symbol} sur ${line.platform.name} ?`)) {
            return;
        }

        setBusyId(line.id);
        setError(null);

        try {
            await api.deleteHolding(line.id);
            setNotice('Ligne supprimée du portefeuille.');

            if (editing?.id === line.id) {
                setEditing(null);
            }

            await loadPortfolio();
        } catch (caught) {
            setError(caught.message);
        } finally {
            setBusyId(null);
        }
    }

    function startCreating() {
        setFormErrors({});
        setNotice(null);
        setEditing({});
    }

    function startEditing(line) {
        setFormErrors({});
        setNotice(null);
        setEditing({
            id: line.id,
            quantity: line.quantity,
            cryptocurrency: { id: cryptocurrencyIdFor(line, cryptocurrencies) },
            platform: { id: platformIdFor(line, platforms) },
        });
    }

    if (loading) {
        return (
            <main className="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
                <p className="text-sm text-slate-500 dark:text-slate-400">Chargement du portefeuille…</p>
            </main>
        );
    }

    if (!portfolio) {
        return (
            <main className="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
                <Banner tone="error">{error ?? 'Le portefeuille est indisponible.'}</Banner>
            </main>
        );
    }

    const { data, meta } = portfolio;

    return (
        <main className="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
            {notice && <Banner tone="success" onDismiss={() => setNotice(null)}>{notice}</Banner>}
            {error && <Banner tone="error" onDismiss={() => setError(null)}>{error}</Banner>}

            <header className="flex flex-wrap items-start justify-between gap-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Portefeuille crypto</h1>
                    <p className="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        Valorisé le {dateTime(meta.priced_at)} · 1 {meta.base_currency} ={' '}
                        {meta.exchange_rate} {meta.quote_currency}
                    </p>
                    <div className="mt-3 flex items-center gap-3">
                        <button
                            type="button"
                            onClick={startCreating}
                            className="inline-flex items-center rounded-lg bg-slate-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200"
                        >
                            + Ajouter une ligne
                        </button>
                        <button
                            type="button"
                            onClick={handleRefresh}
                            disabled={refreshing}
                            className="text-sm text-slate-500 hover:text-slate-800 disabled:opacity-50 dark:text-slate-400 dark:hover:text-slate-200"
                        >
                            {refreshing ? 'Actualisation…' : 'Actualiser les prix'}
                        </button>
                    </div>
                </div>

                <SummaryCards total={data.total} meta={meta} />
            </header>

            <div className="mt-8">
                {editing && (
                    <HoldingForm
                        holding={editing}
                        cryptocurrencies={cryptocurrencies}
                        platforms={platforms}
                        errors={formErrors}
                        submitting={submitting}
                        onSubmit={handleSubmit}
                        onCancel={() => setEditing(null)}
                    />
                )}

                <PortfolioTable
                    holdings={data.holdings}
                    total={data.total}
                    meta={meta}
                    onEdit={startEditing}
                    onDelete={handleDelete}
                    busyId={busyId}
                />
            </div>

            <p className="mt-4 text-xs text-slate-500 dark:text-slate-400">
                Données servies par l'API JSON :{' '}
                <a href="/api/portfolio" className="font-medium underline underline-offset-2">
                    /api/portfolio
                </a>
            </p>
        </main>
    );
}

/**
 * The portfolio payload identifies a cryptocurrency by its CoinGecko id, while
 * the form needs the database id. The reference lists bridge the two.
 */
function cryptocurrencyIdFor(line, cryptocurrencies) {
    return cryptocurrencies.find(
        (cryptocurrency) => cryptocurrency.coingecko_id === line.cryptocurrency.coingecko_id,
    )?.id ?? '';
}

function platformIdFor(line, platforms) {
    return platforms.find((platform) => platform.slug === line.platform.slug)?.id ?? '';
}

function Banner({ tone, children, onDismiss }) {
    const tones = {
        success:
            'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300',
        error:
            'border-red-200 bg-red-50 text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-300',
    };

    return (
        <div className={`mb-6 flex items-start justify-between gap-4 rounded-lg border px-4 py-3 text-sm ${tones[tone]}`}>
            <span>{children}</span>
            {onDismiss && (
                <button type="button" onClick={onDismiss} className="font-medium opacity-60 hover:opacity-100">
                    ×
                </button>
            )}
        </div>
    );
}
