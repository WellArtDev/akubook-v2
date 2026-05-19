import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Index({ auth, suppliers, filters, statement }) {
    const form = useForm({
        supplier_id: filters.supplier_id ?? '',
        date_from: filters.date_from,
        date_to: filters.date_to,
    });

    const submit = (e) => {
        e.preventDefault();
        form.get(route('supplier-statements.index'));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Supplier Statement" />
            <div className="mx-auto max-w-7xl space-y-6 p-6">
                <h1 className="text-2xl font-bold text-slate-900">Supplier Statement</h1>

                <form onSubmit={submit} className="grid gap-3 rounded-lg bg-white p-4 shadow sm:grid-cols-4">
                    <select className="rounded border-slate-300" value={form.data.supplier_id} onChange={(e) => form.setData('supplier_id', e.target.value)}>
                        <option value="">Pilih Supplier</option>
                        {suppliers.map((supplier) => (
                            <option key={supplier.id} value={supplier.id}>{supplier.supplier_code} - {supplier.name}</option>
                        ))}
                    </select>
                    <input type="date" className="rounded border-slate-300" value={form.data.date_from} onChange={(e) => form.setData('date_from', e.target.value)} />
                    <input type="date" className="rounded border-slate-300" value={form.data.date_to} onChange={(e) => form.setData('date_to', e.target.value)} />
                    <div className="flex gap-2">
                        <button type="submit" className="rounded bg-slate-900 px-4 py-2 text-white">Generate</button>
                        {statement && (
                            <a href={route('supplier-statements.pdf', { supplier_id: form.data.supplier_id, date_from: form.data.date_from, date_to: form.data.date_to })} className="rounded border border-slate-300 px-4 py-2 text-sm">PDF JSON</a>
                        )}
                    </div>
                </form>

                {statement && (
                    <div className="space-y-4 rounded-lg bg-white p-4 shadow">
                        <div>
                            <h2 className="text-lg font-semibold">{statement.supplier.supplier_code} - {statement.supplier.name}</h2>
                            <p className="text-sm text-slate-500">{statement.date_from} s/d {statement.date_to}</p>
                        </div>
                        <div className="grid gap-3 sm:grid-cols-4">
                            <Metric label="Opening" value={statement.opening_balance} />
                            <Metric label="Debit" value={statement.total_debit} />
                            <Metric label="Credit" value={statement.total_credit} />
                            <Metric label="Closing" value={statement.closing_balance} />
                        </div>
                        <div className="overflow-x-auto">
                            <table className="min-w-full text-sm">
                                <thead>
                                    <tr className="border-b text-left">
                                        <th className="py-2">Date</th>
                                        <th>Type</th>
                                        <th>Reference</th>
                                        <th>Description</th>
                                        <th className="text-right">Debit</th>
                                        <th className="text-right">Credit</th>
                                        <th className="text-right">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {statement.transactions.map((row, index) => (
                                        <tr key={`${row.reference}-${index}`} className="border-b">
                                            <td className="py-2">{row.date}</td>
                                            <td>{row.type}</td>
                                            <td>{row.reference}</td>
                                            <td>{row.description}</td>
                                            <td className="text-right">{Number(row.debit).toLocaleString('id-ID')}</td>
                                            <td className="text-right">{Number(row.credit).toLocaleString('id-ID')}</td>
                                            <td className="text-right">{Number(row.balance).toLocaleString('id-ID')}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}

function Metric({ label, value }) {
    return (
        <div className="rounded border border-slate-200 p-3">
            <p className="text-xs text-slate-500">{label}</p>
            <p className="text-lg font-semibold">{Number(value).toLocaleString('id-ID')}</p>
        </div>
    );
}
