import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Show({ auth, invoice }) {
    const handleSend = () => {
        if (confirm('Kirim invoice ke customer?')) {
            router.post(route('sales-invoices.send', invoice.id));
        }
    };

    const handleCancel = () => {
        const reason = prompt('Alasan pembatalan:');
        if (reason) {
            router.post(route('sales-invoices.cancel', invoice.id), {
                cancellation_reason: reason,
            });
        }
    };

    const getStatusBadge = (status) => {
        const badges = {
            draft: <span className="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">Draft</span>,
            sent: <span className="px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full">Sent</span>,
            partially_paid: <span className="px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded-full">Partially Paid</span>,
            paid: <span className="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Paid</span>,
            overdue: <span className="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Overdue</span>,
            cancelled: <span className="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-200 rounded-full">Cancelled</span>,
        };
        return badges[status] || status;
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Invoice Detail</h2>}
        >
            <Head title={`Invoice ${invoice.invoice_number}`} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex items-center justify-between mb-6">
                                <div>
                                    <h3 className="text-2xl font-bold">{invoice.invoice_number}</h3>
                                    <p className="text-gray-600">Status: {getStatusBadge(invoice.status)}</p>
                                </div>
                                <div className="flex gap-2">
                                    <Link href={route('sales-invoices.index')} className="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                                        Kembali
                                    </Link>
                                    {invoice.status === 'draft' && (
                                        <>
                                            <Link href={route('sales-invoices.edit', invoice.id)} className="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700">
                                                Edit
                                            </Link>
                                            <button onClick={handleSend} className="px-4 py-2 text-white bg-green-600 rounded-md hover:bg-green-700">
                                                Kirim
                                            </button>
                                        </>
                                    )}
                                    {(invoice.status === 'sent' || invoice.status === 'overdue') && invoice.amount_paid === 0 && (
                                        <button onClick={handleCancel} className="px-4 py-2 text-white bg-red-600 rounded-md hover:bg-red-700">
                                            Batalkan
                                        </button>
                                    )}
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-6 mb-6">
                                <div>
                                    <h4 className="mb-2 font-semibold">Customer</h4>
                                    <p>{invoice.customer?.name}</p>
                                    {invoice.billing_address && <p className="text-sm text-gray-600">{invoice.billing_address}</p>}
                                </div>
                                <div>
                                    <h4 className="mb-2 font-semibold">Invoice Info</h4>
                                    <p>Tanggal: {invoice.invoice_date}</p>
                                    <p>Jatuh Tempo: {invoice.due_date}</p>
                                    {invoice.tax_invoice_number && <p>No. Faktur Pajak: {invoice.tax_invoice_number}</p>}
                                </div>
                            </div>

                            <div className="mb-6">
                                <h4 className="mb-2 font-semibold">Items</h4>
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-4 py-2 text-left">Product</th>
                                            <th className="px-4 py-2 text-right">Qty</th>
                                            <th className="px-4 py-2 text-right">Unit Price</th>
                                            <th className="px-4 py-2 text-right">Discount</th>
                                            <th className="px-4 py-2 text-right">Tax</th>
                                            <th className="px-4 py-2 text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {invoice.lines.map((line) => (
                                            <tr key={line.id}>
                                                <td className="px-4 py-2">{line.product_name}</td>
                                                <td className="px-4 py-2 text-right">{line.quantity} {line.unit}</td>
                                                <td className="px-4 py-2 text-right">Rp {new Intl.NumberFormat('id-ID').format(line.unit_price)}</td>
                                                <td className="px-4 py-2 text-right">Rp {new Intl.NumberFormat('id-ID').format(line.discount_amount)}</td>
                                                <td className="px-4 py-2 text-right">Rp {new Intl.NumberFormat('id-ID').format(line.tax_amount)}</td>
                                                <td className="px-4 py-2 text-right">Rp {new Intl.NumberFormat('id-ID').format(line.line_total)}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            <div className="flex justify-end">
                                <div className="w-64">
                                    <div className="flex justify-between py-2">
                                        <span>Subtotal:</span>
                                        <span>Rp {new Intl.NumberFormat('id-ID').format(invoice.subtotal)}</span>
                                    </div>
                                    <div className="flex justify-between py-2">
                                        <span>Discount:</span>
                                        <span>Rp {new Intl.NumberFormat('id-ID').format(invoice.discount_amount)}</span>
                                    </div>
                                    <div className="flex justify-between py-2">
                                        <span>Tax (PPN 11%):</span>
                                        <span>Rp {new Intl.NumberFormat('id-ID').format(invoice.tax_amount)}</span>
                                    </div>
                                    <div className="flex justify-between py-2 font-bold border-t">
                                        <span>Grand Total:</span>
                                        <span>Rp {new Intl.NumberFormat('id-ID').format(invoice.grand_total)}</span>
                                    </div>
                                    <div className="flex justify-between py-2">
                                        <span>Amount Paid:</span>
                                        <span>Rp {new Intl.NumberFormat('id-ID').format(invoice.amount_paid)}</span>
                                    </div>
                                    <div className="flex justify-between py-2 font-bold text-red-600">
                                        <span>Amount Due:</span>
                                        <span>Rp {new Intl.NumberFormat('id-ID').format(invoice.amount_due)}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
