import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Show({ auth, assignment }) {
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Employee Assignment Detail" />
            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white shadow-sm sm:rounded-lg p-6">
                        <div className="flex justify-between items-center mb-6">
                            <h1 className="text-2xl font-semibold">Employee Assignment Detail</h1>
                            <div className="space-x-2">
                                <Link href={route('employee-assignments.edit', assignment.id)} className="bg-indigo-600 text-white px-4 py-2 rounded">Edit</Link>
                                <Link href={route('employee-assignments.index')} className="bg-gray-200 px-4 py-2 rounded">Back</Link>
                            </div>
                        </div>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div><span className="font-medium">Employee:</span> {assignment.employee?.employee_id} - {assignment.employee?.full_name}</div>
                            <div><span className="font-medium">Branch:</span> {assignment.branch?.code} - {assignment.branch?.name}</div>
                            <div><span className="font-medium">Department:</span> {assignment.department || '-'}</div>
                            <div><span className="font-medium">Position:</span> {assignment.position || '-'}</div>
                            <div><span className="font-medium">Effective Date:</span> {assignment.effective_date}</div>
                            <div><span className="font-medium">Status:</span> {assignment.status}</div>
                        </div>
                        <div className="mt-4 text-sm">
                            <div className="font-medium mb-1">Notes</div>
                            <div className="border rounded p-3 bg-gray-50">{assignment.notes || '-'}</div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
