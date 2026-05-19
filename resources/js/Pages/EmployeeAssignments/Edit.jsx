import React from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { AssignmentForm } from './Create';

export default function Edit({ auth, assignment, employees, branches, statuses }) {
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Edit Employee Assignment" />
            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white shadow-sm sm:rounded-lg p-6">
                        <h1 className="text-2xl font-semibold mb-6">Edit Employee Assignment</h1>
                        <AssignmentForm assignment={assignment} employees={employees} branches={branches} statuses={statuses} />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
