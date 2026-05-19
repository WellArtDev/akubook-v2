import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, fakturs, filters }) {
    const submit = (e) => { e.preventDefault(); router.get(route('faktur-pajaks.index'), Object.fromEntries(new FormData(e.target).entries())); };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Faktur Pajak" />
            <div className="py-12"><div className="max-w-7xl mx-auto sm:px-6 lg:px-8"><div className="bg-white shadow-sm sm:rounded-lg p-6">
                <div className="flex justify-between items-center mb-6"><h1 className="text-2xl font-semibold">Faktur Pajak</h1><Link href={route('faktur-pajaks.create')} className="bg-blue-600 text-white px-4 py-2 rounded">Buat Faktur</Link></div>
                <form onSubmit={submit} className="grid grid-cols-1 md:grid-cols-5 gap-3 mb-6">
                    <input name="search" defaultValue={filters.search || ''} placeholder="Search" className="border rounded px-3 py-2" />
                    <select name="status" defaultValue={filters.status || ''} className="border rounded px-3 py-2"><option value="">All Status</option><option value="draft">Draft</option><option value="issued">Issued</option><option value="cancelled">Cancelled</option></select>
                    <input name="date_from" type="date" defaultValue={filters.date_from || ''} className="border rounded px-3 py-2" />
                    <input name="date_to" type="date" defaultValue={filters.date_to || ''} className="border rounded px-3 py-2" />
                    <button className="bg-gray-700 text-white px-4 py-2 rounded">Filter</button>
                </form>
                <table className="min-w-full text-sm"><thead><tr className="border-b"><th className="text-left py-2">No Faktur</th><th className="text-left py-2">Tanggal</th><th className="text-left py-2">Invoice</th><th className="text-left py-2">Customer</th><th className="text-right py-2">DPP</th><th className="text-right py-2">PPN</th><th className="text-left py-2">Status</th><th className="text-right py-2">Action</th></tr></thead><tbody>{fakturs.data.map((row) => <tr key={row.id} className="border-b"><td className="py-2">{row.faktur_number}</td><td className="py-2">{row.faktur_date}</td><td className="py-2">{row.sales_invoice?.invoice_number}</td><td className="py-2">{row.customer?.name}</td><td className="py-2 text-right">{Number(row.dpp).toLocaleString()}</td><td className="py-2 text-right">{Number(row.ppn_amount).toLocaleString()}</td><td className="py-2">{row.status}</td><td className="py-2 text-right"><Link href={route('faktur-pajaks.show', row.id)} className="text-blue-600">Detail</Link></td></tr>)}</tbody></table>
            </div></div></div>
        </AuthenticatedLayout>
    );
}
