import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, employees, filters, statuses }) {
    const submit = (e) => {
        e.preventDefault();
        const form = new FormData(e.target);
        router.get(route('employees.index'), {
            search: form.get('search') || '',
            employment_status: form.get('employment_status') || '',
        });
    };

    const deactivate = (id) => {
        if (confirm('Nonaktifkan employee?')) {
            router.delete(route('employees.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Employees" />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white shadow-sm sm:rounded-lg p-6">
                        <div className="flex justify-between items-center mb-6">
                            <h1 className="text-2xl font-semibold">Employees</h1>
                            <Link href={route('employees.create')} className="bg-blue-600 text-white px-4 py-2 rounded">Add Employee</Link>
                        </div>

                        <form onSubmit={submit} className="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
                            <input name="search" defaultValue={filters.search || ''} placeholder="Search" className="border rounded px-3 py-2" />
                            <select name="employment_status" defaultValue={filters.employment_status || ''} className="border rounded px-3 py-2">
                                <option value="">All Status</option>
                                {statuses.map((status) => (
                                    <option key={status} value={status}>{status}</option>
                                ))}
                            </select>
                            <button className="bg-gray-700 text-white px-4 py-2 rounded">Filter</button>
                        </form>

                        <table className="min-w-full text-sm">
                            <thead>
                                <tr className="border-b">
                                    <th className="text-left py-2">Employee ID</th>
                                    <th className="text-left py-2">Name</th>
                                    <th className="text-left py-2">Email</th>
                                    <th className="text-left py-2">Department</th>
                                    <th className="text-left py-2">Position</th>
                                    <th className="text-left py-2">Join Date</th>
                                    <th className="text-left py-2">Status</th>
                                    <th className="text-right py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {employees.data.map((employee) => (
                                    <tr key={employee.id} className="border-b">
                                        <td className="py-2">{employee.employee_id}</td>
                                        <td className="py-2">{employee.full_name}</td>
                                        <td className="py-2">{employee.email}</td>
                                        <td className="py-2">{employee.department || '-'}</td>
                                        <td className="py-2">{employee.position || '-'}</td>
                                        <td className="py-2">{employee.join_date}</td>
                                        <td className="py-2">{employee.employment_status}</td>
                                        <td className="py-2 text-right space-x-2">
                                            <Link href={route('employees.show', employee.id)} className="text-blue-600">View</Link>
                                            <Link href={route('employees.edit', employee.id)} className="text-indigo-600">Edit</Link>
                                            {employee.employment_status === 'active' && (
                                                <button onClick={() => deactivate(employee.id)} className="text-red-600">Deactivate</button>
                                            )}
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
