import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, taxConfigurations, filters, taxTypes }) {
    const submit = (e) => { e.preventDefault(); router.get(route('tax-configurations.index'), Object.fromEntries(new FormData(e.target).entries())); };
    const destroy = (id) => { if (confirm('Hapus tax config?')) router.delete(route('tax-configurations.destroy', id)); };

    return <AuthenticatedLayout user={auth.user}><Head title="Tax Configurations" /><div className="py-12"><div className="max-w-7xl mx-auto sm:px-6 lg:px-8"><div className="bg-white shadow-sm sm:rounded-lg p-6">
        <div className="flex justify-between items-center mb-6"><h1 className="text-2xl font-semibold">Tax Configurations</h1><Link href={route('tax-configurations.create')} className="bg-blue-600 text-white px-4 py-2 rounded">Add Tax</Link></div>
        <form onSubmit={submit} className="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
            <input name="search" defaultValue={filters.search || ''} placeholder="Search" className="border rounded px-3 py-2" />
            <select name="tax_type" defaultValue={filters.tax_type || ''} className="border rounded px-3 py-2"><option value="">All Type</option>{Object.entries(taxTypes).map(([key, label]) => <option key={key} value={key}>{label}</option>)}</select>
            <select name="is_active" defaultValue={filters.is_active || ''} className="border rounded px-3 py-2"><option value="">All Status</option><option value="1">Active</option><option value="0">Inactive</option></select>
            <button className="bg-gray-700 text-white px-4 py-2 rounded">Filter</button>
        </form>
        <table className="min-w-full text-sm"><thead><tr className="border-b"><th className="text-left py-2">Code</th><th className="text-left py-2">Name</th><th className="text-left py-2">Type</th><th className="text-right py-2">Rate</th><th className="text-left py-2">Account</th><th className="text-left py-2">Default</th><th className="text-left py-2">Status</th><th className="text-right py-2">Actions</th></tr></thead><tbody>{taxConfigurations.data.map((tax) => <tr key={tax.id} className="border-b"><td className="py-2">{tax.code}</td><td className="py-2">{tax.name}</td><td className="py-2">{taxTypes[tax.tax_type]}</td><td className="py-2 text-right">{Number(tax.rate).toLocaleString()}%</td><td className="py-2">{tax.account?.code} - {tax.account?.name}</td><td className="py-2">{tax.is_default ? 'Yes' : 'No'}</td><td className="py-2">{tax.is_active ? 'Active' : 'Inactive'}</td><td className="py-2 text-right space-x-2"><Link href={route('tax-configurations.show', tax.id)} className="text-blue-600">View</Link><Link href={route('tax-configurations.edit', tax.id)} className="text-indigo-600">Edit</Link><button onClick={() => destroy(tax.id)} className="text-red-600">Delete</button></td></tr>)}</tbody></table>
    </div></div></div></AuthenticatedLayout>;
}
