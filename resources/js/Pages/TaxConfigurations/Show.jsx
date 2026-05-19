import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Show({ auth, taxConfiguration }) {
    const destroy = () => { if (confirm('Hapus tax config?')) router.delete(route('tax-configurations.destroy', taxConfiguration.id)); };
    return <AuthenticatedLayout user={auth.user}><Head title={`Tax ${taxConfiguration.code}`} /><div className="py-12"><div className="max-w-4xl mx-auto sm:px-6 lg:px-8"><div className="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
        <div className="flex justify-between items-center"><h1 className="text-2xl font-semibold">Tax {taxConfiguration.code}</h1><div className="space-x-2"><Link href={route('tax-configurations.index')} className="text-gray-600">Back</Link><Link href={route('tax-configurations.edit', taxConfiguration.id)} className="text-indigo-600">Edit</Link><button onClick={destroy} className="text-red-600">Delete</button></div></div>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <Info label="Code" value={taxConfiguration.code} />
            <Info label="Name" value={taxConfiguration.name} />
            <Info label="Type" value={taxConfiguration.tax_type} />
            <Info label="Rate" value={`${Number(taxConfiguration.rate).toLocaleString()}%`} />
            <Info label="Account" value={`${taxConfiguration.account?.code || '-'} - ${taxConfiguration.account?.name || '-'}`} />
            <Info label="Default" value={taxConfiguration.is_default ? 'Yes' : 'No'} />
            <Info label="Status" value={taxConfiguration.is_active ? 'Active' : 'Inactive'} />
        </div>
        <div><div className="text-sm font-medium text-gray-700">Description</div><div className="mt-1 text-sm text-gray-600">{taxConfiguration.description || '-'}</div></div>
    </div></div></div></AuthenticatedLayout>;
}

function Info({ label, value }) { return <div><div className="text-xs text-gray-500">{label}</div><div className="font-medium">{value}</div></div>; }
