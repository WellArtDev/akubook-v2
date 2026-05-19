import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Show({ auth, employee }) {
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Employee Detail" />
            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
                    <div className="bg-white shadow-sm sm:rounded-lg p-6">
                        <div className="flex justify-between items-center mb-6">
                            <h1 className="text-2xl font-semibold">Employee Detail</h1>
                            <div className="space-x-2">
                                <Link href={route('employees.edit', employee.id)} className="bg-indigo-600 text-white px-4 py-2 rounded">Edit</Link>
                                <Link href={route('employees.index')} className="bg-gray-200 px-4 py-2 rounded">Back</Link>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div><span className="font-medium">Employee ID:</span> {employee.employee_id}</div>
                            <div><span className="font-medium">Full Name:</span> {employee.full_name}</div>
                            <div><span className="font-medium">Email:</span> {employee.email}</div>
                            <div><span className="font-medium">Phone:</span> {employee.phone || '-'}</div>
                            <div><span className="font-medium">Join Date:</span> {employee.join_date}</div>
                            <div><span className="font-medium">Status:</span> {employee.employment_status}</div>
                            <div><span className="font-medium">Department:</span> {employee.department || '-'}</div>
                            <div><span className="font-medium">Position:</span> {employee.position || '-'}</div>
                        </div>

                        <div className="mt-4 text-sm">
                            <div className="font-medium mb-1">Notes</div>
                            <div className="border rounded p-3 bg-gray-50">{employee.notes || '-'}</div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
