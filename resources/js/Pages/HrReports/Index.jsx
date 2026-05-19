import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

function Card({ title, value }) {
    return (
        <div className="bg-white rounded shadow p-4">
            <div className="text-sm text-gray-500">{title}</div>
            <div className="text-2xl font-semibold">{value}</div>
        </div>
    );
}

export default function Index({ auth, filters, generated_at, employee_summary, attendance_summary, leave_summary, overtime_summary, document_summary, employee_rows }) {
    const updateFilter = (key, value) => {
        router.get(route('hr-reports.index'), { ...filters, [key]: value }, { preserveState: true, replace: true });
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">HR Reports</h2>}>
            <Head title="HR Reports" />
            <div className="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div className="bg-white rounded shadow p-4 flex flex-wrap gap-3 items-end">
                    <div>
                        <label className="block text-sm">Date From</label>
                        <input type="date" className="border rounded px-3 py-2" value={filters.date_from || ''} onChange={(e) => updateFilter('date_from', e.target.value)} />
                    </div>
                    <div>
                        <label className="block text-sm">Date To</label>
                        <input type="date" className="border rounded px-3 py-2" value={filters.date_to || ''} onChange={(e) => updateFilter('date_to', e.target.value)} />
                    </div>
                    <div className="min-w-[260px]">
                        <label className="block text-sm">Employee Search</label>
                        <input className="w-full border rounded px-3 py-2" value={filters.search || ''} onChange={(e) => updateFilter('search', e.target.value)} placeholder="Employee ID / Name" />
                    </div>
                    <div className="text-sm text-gray-500 ml-auto">Generated: {generated_at}</div>
                </div>

                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <Card title="Active Employees" value={employee_summary.active_employees} />
                    <Card title="Inactive/Resigned" value={employee_summary.inactive_or_resigned} />
                    <Card title="With Active Assignment" value={employee_summary.with_active_assignment} />
                    <Card title="Present" value={attendance_summary.present_count} />
                    <Card title="Incomplete" value={attendance_summary.incomplete_count} />
                    <Card title="Absent" value={attendance_summary.absent_count} />
                    <Card title="Work Hours" value={attendance_summary.work_hours_total} />
                    <Card title="Leave Days" value={leave_summary.leave_days_total} />
                    <Card title="OT Approved Hours" value={overtime_summary.approved_hours_total} />
                    <Card title="Active Docs" value={document_summary.active_documents} />
                    <Card title="Expired Docs" value={document_summary.expired_documents} />
                    <Card title="Expiring Soon" value={document_summary.expiring_soon_documents} />
                </div>

                <div className="bg-white rounded shadow overflow-hidden">
                    <div className="px-4 py-3 border-b font-semibold">Employee Snapshot</div>
                    <table className="min-w-full divide-y">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-4 py-2 text-left text-xs">Employee ID</th>
                                <th className="px-4 py-2 text-left text-xs">Name</th>
                                <th className="px-4 py-2 text-left text-xs">Status</th>
                                <th className="px-4 py-2 text-left text-xs">Department</th>
                                <th className="px-4 py-2 text-left text-xs">Position</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {employee_rows.map((row, idx) => (
                                <tr key={idx}>
                                    <td className="px-4 py-2 text-sm">{row.employee_id}</td>
                                    <td className="px-4 py-2 text-sm">{row.full_name}</td>
                                    <td className="px-4 py-2 text-sm">{row.employment_status}</td>
                                    <td className="px-4 py-2 text-sm">{row.department || '-'}</td>
                                    <td className="px-4 py-2 text-sm">{row.position || '-'}</td>
                                </tr>
                            ))}
                            {employee_rows.length === 0 && (
                                <tr><td colSpan={5} className="px-4 py-6 text-center text-sm text-gray-500">No rows</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
