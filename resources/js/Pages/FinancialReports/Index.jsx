import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

const money = (value) => Number(value || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

function SummaryCard({ title, value }) {
    return (
        <div className="bg-white rounded shadow p-4">
            <div className="text-sm text-gray-500">{title}</div>
            <div className="text-xl font-semibold mt-1">{money(value)}</div>
        </div>
    );
}

export default function Index({ auth, generated_at, filters, trial_balance, profit_loss, balance_sheet }) {
    const updateFilter = (key, value) => {
        router.get(route('financial-reports.index'), { ...filters, [key]: value }, { preserveState: true, replace: true });
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Financial Reports</h2>}>
            <Head title="Financial Reports" />
            <div className="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <div className="bg-white rounded shadow p-4 flex flex-wrap gap-3 items-end">
                    <div>
                        <label className="block text-xs text-gray-500">Date From</label>
                        <input type="date" className="border rounded px-3 py-2" value={filters.date_from || ''} onChange={(e) => updateFilter('date_from', e.target.value)} />
                    </div>
                    <div>
                        <label className="block text-xs text-gray-500">Date To</label>
                        <input type="date" className="border rounded px-3 py-2" value={filters.date_to || ''} onChange={(e) => updateFilter('date_to', e.target.value)} />
                    </div>
                    <a href={route('report-exports.financial', filters)} className="px-3 py-2 bg-emerald-600 text-white rounded">Export CSV</a>
                    <div className="text-xs text-gray-500 ml-auto">Generated: {generated_at}</div>
                </div>

                <section className="space-y-3">
                    <h3 className="text-lg font-semibold">Profit &amp; Loss</h3>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <SummaryCard title="Revenue" value={profit_loss.revenue} />
                        <SummaryCard title="Expense" value={profit_loss.expense} />
                        <SummaryCard title="Net Profit/Loss" value={profit_loss.net_profit} />
                    </div>
                </section>

                <section className="space-y-3">
                    <h3 className="text-lg font-semibold">Balance Sheet</h3>
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <SummaryCard title="Assets" value={balance_sheet.assets} />
                        <SummaryCard title="Liabilities" value={balance_sheet.liabilities} />
                        <SummaryCard title="Equity" value={balance_sheet.equity} />
                        <SummaryCard title="Liabilities + Equity" value={balance_sheet.liabilities_plus_equity} />
                    </div>
                </section>

                <section className="space-y-3">
                    <h3 className="text-lg font-semibold">Trial Balance</h3>
                    <div className="bg-white rounded shadow overflow-x-auto">
                        <table className="min-w-full text-sm">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-2 text-left">Code</th>
                                    <th className="px-4 py-2 text-left">Name</th>
                                    <th className="px-4 py-2 text-left">Type</th>
                                    <th className="px-4 py-2 text-right">Debit</th>
                                    <th className="px-4 py-2 text-right">Credit</th>
                                    <th className="px-4 py-2 text-right">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                {trial_balance.rows.map((row) => (
                                    <tr key={row.account_id} className="border-t">
                                        <td className="px-4 py-2">{row.account_code}</td>
                                        <td className="px-4 py-2">{row.account_name}</td>
                                        <td className="px-4 py-2">{row.type}</td>
                                        <td className="px-4 py-2 text-right">{money(row.debit)}</td>
                                        <td className="px-4 py-2 text-right">{money(row.credit)}</td>
                                        <td className="px-4 py-2 text-right">{money(row.balance)}</td>
                                    </tr>
                                ))}
                            </tbody>
                            <tfoot className="bg-gray-50 font-semibold border-t">
                                <tr>
                                    <td className="px-4 py-2" colSpan={3}>Total</td>
                                    <td className="px-4 py-2 text-right">{money(trial_balance.summary.total_debit)}</td>
                                    <td className="px-4 py-2 text-right">{money(trial_balance.summary.total_credit)}</td>
                                    <td className="px-4 py-2 text-right">{money(trial_balance.summary.total_balance)}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
