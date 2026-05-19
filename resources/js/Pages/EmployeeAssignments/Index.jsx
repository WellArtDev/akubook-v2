import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, assignments, branches, statuses, filters }) {
    const submit = (e) => {
        e.preventDefault();
        const form = new FormData(e.target);
        router.get(route('employee-assignments.index'), {
            search: form.get('search') || '',
            status: form.get('status') || '',
            branch_id: form.get('branch_id') || '',
        });
    };

    const deactivate = (id) => {
        if (confirm('Nonaktifkan assignment?')) {
            router.delete(route('employee-assignments.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Employee Assignments" />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white shadow-sm sm:rounded-lg p-6">
                        <div className="flex justify-between items-center mb-6">
                            <h1 className="text-2xl font-semibold">Employee Assignments</h1>
                            <Link href={route('employee-assignments.create')} className="bg-blue-600 text-white px-4 py-2 rounded">Add Assignment</Link>
                        </div>

                        <form onSubmit={submit} className="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
                            <input name="search" defaultValue={filters.search || ''} placeholder="Search employee" className="border rounded px-3 py-2" />
                            <select name="status" defaultValue={filters.status || ''} className="border rounded px-3 py-2">
                                <option value="">All Status</option>
                                {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
                            </select>
                            <select name="branch_id" defaultValue={filters.branch_id || ''} className="border rounded px-3 py-2">
                                <option value="">All Branches</option>
                                {branches.map((branch) => <option key={branch.id} value={branch.id}>{branch.code} - {branch.name}</option>)}
                            </select>
                            <button className="bg-gray-700 text-white px-4 py-2 rounded">Filter</button>
                        </form>

                        <table className="min-w-full text-sm">
                            <thead>
                                <tr className="border-b">
                                    <th className="text-left py-2">Employee</th>
                                    <th className="text-left py-2">Branch</th>
                                    <th className="text-left py-2">Department</th>
                                    <th className="text-left py-2">Position</th>
                                    <th className="text-left py-2">Effective</th>
                                    <th className="text-left py-2">Status</th>
                                    <th className="text-right py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {assignments.data.map((assignment) => (
                                    <tr key={assignment.id} className="border-b">
                                        <td className="py-2">{assignment.employee?.employee_id} - {assignment.employee?.full_name}</td>
                                        <td className="py-2">{assignment.branch?.code} - {assignment.branch?.name}</td>
                                        <td className="py-2">{assignment.department || '-'}</td>
                                        <td className="py-2">{assignment.position || '-'}</td>
                                        <td className="py-2">{assignment.effective_date}</td>
                                        <td className="py-2">{assignment.status}</td>
                                        <td className="py-2 text-right space-x-2">
                                            <Link href={route('employee-assignments.show', assignment.id)} className="text-blue-600">View</Link>
                                            <Link href={route('employee-assignments.edit', assignment.id)} className="text-indigo-600">Edit</Link>
                                            {assignment.status === 'active' && <button onClick={() => deactivate(assignment.id)} className="text-red-600">Deactivate</button>}
                                        </td>
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
