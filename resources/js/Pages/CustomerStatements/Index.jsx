import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

const formatCurrency = (value) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(value || 0));

export default function Index({ auth, customers, filters, statement }) {
    const { data, setData, get, processing } = useForm({
        customer_id: filters.customer_id || '',
        date_from: filters.date_from || '',
        date_to: filters.date_to || '',
    });

    const submit = (event) => {
        event.preventDefault();
        get(route('customer-statements.index'), { preserveState: true });
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Customer Statement</h2>}>
            <Head title="Customer Statement" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                    <form onSubmit={submit} className="grid gap-4 rounded bg-white p-6 shadow md:grid-cols-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700">Customer</label>
                            <select value={data.customer_id} onChange={(e) => setData('customer_id', e.target.value)} className="mt-1 w-full rounded border-gray-300">
                                <option value="">Pilih customer</option>
                                {customers.map((customer) => (
                                    <option key={customer.id} value={customer.id}>{customer.name}</option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">Dari</label>
                            <input type="date" value={data.date_from} onChange={(e) => setData('date_from', e.target.value)} className="mt-1 w-full rounded border-gray-300" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">Sampai</label>
                            <input type="date" value={data.date_to} onChange={(e) => setData('date_to', e.target.value)} className="mt-1 w-full rounded border-gray-300" />
                        </div>
                        <div className="flex items-end gap-3">
                            <button type="submit" disabled={processing} className="rounded bg-blue-600 px-4 py-2 text-white disabled:opacity-50">Generate</button>
                            {statement && (
                                <Link href={route('customer-statements.pdf', data)} className="rounded bg-gray-800 px-4 py-2 text-white">PDF JSON</Link>
                            )}
                        </div>
                    </form>

                    {statement && (
                        <>
                            <div className="rounded bg-white p-6 shadow">
                                <div className="mb-4 flex items-start justify-between">
                                    <div>
                                        <h3 className="text-lg font-semibold text-gray-900">{statement.customer.name}</h3>
                                        <p className="text-sm text-gray-500">{statement.date_from} - {statement.date_to}</p>
                                    </div>
                                    <div className="text-right text-sm text-gray-500">{statement.customer.code}</div>
                                </div>
                                <div className="grid gap-4 md:grid-cols-5">
                                    <Metric label="Opening" value={formatCurrency(statement.opening_balance)} />
                                    <Metric label="Invoices" value={formatCurrency(statement.total_debit)} />
                                    <Metric label="Payments/Credits" value={formatCurrency(statement.total_credit)} />
                                    <Metric label="Closing" value={formatCurrency(statement.closing_balance)} />
                                    <Metric label="0-30 Aging" value={formatCurrency(statement.aging?.['0_30'])} />
                                </div>
                            </div>

                            <div className="rounded bg-white p-6 shadow">
                                <h3 className="mb-4 text-lg font-semibold text-gray-900">Transactions</h3>
                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                {['Date', 'Type', 'Reference', 'Description', 'Debit', 'Credit', 'Balance'].map((head) => <th key={head} className="px-3 py-2 text-left font-medium text-gray-500">{head}</th>)}
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-100">
                                            {statement.transactions.map((row, index) => (
                                                <tr key={`${row.reference}-${index}`}>
                                                    <td className="px-3 py-2">{row.date}</td>
                                                    <td className="px-3 py-2">{row.type}</td>
                                                    <td className="px-3 py-2">{row.reference}</td>
                                                    <td className="px-3 py-2">{row.description}</td>
                                                    <td className="px-3 py-2">{formatCurrency(row.debit)}</td>
                                                    <td className="px-3 py-2">{formatCurrency(row.credit)}</td>
                                                    <td className="px-3 py-2 font-medium">{formatCurrency(row.balance)}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function Metric({ label, value }) {
    return (
        <div className="rounded border border-gray-200 p-4">
            <div className="text-xs uppercase tracking-wide text-gray-500">{label}</div>
            <div className="mt-1 text-lg font-semibold text-gray-900">{value}</div>
        </div>
    );
}
