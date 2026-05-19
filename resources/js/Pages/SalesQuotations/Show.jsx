import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Show({ auth, quotation }) {
    const money = (value) => new Intl.NumberFormat('id-ID').format(value || 0);
    const badgeClass = {
        draft: 'bg-gray-100 text-gray-800',
        sent: 'bg-blue-100 text-blue-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
        expired: 'bg-yellow-100 text-yellow-800',
        converted: 'bg-purple-100 text-purple-800',
        revised: 'bg-orange-100 text-orange-800',
    };

    const action = (name, message) => {
        if (confirm(message)) {
            router.post(route(`sales-quotations.${name}`, quotation.id));
        }
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<div className="flex items-center justify-between"><h2 className="text-xl font-semibold leading-tight text-gray-800">Sales Quotation - {quotation.quotation_number}</h2><span className={`rounded-full px-3 py-1 text-sm font-semibold ${badgeClass[quotation.status] || 'bg-gray-100 text-gray-800'}`}>{quotation.status}</span></div>}>
            <Head title={`Sales Quotation - ${quotation.quotation_number}`} />
            <div className="py-12"><div className="mx-auto max-w-7xl sm:px-6 lg:px-8"><div className="bg-white p-6 shadow-sm sm:rounded-lg space-y-6">
                <div className="flex justify-end gap-2">
                    {quotation.can_edit && <Link href={route('sales-quotations.edit', quotation.id)} className="rounded bg-blue-600 px-4 py-2 text-white">Edit</Link>}
                    {quotation.can_send && <button onClick={() => action('send', `Kirim quotation ${quotation.quotation_number}?`)} className="rounded bg-indigo-600 px-4 py-2 text-white">Send</button>}
                    {quotation.status === 'sent' && <button onClick={() => action('approve', `Approve quotation ${quotation.quotation_number}?`)} className="rounded bg-green-600 px-4 py-2 text-white">Approve</button>}
                    {quotation.status === 'sent' && <button onClick={() => action('reject', `Reject quotation ${quotation.quotation_number}?`)} className="rounded bg-red-600 px-4 py-2 text-white">Reject</button>}
                    {quotation.can_revise && <button onClick={() => action('revise', `Buat revisi ${quotation.quotation_number}?`)} className="rounded bg-orange-600 px-4 py-2 text-white">Revise</button>}
                    <button onClick={() => action('duplicate', `Duplikat quotation ${quotation.quotation_number}?`)} className="rounded bg-gray-700 px-4 py-2 text-white">Duplicate</button>
                    {quotation.can_convert && <button onClick={() => action('convert', `Convert quotation ${quotation.quotation_number} ke sales order?`)} className="rounded bg-purple-600 px-4 py-2 text-white">Convert to SO</button>}
                    {quotation.can_edit && <button onClick={() => confirm(`Hapus quotation ${quotation.quotation_number}?`) && router.delete(route('sales-quotations.destroy', quotation.id))} className="rounded bg-red-700 px-4 py-2 text-white">Delete</button>}
                </div>
                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div className="space-y-1 text-sm"><p><span className="font-semibold">Quotation Date:</span> {quotation.quotation_date}</p><p><span className="font-semibold">Valid Until:</span> {quotation.valid_until}</p><p><span className="font-semibold">Customer:</span> {quotation.customer?.name}</p><p><span className="font-semibold">Contact:</span> {quotation.customer_contact?.name || '-'}</p><p><span className="font-semibold">Reference:</span> {quotation.reference || '-'}</p></div>
                    <div className="space-y-1 text-sm"><p><span className="font-semibold">Sales Person:</span> {quotation.sales_person?.name || '-'}</p><p><span className="font-semibold">Payment Terms:</span> {quotation.payment_terms || '-'}</p><p><span className="font-semibold">Delivery Terms:</span> {quotation.delivery_terms || '-'}</p><p><span className="font-semibold">Revision:</span> {quotation.revision_number}</p><p><span className="font-semibold">Original:</span> {quotation.original_quotation?.quotation_number || '-'}</p></div>
                </div>
                {quotation.notes && <div><h3 className="font-semibold">Notes</h3><p className="text-sm text-gray-700">{quotation.notes}</p></div>}
                <div className="overflow-x-auto"><table className="min-w-full divide-y divide-gray-200"><thead><tr><th className="px-3 py-2 text-left">#</th><th className="px-3 py-2 text-left">Item</th><th className="px-3 py-2 text-left">Description</th><th className="px-3 py-2 text-right">Qty</th><th className="px-3 py-2 text-left">Unit</th><th className="px-3 py-2 text-right">Price</th><th className="px-3 py-2 text-right">Discount</th><th className="px-3 py-2 text-right">Line Total</th></tr></thead><tbody>{quotation.lines.map((line) => <tr key={line.id} className="border-t"><td className="px-3 py-2">{line.line_number}</td><td className="px-3 py-2">{line.item?.name}</td><td className="px-3 py-2">{line.description || '-'}</td><td className="px-3 py-2 text-right">{line.quantity}</td><td className="px-3 py-2">{line.unit}</td><td className="px-3 py-2 text-right">Rp {money(line.unit_price)}</td><td className="px-3 py-2 text-right">Rp {money(line.discount_amount)}</td><td className="px-3 py-2 text-right">Rp {money(line.line_total)}</td></tr>)}</tbody></table></div>
                <div className="ml-auto w-72 space-y-1 text-sm"><div className="flex justify-between"><span>Subtotal</span><span>Rp {money(quotation.subtotal)}</span></div><div className="flex justify-between"><span>Discount</span><span>Rp {money(quotation.discount_amount)}</span></div><div className="flex justify-between"><span>Subtotal After Discount</span><span>Rp {money(quotation.subtotal_after_discount)}</span></div><div className="flex justify-between"><span>Tax</span><span>Rp {money(quotation.tax_amount)}</span></div><div className="flex justify-between border-t pt-2 text-lg font-bold"><span>Grand Total</span><span>Rp {money(quotation.grand_total)}</span></div></div>
                {quotation.revisions?.length > 0 && <div><h3 className="mb-2 font-semibold">Revisions</h3><div className="flex flex-wrap gap-2">{quotation.revisions.map((revision) => <Link key={revision.id} href={route('sales-quotations.show', revision.id)} className="rounded bg-gray-100 px-3 py-1 text-sm">{revision.quotation_number}</Link>)}</div></div>}
                <div><Link href={route('sales-quotations.index')} className="rounded bg-gray-200 px-4 py-2">Kembali</Link></div>
            </div></div></div>
        </AuthenticatedLayout>
    );
}
