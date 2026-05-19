import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, vouchers, filters }) {
    const submit = (e) => {
        e.preventDefault();
        const form = new FormData(e.target);
        router.get(route('vouchers.index'), Object.fromEntries(form.entries()));
    };

    const destroy = (id) => {
        if (confirm('Hapus voucher?')) router.delete(route('vouchers.destroy', id));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Vouchers" />
            <div className="py-12"><div className="max-w-7xl mx-auto sm:px-6 lg:px-8"><div className="bg-white shadow-sm sm:rounded-lg p-6">
                <div className="flex justify-between items-center mb-6"><h1 className="text-2xl font-semibold">Payment & Receipt Vouchers</h1><Link href={route('vouchers.create')} className="bg-blue-600 text-white px-4 py-2 rounded">Add Voucher</Link></div>
                <form onSubmit={submit} className="grid grid-cols-1 md:grid-cols-6 gap-3 mb-6">
                    <input name="search" defaultValue={filters.search || ''} placeholder="Search number" className="border rounded px-3 py-2" />
                    <select name="voucher_type" defaultValue={filters.voucher_type || ''} className="border rounded px-3 py-2"><option value="">All Type</option><option value="payment">Payment</option><option value="receipt">Receipt</option></select>
                    <select name="status" defaultValue={filters.status || ''} className="border rounded px-3 py-2"><option value="">All Status</option><option value="draft">Draft</option><option value="posted">Posted</option><option value="cancelled">Cancelled</option></select>
                    <input name="date_from" type="date" defaultValue={filters.date_from || ''} className="border rounded px-3 py-2" />
                    <input name="date_to" type="date" defaultValue={filters.date_to || ''} className="border rounded px-3 py-2" />
                    <button className="bg-gray-700 text-white px-4 py-2 rounded">Filter</button>
                </form>
                <table className="min-w-full text-sm">
                    <thead><tr className="border-b"><th className="text-left py-2">Number</th><th className="text-left py-2">Date</th><th className="text-left py-2">Type</th><th className="text-left py-2">Status</th><th className="text-right py-2">Amount</th><th className="text-left py-2">Counterpart</th><th className="text-right py-2">Actions</th></tr></thead>
                    <tbody>{vouchers.data.map((voucher) => <tr key={voucher.id} className="border-b"><td className="py-2">{voucher.voucher_number}</td><td className="py-2">{voucher.voucher_date}</td><td className="py-2 capitalize">{voucher.voucher_type}</td><td className="py-2 capitalize">{voucher.status}</td><td className="py-2 text-right">{Number(voucher.amount).toLocaleString()}</td><td className="py-2">{voucher.counterpart_account?.code} - {voucher.counterpart_account?.name}</td><td className="py-2 text-right space-x-2"><Link href={route('vouchers.show', voucher.id)} className="text-blue-600">View</Link>{voucher.status === 'draft' && <button onClick={() => destroy(voucher.id)} className="text-red-600">Delete</button>}</td></tr>)}</tbody>
                </table>
            </div></div></div>
        </AuthenticatedLayout>
    );
}
