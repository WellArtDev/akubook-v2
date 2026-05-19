import React from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { DocumentForm } from './Create';

export default function Edit({ auth, document, employees, documentTypes, statuses }) {
    return <AuthenticatedLayout user={auth.user}><Head title="Edit Employee Document" /><div className="py-12"><div className="max-w-4xl mx-auto sm:px-6 lg:px-8"><div className="bg-white shadow-sm sm:rounded-lg p-6"><h1 className="text-2xl font-semibold mb-6">Edit Employee Document</h1><DocumentForm document={document} employees={employees} documentTypes={documentTypes} statuses={statuses} /></div></div></div></AuthenticatedLayout>;
}
