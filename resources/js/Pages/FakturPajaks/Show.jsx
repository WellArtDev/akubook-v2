import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Show({ auth, faktur }) {
    const issue = () => { if (confirm('Issue Faktur Pajak?')) router.post(route('faktur-pajaks.issue', faktur.id)); };
    const cancel = () => { if (confirm('Cancel Faktur Pajak?')) router.post(route('faktur-pajaks.cancel', faktur.id)); };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={faktur.faktur_number} />
            <div className="py-12"><div className="max-w-5xl mx-auto sm:px-6 lg:px-8"><div className="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                <div className="flex justify-between"><div><h1 className="text-2xl font-semibold">{faktur.faktur_number}</h1><p className="text-sm text-gray-500">{faktur.status}</p></div><div className="space-x-2"><Link href={route('faktur-pajaks.index')} className="text-blue-600">Back</Link>{faktur.status === 'draft' && <button onClick={issue} className="bg-green-600 text-white px-3 py-2 rounded">Issue</button>}{faktur.status !== 'cancelled' && <button onClick={cancel} className="bg-red-600 text-white px-3 py-2 rounded">Cancel</button>}</div></div>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm"><div><div className="text-gray-500">Invoice</div><div>{faktur.sales_invoice?.invoice_number}</div></div><div><div className="text-gray-500">Customer</div><div>{faktur.customer?.name}</div></div><div><div className="text-gray-500">Tanggal Faktur</div><div>{faktur.faktur_date}</div></div><div><div className="text-gray-500">Created By</div><div>{faktur.creator?.name}</div></div></div>
                <div className="grid grid-cols-3 gap-3 text-sm"><div className="border rounded p-3"><div className="text-gray-500">DPP</div><div className="font-semibold">{Number(faktur.dpp).toLocaleString()}</div></div><div className="border rounded p-3"><div className="text-gray-500">PPN</div><div className="font-semibold">{Number(faktur.ppn_amount).toLocaleString()}</div></div><div className="border rounded p-3"><div className="text-gray-500">Total</div><div className="font-semibold">{Number(faktur.grand_total).toLocaleString()}</div></div></div>
                {faktur.notes && <div><div className="text-sm text-gray-500">Notes</div><div className="text-sm">{faktur.notes}</div></div>}
            </div></div></div>
        </AuthenticatedLayout>
    );
}
