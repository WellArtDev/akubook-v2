import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export function DocumentForm({ document, employees, documentTypes, statuses }) {
    const { data, setData, post, put, processing, errors } = useForm({
        employee_id: document?.employee_id ?? employees[0]?.id ?? '',
        document_type: document?.document_type ?? documentTypes[0] ?? 'id_card',
        document_number: document?.document_number ?? '',
        issue_date: document?.issue_date ?? new Date().toISOString().slice(0, 10),
        expiry_date: document?.expiry_date ?? '',
        status: document?.status ?? 'active',
        notes: document?.notes ?? '',
    });

    const submit = (e) => {
        e.preventDefault();
        if (document) put(route('employee-documents.update', document.id)); else post(route('employee-documents.store'));
    };

    return (
        <form onSubmit={submit} className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label className="block text-sm mb-1">Employee</label><select value={data.employee_id} onChange={(e) => setData('employee_id', e.target.value)} className="w-full border rounded px-3 py-2">{employees.map((employee) => <option key={employee.id} value={employee.id}>{employee.employee_id} - {employee.full_name}</option>)}</select>{errors.employee_id && <p className="text-red-600 text-sm">{errors.employee_id}</p>}</div>
                <div><label className="block text-sm mb-1">Document Type</label><select value={data.document_type} onChange={(e) => setData('document_type', e.target.value)} className="w-full border rounded px-3 py-2">{documentTypes.map((type) => <option key={type} value={type}>{type}</option>)}</select>{errors.document_type && <p className="text-red-600 text-sm">{errors.document_type}</p>}</div>
                <div><label className="block text-sm mb-1">Document Number</label><input value={data.document_number} onChange={(e) => setData('document_number', e.target.value)} className="w-full border rounded px-3 py-2" />{errors.document_number && <p className="text-red-600 text-sm">{errors.document_number}</p>}</div>
                <div><label className="block text-sm mb-1">Status</label><select value={data.status} onChange={(e) => setData('status', e.target.value)} className="w-full border rounded px-3 py-2">{statuses.map((status) => <option key={status} value={status}>{status}</option>)}</select>{errors.status && <p className="text-red-600 text-sm">{errors.status}</p>}</div>
                <div><label className="block text-sm mb-1">Issue Date</label><input type="date" value={data.issue_date} onChange={(e) => setData('issue_date', e.target.value)} className="w-full border rounded px-3 py-2" />{errors.issue_date && <p className="text-red-600 text-sm">{errors.issue_date}</p>}</div>
                <div><label className="block text-sm mb-1">Expiry Date</label><input type="date" value={data.expiry_date || ''} onChange={(e) => setData('expiry_date', e.target.value)} className="w-full border rounded px-3 py-2" />{errors.expiry_date && <p className="text-red-600 text-sm">{errors.expiry_date}</p>}</div>
            </div>
            <div><label className="block text-sm mb-1">Notes</label><textarea value={data.notes} onChange={(e) => setData('notes', e.target.value)} className="w-full border rounded px-3 py-2" rows={3} />{errors.notes && <p className="text-red-600 text-sm">{errors.notes}</p>}</div>
            <button disabled={processing} className="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
        </form>
    );
}

export default function Create({ auth, employees, documentTypes, statuses }) {
    return <AuthenticatedLayout user={auth.user}><Head title="Create Employee Document" /><div className="py-12"><div className="max-w-4xl mx-auto sm:px-6 lg:px-8"><div className="bg-white shadow-sm sm:rounded-lg p-6"><h1 className="text-2xl font-semibold mb-6">Create Employee Document</h1><DocumentForm employees={employees} documentTypes={documentTypes} statuses={statuses} /></div></div></div></AuthenticatedLayout>;
}
