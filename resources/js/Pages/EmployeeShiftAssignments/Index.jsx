import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, assignments, shifts, statuses, filters }) {
    const updateFilter = (key, value) => {
        router.get(route('employee-shift-assignments.index'), { ...filters, [key]: value }, { preserveState: true, replace: true });
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Employee Shift Assignments</h2>}>
            <Head title="Employee Shift Assignments" />
            <div className="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div className="flex justify-between items-center">
                    <div className="flex gap-2">
                        <input className="border rounded px-3 py-2" placeholder="Search employee" value={filters.search || ''} onChange={(e) => updateFilter('search', e.target.value)} />
                        <select className="border rounded px-3 py-2" value={filters.status || ''} onChange={(e) => updateFilter('status', e.target.value)}>
                            <option value="">All Status</option>
                            {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
                        </select>
                        <select className="border rounded px-3 py-2" value={filters.work_shift_id || ''} onChange={(e) => updateFilter('work_shift_id', e.target.value)}>
                            <option value="">All Shift</option>
                            {shifts.map((shift) => <option key={shift.id} value={shift.id}>{shift.shift_code} - {shift.name}</option>)}
                        </select>
                    </div>
                    <Link href={route('employee-shift-assignments.create')} className="px-4 py-2 bg-indigo-600 text-white rounded">Tambah</Link>
                </div>
                <div className="bg-white shadow rounded overflow-hidden">
                    <table className="min-w-full divide-y">
                        <thead className="bg-gray-50"><tr><th className="px-4 py-2 text-left">Employee</th><th className="px-4 py-2 text-left">Shift</th><th className="px-4 py-2 text-left">Effective</th><th className="px-4 py-2 text-left">Status</th><th className="px-4 py-2 text-left">Aksi</th></tr></thead>
                        <tbody className="divide-y">
                            {assignments.data.map((assignment) => (
                                <tr key={assignment.id}>
                                    <td className="px-4 py-2">{assignment.employee?.employee_id} - {assignment.employee?.full_name}</td>
                                    <td className="px-4 py-2">{assignment.shift?.shift_code} - {assignment.shift?.name}</td>
                                    <td className="px-4 py-2">{assignment.effective_date}</td>
                                    <td className="px-4 py-2">{assignment.status}</td>
                                    <td className="px-4 py-2 space-x-2">
                                        <Link className="text-indigo-600" href={route('employee-shift-assignments.show', assignment.id)}>Detail</Link>
                                        <Link className="text-blue-600" href={route('employee-shift-assignments.edit', assignment.id)}>Edit</Link>
                                        {assignment.status === 'active' && <button className="text-red-600" onClick={() => router.delete(route('employee-shift-assignments.destroy', assignment.id))}>Nonaktif</button>}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
