import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, documents, filters, statuses, documentTypes }) {
    const submit = (e) => {
        e.preventDefault();
        const form = new FormData(e.target);
        router.get(route('employee-documents.index'), Object.fromEntries(form.entries()));
    };

    const deactivate = (id) => {
        if (confirm('Nonaktifkan dokumen?')) router.delete(route('employee-documents.destroy', id));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Employee Documents" />
            <div className="py-12"><div className="max-w-7xl mx-auto sm:px-6 lg:px-8"><div className="bg-white shadow-sm sm:rounded-lg p-6">
                <div className="flex justify-between items-center mb-6">
                    <h1 className="text-2xl font-semibold">Employee Documents</h1>
                    <Link href={route('employee-documents.create')} className="bg-blue-600 text-white px-4 py-2 rounded">Add Document</Link>
                </div>
                <form onSubmit={submit} className="grid grid-cols-1 md:grid-cols-6 gap-3 mb-6">
                    <input name="search" defaultValue={filters.search || ''} placeholder="Search" className="border rounded px-3 py-2" />
                    <select name="document_type" defaultValue={filters.document_type || ''} className="border rounded px-3 py-2"><option value="">All Type</option>{documentTypes.map((type) => <option key={type} value={type}>{type}</option>)}</select>
                    <select name="status" defaultValue={filters.status || ''} className="border rounded px-3 py-2"><option value="">All Status</option>{statuses.map((status) => <option key={status} value={status}>{status}</option>)}</select>
                    <input type="date" name="expiry_from" defaultValue={filters.expiry_from || ''} className="border rounded px-3 py-2" />
                    <input type="date" name="expiry_to" defaultValue={filters.expiry_to || ''} className="border rounded px-3 py-2" />
                    <button className="bg-gray-700 text-white px-4 py-2 rounded">Filter</button>
                </form>
                <table className="min-w-full text-sm"><thead><tr className="border-b"><th className="text-left py-2">Employee</th><th className="text-left py-2">Type</th><th className="text-left py-2">Number</th><th className="text-left py-2">Issue</th><th className="text-left py-2">Expiry</th><th className="text-left py-2">Status</th><th className="text-right py-2">Action</th></tr></thead>
                    <tbody>{documents.data.map((doc) => <tr key={doc.id} className="border-b"><td className="py-2">{doc.employee?.employee_id} - {doc.employee?.full_name}</td><td className="py-2">{doc.document_type}</td><td className="py-2">{doc.document_number}</td><td className="py-2">{doc.issue_date}</td><td className="py-2">{doc.expiry_date || '-'}</td><td className="py-2">{doc.status}</td><td className="py-2 text-right space-x-2"><Link href={route('employee-documents.show', doc.id)} className="text-blue-600">View</Link><Link href={route('employee-documents.edit', doc.id)} className="text-indigo-600">Edit</Link>{doc.status === 'active' && <button onClick={() => deactivate(doc.id)} className="text-red-600">Deactivate</button>}</td></tr>)}</tbody>
                </table>
            </div></div></div>
        </AuthenticatedLayout>
    );
}
