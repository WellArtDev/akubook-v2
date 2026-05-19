import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Show({ auth, document, isExpired }) {
    const deactivate = () => {
        if (confirm('Nonaktifkan dokumen?')) router.delete(route('employee-documents.destroy', document.id));
    };

    return <AuthenticatedLayout user={auth.user}><Head title="Employee Document Detail" /><div className="py-12"><div className="max-w-4xl mx-auto sm:px-6 lg:px-8"><div className="bg-white shadow-sm sm:rounded-lg p-6">
        <div className="flex justify-between items-center mb-6"><h1 className="text-2xl font-semibold">Employee Document Detail</h1><div className="space-x-2"><Link href={route('employee-documents.edit', document.id)} className="bg-indigo-600 text-white px-4 py-2 rounded">Edit</Link>{document.status === 'active' && <button onClick={deactivate} className="bg-red-600 text-white px-4 py-2 rounded">Deactivate</button>}<Link href={route('employee-documents.index')} className="bg-gray-200 px-4 py-2 rounded">Back</Link></div></div>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div><span className="font-medium">Employee:</span> {document.employee?.employee_id} - {document.employee?.full_name}</div>
            <div><span className="font-medium">Type:</span> {document.document_type}</div>
            <div><span className="font-medium">Number:</span> {document.document_number}</div>
            <div><span className="font-medium">Status:</span> {document.status}</div>
            <div><span className="font-medium">Issue Date:</span> {document.issue_date}</div>
            <div><span className="font-medium">Expiry Date:</span> {document.expiry_date || '-'}</div>
            <div><span className="font-medium">Expired:</span> {isExpired ? 'Yes' : 'No'}</div>
        </div>
        <div className="mt-4 text-sm"><div className="font-medium mb-1">Notes</div><div className="border rounded p-3 bg-gray-50">{document.notes || '-'}</div></div>
    </div></div></div></AuthenticatedLayout>;
}
