import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

function formatMoney(value) {
    return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(value || 0));
}

export default function Index({ auth, runs, activeRun, period, summary }) {
    const runPayroll = () => {
        router.get(route('payroll-runs.index'), { period, run: 1 }, { preserveState: true });
    };

    const setPeriod = (value) => {
        router.get(route('payroll-runs.index'), { period: value }, { preserveState: true, replace: true });
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Payroll Runs</h2>}>
            <Head title="Payroll Runs" />
            <div className="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div className="flex gap-2 items-center">
                    <input type="month" className="border rounded px-3 py-2" value={period} onChange={(e) => setPeriod(e.target.value)} />
                    <button onClick={runPayroll} className="px-4 py-2 bg-indigo-600 text-white rounded">Run Payroll</button>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Card title="Employees" value={summary.employee_count} />
                    <Card title="Total Earnings" value={formatMoney(summary.total_earnings)} />
                    <Card title="Total Deductions" value={formatMoney(summary.total_deductions)} />
                    <Card title="Total Net Pay" value={formatMoney(summary.total_net_pay)} />
                </div>

                <div className="bg-white shadow rounded p-4">
                    <h3 className="font-semibold mb-3">Payroll Detail {period}</h3>
                    <div className="overflow-auto">
                        <table className="min-w-full text-sm">
                            <thead><tr><th className="text-left px-2 py-1">Employee</th><th className="text-right px-2 py-1">Present</th><th className="text-right px-2 py-1">Incomplete</th><th className="text-right px-2 py-1">Absent</th><th className="text-right px-2 py-1">Work Hours</th><th className="text-right px-2 py-1">OT Hours</th><th className="text-right px-2 py-1">Earnings</th><th className="text-right px-2 py-1">Deductions</th><th className="text-right px-2 py-1">Gross</th><th className="text-right px-2 py-1">PPh21</th><th className="text-right px-2 py-1">Net</th><th className="text-right px-2 py-1">Net After Tax</th></tr></thead>
                            <tbody>
                                {(activeRun?.lines || []).map((line) => (
                                    <tr key={line.id} className="border-t">
                                        <td className="px-2 py-1">{line.employee?.employee_id} - {line.employee?.full_name}</td>
                                        <td className="px-2 py-1 text-right">{line.present_days}</td>
                                        <td className="px-2 py-1 text-right">{line.incomplete_days}</td>
                                        <td className="px-2 py-1 text-right">{line.absent_days}</td>
                                        <td className="px-2 py-1 text-right">{Number(line.attendance_work_hours || 0).toFixed(2)}</td>
                                        <td className="px-2 py-1 text-right">{Number(line.approved_overtime_hours || 0).toFixed(2)}</td>
                                        <td className="px-2 py-1 text-right">{formatMoney(line.earning_total)}</td>
                                        <td className="px-2 py-1 text-right">{formatMoney(line.deduction_total)}</td>
                                        <td className="px-2 py-1 text-right">{formatMoney(line.gross_pay)}</td>
                                        <td className="px-2 py-1 text-right">{formatMoney(line.pph21_amount || 0)}</td>
                                        <td className="px-2 py-1 text-right">{formatMoney(line.net_pay)}</td>
                                        <td className="px-2 py-1 text-right">{formatMoney(line.net_pay_after_pph21 || line.net_pay)}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>

                <div className="bg-white shadow rounded p-4">
                    <h3 className="font-semibold mb-3">Run History</h3>
                    <table className="min-w-full text-sm">
                        <thead><tr><th className="text-left px-2 py-1">Run</th><th className="text-left px-2 py-1">Period</th><th className="text-left px-2 py-1">Status</th><th className="text-right px-2 py-1">Net</th></tr></thead>
                        <tbody>
                            {runs.data.map((run) => (
                                <tr key={run.id} className="border-t">
                                    <td className="px-2 py-1">{run.run_number}</td>
                                    <td className="px-2 py-1">{run.period}</td>
                                    <td className="px-2 py-1">{run.status}</td>
                                    <td className="px-2 py-1 text-right">{formatMoney(run.total_net_pay)}</td>
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
