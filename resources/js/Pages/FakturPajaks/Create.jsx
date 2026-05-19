import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Create({ auth, salesInvoices }) {
    const { data, setData, post, processing, errors } = useForm({
        faktur_date: new Date().toISOString().slice(0, 10),
        sales_invoice_id: salesInvoices[0]?.id || '',
        notes: '',
    });

    const invoice = salesInvoices.find((row) => String(row.id) === String(data.sales_invoice_id));

    const submit = (e) => { e.preventDefault(); post(route('faktur-pajaks.store')); };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Buat Faktur Pajak" />
            <div className="py-12"><div className="max-w-4xl mx-auto sm:px-6 lg:px-8"><div className="bg-white shadow-sm sm:rounded-lg p-6">
                <div className="flex justify-between mb-6"><h1 className="text-2xl font-semibold">Buat Faktur Pajak</h1><Link href={route('faktur-pajaks.index')} className="text-blue-600">Back</Link></div>
                <form onSubmit={submit} className="space-y-4">
                    <div><label className="block text-sm mb-1">Tanggal Faktur</label><input type="date" value={data.faktur_date} onChange={(e) => setData('faktur_date', e.target.value)} className="w-full border rounded px-3 py-2" />{errors.faktur_date && <div className="text-red-600 text-sm">{errors.faktur_date}</div>}</div>
                    <div><label className="block text-sm mb-1">Sales Invoice</label><select value={data.sales_invoice_id} onChange={(e) => setData('sales_invoice_id', e.target.value)} className="w-full border rounded px-3 py-2"><option value="">Pilih Invoice</option>{salesInvoices.map((row) => <option key={row.id} value={row.id}>{row.invoice_number} - {row.customer?.name}</option>)}</select>{errors.sales_invoice_id && <div className="text-red-600 text-sm">{errors.sales_invoice_id}</div>}</div>
                    {invoice && <div className="grid grid-cols-3 gap-3 text-sm"><div className="border rounded p-3"><div className="text-gray-500">DPP</div><div className="font-semibold">{Number(invoice.subtotal - invoice.discount_amount).toLocaleString()}</div></div><div className="border rounded p-3"><div className="text-gray-500">PPN</div><div className="font-semibold">{Number(invoice.tax_amount).toLocaleString()}</div></div><div className="border rounded p-3"><div className="text-gray-500">Total</div><div className="font-semibold">{Number(invoice.grand_total).toLocaleString()}</div></div></div>}
                    <div><label className="block text-sm mb-1">Notes</label><textarea value={data.notes} onChange={(e) => setData('notes', e.target.value)} className="w-full border rounded px-3 py-2" /></div>
                    <button disabled={processing} className="bg-blue-600 text-white px-4 py-2 rounded">Simpan</button>
                </form>
            </div></div></div>
        </AuthenticatedLayout>
    );
}
