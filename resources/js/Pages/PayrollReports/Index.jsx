import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

function formatMoney(value) {
    return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(value || 0));
}

export default function Index({ auth, period, filters, run, rows, summary }) {
    const updateFilter = (key, value) => {
        router.get(route('payroll-reports.index'), { ...filters, [key]: value }, { preserveState: true, replace: true });
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Payroll Reports</h2>}>
            <Head title="Payroll Reports" />
            <div className="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div className="flex flex-wrap gap-2 items-center">
                    <input type="month" className="border rounded px-3 py-2" value={filters.period || period} onChange={(e) => updateFilter('period', e.target.value)} />
                    <input className="border rounded px-3 py-2" placeholder="Cari employee" value={filters.search || ''} onChange={(e) => updateFilter('search', e.target.value)} />
                    <select className="border rounded px-3 py-2" value={filters.status || ''} onChange={(e) => updateFilter('status', e.target.value)}>
                        <option value="">All Status</option>
                        <option value="draft">draft</option>
                        <option value="calculated">calculated</option>
                    </select>
                </div>

                <div className="bg-white shadow rounded p-4">
                    <div className="text-sm text-gray-500">Run</div>
                    <div className="font-semibold">{run ? `${run.run_number} (${run.status})` : 'Belum ada run period ini'}</div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <Card title="Employees" value={summary.employee_count} />
                    <Card title="Total Earnings" value={formatMoney(summary.total_earnings)} />
                    <Card title="Total Deductions" value={formatMoney(summary.total_deductions)} />
                    <Card title="Total PPh21" value={formatMoney(summary.total_pph21)} />
                    <Card title="Total Net Pay" value={formatMoney(summary.total_net_pay_after_pph21)} />
                </div>

                <div className="bg-white shadow rounded p-4 overflow-auto">
                    <table className="min-w-full text-sm">
                        <thead><tr><th className="text-left px-2 py-1">Employee</th><th className="text-left px-2 py-1">Status</th><th className="text-right px-2 py-1">Earnings</th><th className="text-right px-2 py-1">Deductions</th><th className="text-right px-2 py-1">Gross</th><th className="text-right px-2 py-1">PPh21</th><th className="text-right px-2 py-1">Net After Tax</th></tr></thead>
                        <tbody>
                            {rows.map((row) => (
                                <tr key={row.id} className="border-t">
                                    <td className="px-2 py-1">{row.employee_id} - {row.employee_name}</td>
                                    <td className="px-2 py-1">{row.status}</td>
                                    <td className="px-2 py-1 text-right">{formatMoney(row.earning_total)}</td>
                                    <td className="px-2 py-1 text-right">{formatMoney(row.deduction_total)}</td>
                                    <td className="px-2 py-1 text-right">{formatMoney(row.gross_pay)}</td>
                                    <td className="px-2 py-1 text-right">{formatMoney(row.pph21_amount)}</td>
                                    <td className="px-2 py-1 text-right">{formatMoney(row.net_pay_after_pph21)}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function Card({ title, value }) {
    return <div className="bg-white shadow rounded p-4"><div className="text-xs text-gray-500">{title}</div><div className="text-lg font-semibold">{value}</div></div>;
}
