import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Create({ auth, employees, leaveTypes }) {
    const { data, setData, post, processing, errors } = useForm({
        employee_id: employees[0]?.id ?? '',
        leave_type: leaveTypes[0] ?? 'annual',
        start_date: new Date().toISOString().slice(0, 10),
        end_date: new Date().toISOString().slice(0, 10),
        reason: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('leave-requests.store'));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Create Leave Request" />
            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white shadow-sm sm:rounded-lg p-6">
                        <h1 className="text-2xl font-semibold mb-6">Create Leave Request</h1>
                        <form onSubmit={submit} className="space-y-4">
                            <div>
                                <label className="block text-sm mb-1">Employee</label>
                                <select value={data.employee_id} onChange={(e) => setData('employee_id', e.target.value)} className="w-full border rounded px-3 py-2">
                                    {employees.map((employee) => (
                                        <option key={employee.id} value={employee.id}>{employee.employee_id} - {employee.full_name}</option>
                                    ))}
                                </select>
                                {errors.employee_id && <p className="text-red-600 text-sm">{errors.employee_id}</p>}
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label className="block text-sm mb-1">Leave Type</label>
                                    <select value={data.leave_type} onChange={(e) => setData('leave_type', e.target.value)} className="w-full border rounded px-3 py-2">
                                        {leaveTypes.map((type) => <option key={type} value={type}>{type}</option>)}
                                    </select>
                                    {errors.leave_type && <p className="text-red-600 text-sm">{errors.leave_type}</p>}
                                </div>
                                <div>
                                    <label className="block text-sm mb-1">Start Date</label>
                                    <input type="date" value={data.start_date} onChange={(e) => setData('start_date', e.target.value)} className="w-full border rounded px-3 py-2" />
                                    {errors.start_date && <p className="text-red-600 text-sm">{errors.start_date}</p>}
                                </div>
                                <div>
                                    <label className="block text-sm mb-1">End Date</label>
                                    <input type="date" value={data.end_date} onChange={(e) => setData('end_date', e.target.value)} className="w-full border rounded px-3 py-2" />
                                    {errors.end_date && <p className="text-red-600 text-sm">{errors.end_date}</p>}
                                </div>
                            </div>

                            <div>
                                <label className="block text-sm mb-1">Reason</label>
                                <textarea value={data.reason} onChange={(e) => setData('reason', e.target.value)} className="w-full border rounded px-3 py-2" rows={3} />
                                {errors.reason && <p className="text-red-600 text-sm">{errors.reason}</p>}
                            </div>

                            <button disabled={processing} className="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
