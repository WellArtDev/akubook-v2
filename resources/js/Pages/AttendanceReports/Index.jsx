import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

function Card({ label, value }) {
    return <div className="bg-white rounded shadow p-4"><div className="text-sm text-gray-500">{label}</div><div className="text-2xl font-semibold">{value}</div></div>;
}

export default function Index({ auth, rows, summary, filters, statuses }) {
    const updateFilter = (key, value) => {
        router.get(route('attendance-reports.index'), { ...filters, [key]: value }, { preserveState: true, replace: true });
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Attendance Reports</h2>}>
            <Head title="Attendance Reports" />
            <div className="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div className="bg-white rounded shadow p-4 grid grid-cols-1 md:grid-cols-4 gap-3">
                    <input className="border rounded px-3 py-2" placeholder="Search employee" value={filters.search || ''} onChange={(e) => updateFilter('search', e.target.value)} />
                    <select className="border rounded px-3 py-2" value={filters.status || ''} onChange={(e) => updateFilter('status', e.target.value)}>
                        <option value="">All Status</option>
                        {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
                    </select>
                    <input type="date" className="border rounded px-3 py-2" value={filters.date_from || ''} onChange={(e) => updateFilter('date_from', e.target.value)} />
                    <input type="date" className="border rounded px-3 py-2" value={filters.date_to || ''} onChange={(e) => updateFilter('date_to', e.target.value)} />
                </div>
                <div className="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <Card label="Records" value={summary.total_records} />
                    <Card label="Present" value={summary.present_count} />
                    <Card label="Incomplete" value={summary.incomplete_count} />
                    <Card label="Absent" value={summary.absent_count} />
                    <Card label="Work Hours" value={summary.total_work_hours} />
                    <Card label="Overtime" value={summary.total_overtime_hours} />
                </div>
                <div className="bg-white shadow rounded overflow-hidden">
                    <table className="min-w-full divide-y">
                        <thead className="bg-gray-50"><tr><th className="px-4 py-2 text-left">Employee</th><th className="px-4 py-2 text-left">Date</th><th className="px-4 py-2 text-left">Check In</th><th className="px-4 py-2 text-left">Check Out</th><th className="px-4 py-2 text-left">Work</th><th className="px-4 py-2 text-left">Overtime</th><th className="px-4 py-2 text-left">Status</th></tr></thead>
                        <tbody className="divide-y">
                            {rows.map((row) => (
                                <tr key={row.id}>
                                    <td className="px-4 py-2">{row.employee_id} - {row.employee_name}</td>
                                    <td className="px-4 py-2">{row.attendance_date}</td>
                                    <td className="px-4 py-2">{row.check_in_at || '-'}</td>
                                    <td className="px-4 py-2">{row.check_out_at || '-'}</td>
                                    <td className="px-4 py-2">{row.work_hours}</td>
                                    <td className="px-4 py-2">{row.overtime_hours}</td>
                                    <td className="px-4 py-2">{row.status}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
