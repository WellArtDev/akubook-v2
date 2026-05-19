import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export function EmployeeForm({ employee, statuses = ['active', 'inactive', 'resigned'], onSubmit }) {
    const { data, setData, post, put, processing, errors } = useForm({
        employee_id: employee?.employee_id ?? '',
        full_name: employee?.full_name ?? '',
        email: employee?.email ?? '',
        phone: employee?.phone ?? '',
        join_date: employee?.join_date ?? new Date().toISOString().slice(0, 10),
        employment_status: employee?.employment_status ?? 'active',
        department: employee?.department ?? '',
        position: employee?.position ?? '',
        notes: employee?.notes ?? '',
    });

    const submit = (e) => {
        e.preventDefault();
        if (employee) {
            put(route('employees.update', employee.id));
            return;
        }
        post(route('employees.store'));
    };

    return (
        <form onSubmit={onSubmit ?? submit} className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label className="block text-sm mb-1">Employee ID</label>
                    <input value={data.employee_id} onChange={(e) => setData('employee_id', e.target.value)} className="w-full border rounded px-3 py-2" />
                    {errors.employee_id && <p className="text-red-600 text-sm">{errors.employee_id}</p>}
                </div>
                <div>
                    <label className="block text-sm mb-1">Full Name</label>
                    <input value={data.full_name} onChange={(e) => setData('full_name', e.target.value)} className="w-full border rounded px-3 py-2" />
                    {errors.full_name && <p className="text-red-600 text-sm">{errors.full_name}</p>}
                </div>
                <div>
                    <label className="block text-sm mb-1">Email</label>
                    <input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} className="w-full border rounded px-3 py-2" />
                    {errors.email && <p className="text-red-600 text-sm">{errors.email}</p>}
                </div>
                <div>
                    <label className="block text-sm mb-1">Phone</label>
                    <input value={data.phone} onChange={(e) => setData('phone', e.target.value)} className="w-full border rounded px-3 py-2" />
                    {errors.phone && <p className="text-red-600 text-sm">{errors.phone}</p>}
                </div>
                <div>
                    <label className="block text-sm mb-1">Join Date</label>
                    <input type="date" value={data.join_date} onChange={(e) => setData('join_date', e.target.value)} className="w-full border rounded px-3 py-2" />
                    {errors.join_date && <p className="text-red-600 text-sm">{errors.join_date}</p>}
                </div>
                <div>
                    <label className="block text-sm mb-1">Employment Status</label>
                    <select value={data.employment_status} onChange={(e) => setData('employment_status', e.target.value)} className="w-full border rounded px-3 py-2">
                        {statuses.map((status) => (
                            <option key={status} value={status}>{status}</option>
                        ))}
                    </select>
                    {errors.employment_status && <p className="text-red-600 text-sm">{errors.employment_status}</p>}
                </div>
                <div>
                    <label className="block text-sm mb-1">Department</label>
                    <input value={data.department} onChange={(e) => setData('department', e.target.value)} className="w-full border rounded px-3 py-2" />
                    {errors.department && <p className="text-red-600 text-sm">{errors.department}</p>}
                </div>
                <div>
                    <label className="block text-sm mb-1">Position</label>
                    <input value={data.position} onChange={(e) => setData('position', e.target.value)} className="w-full border rounded px-3 py-2" />
                    {errors.position && <p className="text-red-600 text-sm">{errors.position}</p>}
                </div>
            </div>
            <div>
                <label className="block text-sm mb-1">Notes</label>
                <textarea value={data.notes} onChange={(e) => setData('notes', e.target.value)} className="w-full border rounded px-3 py-2" rows={3} />
                {errors.notes && <p className="text-red-600 text-sm">{errors.notes}</p>}
            </div>
            <button disabled={processing} className="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
        </form>
    );
}

export default function Create({ auth, statuses }) {
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Create Employee" />
            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white shadow-sm sm:rounded-lg p-6">
                        <h1 className="text-2xl font-semibold mb-6">Create Employee</h1>
                        <EmployeeForm statuses={statuses} />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
