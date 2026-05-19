import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Show({ auth, assignment }) {
    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Shift Assignment Detail</h2>}>
            <Head title="Shift Assignment Detail" />
            <div className="py-6 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div className="flex gap-2">
                    <Link href={route('employee-shift-assignments.index')} className="px-3 py-2 bg-gray-200 rounded">Kembali</Link>
                    <Link href={route('employee-shift-assignments.edit', assignment.id)} className="px-3 py-2 bg-indigo-600 text-white rounded">Edit</Link>
                    {assignment.status === 'active' && <button onClick={() => router.delete(route('employee-shift-assignments.destroy', assignment.id))} className="px-3 py-2 bg-red-600 text-white rounded">Nonaktif</button>}
                </div>
                <div className="bg-white rounded shadow p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><div className="text-sm text-gray-500">Employee</div><div>{assignment.employee?.employee_id} - {assignment.employee?.full_name}</div></div>
                    <div><div className="text-sm text-gray-500">Shift</div><div>{assignment.shift?.shift_code} - {assignment.shift?.name}</div></div>
                    <div><div className="text-sm text-gray-500">Effective Date</div><div>{assignment.effective_date}</div></div>
                    <div><div className="text-sm text-gray-500">Status</div><div>{assignment.status}</div></div>
                    <div className="md:col-span-2"><div className="text-sm text-gray-500">Notes</div><div>{assignment.notes || '-'}</div></div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
