import React from 'react';
import { Head, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, transactions, summary, filters, cashAccounts, bankAccounts }) {
    const submit = (e) => {
        e.preventDefault();
        const form = new FormData(e.target);
        router.get(route('cash-flow-reports.index'), Object.fromEntries(form.entries()));
    };

    const accountOptions = filters.cash_bank_type === 'bank' ? bankAccounts : cashAccounts;

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Cash Flow Report" />
            <div className="py-12"><div className="max-w-7xl mx-auto sm:px-6 lg:px-8"><div className="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                <h1 className="text-2xl font-semibold">Cash Flow Report</h1>
                <form onSubmit={submit} className="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <input name="date_from" type="date" defaultValue={filters.date_from} className="border rounded px-3 py-2" />
                    <input name="date_to" type="date" defaultValue={filters.date_to} className="border rounded px-3 py-2" />
                    <select name="cash_bank_type" defaultValue={filters.cash_bank_type} className="border rounded px-3 py-2">
                        <option value="">All Type</option>
                        <option value="cash">Cash</option>
                        <option value="bank">Bank</option>
                    </select>
                    <select name="cash_bank_account_id" defaultValue={filters.cash_bank_account_id} className="border rounded px-3 py-2">
                        <option value="">All Account</option>
                        {accountOptions.map((account) => <option key={account.id} value={account.id}>{account.code} - {account.name}</option>)}
                    </select>
                    <button className="bg-gray-700 text-white px-4 py-2 rounded">Filter</button>
                </form>

                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Card title="Opening" value={summary.opening_balance} />
                    <Card title="Cash In" value={summary.cash_in} />
                    <Card title="Cash Out" value={summary.cash_out} />
                    <Card title="Closing" value={summary.closing_balance} />
                </div>

                <table className="min-w-full text-sm">
                    <thead>
                        <tr className="border-b">
                            <th className="text-left py-2">Date</th>
                            <th className="text-left py-2">Number</th>
                            <th className="text-left py-2">Type</th>
                            <th className="text-right py-2">Amount</th>
                            <th className="text-left py-2">Counterpart</th>
                            <th className="text-left py-2">Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        {transactions.map((trx) => (
                            <tr key={trx.id} className="border-b">
                                <td className="py-2">{trx.voucher_date}</td>
                                <td className="py-2">{trx.voucher_number}</td>
                                <td className="py-2 capitalize">{trx.voucher_type}</td>
                                <td className="py-2 text-right">{Number(trx.amount).toLocaleString()}</td>
                                <td className="py-2">{trx.counterpart_account?.code} - {trx.counterpart_account?.name}</td>
                                <td className="py-2">{trx.reference_number || '-'}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div></div></div>
        </AuthenticatedLayout>
    );
}

function Card({ title, value }) {
    return <div className="border rounded p-4"><div className="text-xs text-gray-500">{title}</div><div className="text-xl font-semibold">{Number(value).toLocaleString()}</div></div>;
}
