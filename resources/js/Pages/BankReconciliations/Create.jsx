import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Create({ auth, bankAccounts }) {
    const today = new Date().toISOString().slice(0, 10);
    const firstBank = bankAccounts[0];
    const { data, setData, post, processing, errors } = useForm({
        bank_account_id: firstBank?.id || '',
        statement_start_date: today,
        statement_end_date: today,
        reconciliation_date: today,
        statement_opening_balance: firstBank?.opening_balance || 0,
        statement_closing_balance: firstBank?.opening_balance || 0,
        notes: '',
        lines: [
            { transaction_date: today, description: '', debit: 0, credit: 0, reference_number: '', notes: '' },
        ],
    });

    const updateLine = (index, key, value) => {
        const lines = [...data.lines];
        lines[index] = { ...lines[index], [key]: value };
        setData('lines', lines);
    };

    const addLine = () => setData('lines', [...data.lines, { transaction_date: today, description: '', debit: 0, credit: 0, reference_number: '', notes: '' }]);
    const removeLine = (index) => setData('lines', data.lines.filter((_, i) => i !== index));

    const submit = (e) => {
        e.preventDefault();
        post(route('bank-reconciliations.store'));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="New Bank Reconciliation" />
            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <form onSubmit={submit} className="space-y-6">
                        <div className="rounded bg-white p-6 shadow">
                            <div className="mb-6 flex items-center justify-between">
                                <h1 className="text-2xl font-semibold">New Bank Reconciliation</h1>
                                <Link href={route('bank-reconciliations.index')} className="text-indigo-600">Back</Link>
                            </div>
                            {errors.error && <div className="mb-4 rounded bg-red-50 p-3 text-red-700">{errors.error}</div>}
                            <div className="grid gap-4 md:grid-cols-3">
                                <label className="block">
                                    <span className="text-sm font-medium">Bank Account</span>
                                    <select value={data.bank_account_id} onChange={(e) => setData('bank_account_id', e.target.value)} className="mt-1 w-full rounded border-gray-300">
                                        <option value="">Select bank</option>
                                        {bankAccounts.map((account) => <option key={account.id} value={account.id}>{account.code} - {account.name}</option>)}
                                    </select>
                                    {errors.bank_account_id && <p className="text-sm text-red-600">{errors.bank_account_id}</p>}
                                </label>
                                <label className="block">
                                    <span className="text-sm font-medium">Start Date</span>
                                    <input type="date" value={data.statement_start_date} onChange={(e) => setData('statement_start_date', e.target.value)} className="mt-1 w-full rounded border-gray-300" />
                                </label>
                                <label className="block">
                                    <span className="text-sm font-medium">End Date</span>
                                    <input type="date" value={data.statement_end_date} onChange={(e) => setData('statement_end_date', e.target.value)} className="mt-1 w-full rounded border-gray-300" />
                                </label>
                                <label className="block">
                                    <span className="text-sm font-medium">Reconciliation Date</span>
                                    <input type="date" value={data.reconciliation_date} onChange={(e) => setData('reconciliation_date', e.target.value)} className="mt-1 w-full rounded border-gray-300" />
                                </label>
                                <label className="block">
                                    <span className="text-sm font-medium">Opening Balance</span>
                                    <input type="number" value={data.statement_opening_balance} onChange={(e) => setData('statement_opening_balance', e.target.value)} className="mt-1 w-full rounded border-gray-300" />
                                </label>
                                <label className="block">
                                    <span className="text-sm font-medium">Closing Balance</span>
                                    <input type="number" value={data.statement_closing_balance} onChange={(e) => setData('statement_closing_balance', e.target.value)} className="mt-1 w-full rounded border-gray-300" />
                                </label>
                            </div>
                        </div>

                        <div className="rounded bg-white p-6 shadow">
                            <div className="mb-4 flex items-center justify-between">
                                <h2 className="text-lg font-semibold">Statement Lines</h2>
                                <button type="button" onClick={addLine} className="rounded bg-gray-100 px-3 py-2 text-sm">Add Line</button>
                            </div>
                            <div className="space-y-3">
                                {data.lines.map((line, index) => (
                                    <div key={index} className="grid gap-3 rounded border p-3 md:grid-cols-7">
                                        <input type="date" value={line.transaction_date} onChange={(e) => updateLine(index, 'transaction_date', e.target.value)} className="rounded border-gray-300" />
                                        <input value={line.description} onChange={(e) => updateLine(index, 'description', e.target.value)} placeholder="Description" className="rounded border-gray-300 md:col-span-2" />
                                        <input type="number" value={line.debit} onChange={(e) => updateLine(index, 'debit', e.target.value)} placeholder="Debit" className="rounded border-gray-300" />
                                        <input type="number" value={line.credit} onChange={(e) => updateLine(index, 'credit', e.target.value)} placeholder="Credit" className="rounded border-gray-300" />
                                        <input value={line.reference_number} onChange={(e) => updateLine(index, 'reference_number', e.target.value)} placeholder="Reference" className="rounded border-gray-300" />
                                        <button type="button" onClick={() => removeLine(index)} className="rounded bg-red-50 px-3 py-2 text-red-700">Remove</button>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <button type="submit" disabled={processing} className="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 disabled:opacity-50">
                            Save
                        </button>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
