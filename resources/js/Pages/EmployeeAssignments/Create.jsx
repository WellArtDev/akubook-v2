import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export function AssignmentForm({ assignment, employees, branches, statuses }) {
    const { data, setData, post, put, processing, errors } = useForm({
        employee_id: assignment?.employee_id ?? '',
        branch_id: assignment?.branch_id ?? '',
        department: assignment?.department ?? '',
        position: assignment?.position ?? '',
        effective_date: assignment?.effective_date ?? new Date().toISOString().slice(0, 10),
        status: assignment?.status ?? 'active',
        notes: assignment?.notes ?? '',
    });

    const submit = (e) => {
        e.preventDefault();
        if (assignment) {
            put(route('employee-assignments.update', assignment.id));
            return;
        }
        post(route('employee-assignments.store'));
    };

    return (
        <form onSubmit={submit} className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label className="block text-sm mb-1">Employee</label>
                    <select value={data.employee_id} onChange={(e) => setData('employee_id', e.target.value)} className="w-full border rounded px-3 py-2">
                        <option value="">Select Employee</option>
                        {employees.map((employee) => <option key={employee.id} value={employee.id}>{employee.employee_id} - {employee.full_name}</option>)}
                    </select>
                    {errors.employee_id && <p className="text-red-600 text-sm">{errors.employee_id}</p>}
                </div>
                <div>
                    <label className="block text-sm mb-1">Branch</label>
                    <select value={data.branch_id} onChange={(e) => setData('branch_id', e.target.value)} className="w-full border rounded px-3 py-2">
                        <option value="">Select Branch</option>
                        {branches.map((branch) => <option key={branch.id} value={branch.id}>{branch.code} - {branch.name}</option>)}
                    </select>
                    {errors.branch_id && <p className="text-red-600 text-sm">{errors.branch_id}</p>}
                </div>
                <div>
                    <label className="block text-sm mb-1">Department</label>
                    <input value={data.department} onChange={(e) => setData('department', e.target.value)} className="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label className="block text-sm mb-1">Position</label>
                    <input value={data.position} onChange={(e) => setData('position', e.target.value)} className="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label className="block text-sm mb-1">Effective Date</label>
                    <input type="date" value={data.effective_date} onChange={(e) => setData('effective_date', e.target.value)} className="w-full border rounded px-3 py-2" />
                    {errors.effective_date && <p className="text-red-600 text-sm">{errors.effective_date}</p>}
                </div>
                <div>
                    <label className="block text-sm mb-1">Status</label>
                    <select value={data.status} onChange={(e) => setData('status', e.target.value)} className="w-full border rounded px-3 py-2">
                        {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
                    </select>
                </div>
            </div>
            <div>
                <label className="block text-sm mb-1">Notes</label>
                <textarea value={data.notes} onChange={(e) => setData('notes', e.target.value)} className="w-full border rounded px-3 py-2" rows={3} />
            </div>
            <button disabled={processing} className="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
        </form>
    );
}

export default function Create({ auth, employees, branches, statuses }) {
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Create Employee Assignment" />
            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white shadow-sm sm:rounded-lg p-6">
                        <h1 className="text-2xl font-semibold mb-6">Create Employee Assignment</h1>
                        <AssignmentForm employees={employees} branches={branches} statuses={statuses} />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
