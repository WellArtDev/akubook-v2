import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, reconciliations, bankAccounts, filters = {} }) {
    const updateFilter = (key, value) => {
        router.get(route('bank-reconciliations.index'), { ...filters, [key]: value }, { preserveState: true, replace: true });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Bank Reconciliations" />
            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-6 flex items-center justify-between">
                        <h1 className="text-2xl font-semibold text-gray-900">Bank Reconciliations</h1>
                        <Link href={route('bank-reconciliations.create')} className="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
                            New Reconciliation
                        </Link>
                    </div>

                    <div className="mb-6 grid gap-4 rounded bg-white p-4 shadow md:grid-cols-4">
                        <select value={filters.bank_account_id || ''} onChange={(e) => updateFilter('bank_account_id', e.target.value)} className="rounded border-gray-300">
                            <option value="">All Banks</option>
                            {bankAccounts.map((account) => (
                                <option key={account.id} value={account.id}>{account.code} - {account.name}</option>
                            ))}
                        </select>
                        <select value={filters.status || ''} onChange={(e) => updateFilter('status', e.target.value)} className="rounded border-gray-300">
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="reconciled">Reconciled</option>
                        </select>
                        <input type="date" value={filters.date_from || ''} onChange={(e) => updateFilter('date_from', e.target.value)} className="rounded border-gray-300" />
                        <input type="date" value={filters.date_to || ''} onChange={(e) => updateFilter('date_to', e.target.value)} className="rounded border-gray-300" />
                    </div>

                    <div className="overflow-hidden bg-white shadow sm:rounded-lg">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Number</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Bank</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Period</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                    <th className="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Difference</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 bg-white">
                                {reconciliations.data.map((item) => (
                                    <tr key={item.id} className="hover:bg-gray-50" onClick={() => router.visit(route('bank-reconciliations.show', item.id))}>
                                        <td className="px-6 py-4 text-sm font-medium text-indigo-600">{item.reconciliation_number}</td>
                                        <td className="px-6 py-4 text-sm text-gray-900">{item.bank_account?.name}</td>
                                        <td className="px-6 py-4 text-sm text-gray-900">{item.statement_start_date} - {item.statement_end_date}</td>
                                        <td className="px-6 py-4 text-sm text-gray-900">{item.status}</td>
                                        <td className="px-6 py-4 text-right text-sm text-gray-900">{Number(item.difference).toLocaleString()}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
