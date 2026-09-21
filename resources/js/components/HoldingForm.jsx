import { useEffect, useState } from 'react';

const FIELD_CLASS =
    'mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm ' +
    'focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 ' +
    'dark:border-slate-700 dark:bg-slate-950';

/**
 * Create or edit one holding. The same form serves both cases: an existing
 * holding pre-fills the fields, otherwise it starts empty.
 */
export default function HoldingForm({
    holding,
    cryptocurrencies,
    platforms,
    errors,
    submitting,
    onSubmit,
    onCancel,
}) {
    const isEdit = Boolean(holding?.id);

    const [values, setValues] = useState({
        cryptocurrency_id: '',
        platform_id: '',
        quantity: '',
    });

    useEffect(() => {
        setValues({
            cryptocurrency_id: holding?.cryptocurrency?.id ?? '',
            platform_id: holding?.platform?.id ?? '',
            quantity: holding?.quantity ?? '',
        });
    }, [holding]);

    function handleChange(event) {
        const { name, value } = event.target;
        setValues((current) => ({ ...current, [name]: value }));
    }

    function handleSubmit(event) {
        event.preventDefault();

        onSubmit({
            cryptocurrency_id: Number(values.cryptocurrency_id),
            platform_id: Number(values.platform_id),
            quantity: Number(values.quantity),
        });
    }

    return (
        <form
            onSubmit={handleSubmit}
            className="mb-8 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900"
        >
            <h2 className="text-lg font-semibold">
                {isEdit ? 'Modifier la ligne' : 'Ajouter une ligne'}
            </h2>

            <div className="mt-5 grid gap-5 sm:grid-cols-3">
                <div>
                    <label htmlFor="cryptocurrency_id" className="block text-sm font-medium text-slate-700 dark:text-slate-300">
                        Cryptomonnaie
                    </label>
                    <select
                        id="cryptocurrency_id"
                        name="cryptocurrency_id"
                        value={values.cryptocurrency_id}
                        onChange={handleChange}
                        className={FIELD_CLASS}
                    >
                        <option value="">Choisir…</option>
                        {cryptocurrencies.map((cryptocurrency) => (
                            <option key={cryptocurrency.id} value={cryptocurrency.id}>
                                {cryptocurrency.name} ({cryptocurrency.symbol})
                            </option>
                        ))}
                    </select>
                    <FieldError messages={errors.cryptocurrency_id} />
                </div>

                <div>
                    <label htmlFor="platform_id" className="block text-sm font-medium text-slate-700 dark:text-slate-300">
                        Plateforme
                    </label>
                    <select
                        id="platform_id"
                        name="platform_id"
                        value={values.platform_id}
                        onChange={handleChange}
                        className={FIELD_CLASS}
                    >
                        <option value="">Choisir…</option>
                        {platforms.map((platform) => (
                            <option key={platform.id} value={platform.id}>
                                {platform.name}
                            </option>
                        ))}
                    </select>
                    <FieldError messages={errors.platform_id} />
                </div>

                <div>
                    <label htmlFor="quantity" className="block text-sm font-medium text-slate-700 dark:text-slate-300">
                        Quantité
                    </label>
                    <input
                        type="number"
                        id="quantity"
                        name="quantity"
                        step="0.00000001"
                        min="0.00000001"
                        value={values.quantity}
                        onChange={handleChange}
                        className={FIELD_CLASS}
                    />
                    <FieldError messages={errors.quantity} />
                </div>
            </div>

            <div className="mt-6 flex items-center gap-3">
                <button
                    type="submit"
                    disabled={submitting}
                    className="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-50 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200"
                >
                    {submitting ? 'Enregistrement…' : isEdit ? 'Enregistrer' : 'Ajouter'}
                </button>
                <button
                    type="button"
                    onClick={onCancel}
                    className="text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
                >
                    Annuler
                </button>
            </div>
        </form>
    );
}

function FieldError({ messages }) {
    if (!messages?.length) {
        return null;
    }

    return <p className="mt-1.5 text-sm text-red-600 dark:text-red-400">{messages[0]}</p>;
}
