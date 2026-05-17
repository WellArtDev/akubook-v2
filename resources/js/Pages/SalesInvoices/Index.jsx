import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, invoices, filters }) {
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

    const handleDelete = (invoice) => {
        if (confirm(`Hapus Invoice ${invoice.invoice_number}?`)) {
            router.delete(route('sales-invoices.destroy', invoice.id));
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Sales Invoices</h2>}
        >
            <Head title="Sales Invoices" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex items-center justify-between mb-6">
                                <Link
                                    href={route('sales-invoices.create')}
                                    className="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700"
                                >
                                    Buat Invoice
                                </Link>
                            </div>

                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Invoice Number</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Date</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Customer</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Grand Total</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Amount Due</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Status</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {invoices.data.map((invoice) => (
                                            <tr key={invoice.id}>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <Link href={route('sales-invoices.show', invoice.id)} className="text-blue-600 hover:text-blue-900">
                                                        {invoice.invoice_number}
                                                    </Link>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">{invoice.invoice_date}</td>
                                                <td className="px-6 py-4">{invoice.customer?.name}</td>
                                                <td className="px-6 py-4 text-right whitespace-nowrap">
                                                    Rp {new Intl.NumberFormat('id-ID').format(invoice.grand_total)}
                                                </td>
                                                <td className="px-6 py-4 text-right whitespace-nowrap">
                                                    Rp {new Intl.NumberFormat('id-ID').format(invoice.amount_due)}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">{getStatusBadge(invoice.status)}</td>
                                                <td className="px-6 py-4 text-right whitespace-nowrap">
                                                    <div className="flex justify-end gap-2">
                                                        {invoice.status === 'draft' && (
                                                            <>
                                                                <Link href={route('sales-invoices.edit', invoice.id)} className="text-blue-600 hover:text-blue-900">
                                                                    Edit
                                                                </Link>
                                                                <button onClick={() => handleDelete(invoice)} className="text-red-600 hover:text-red-900">
                                                                    Hapus
                                                                </button>
                                                            </>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            {invoices.links.length > 3 && (
                                <div className="flex justify-center mt-6 gap-2">
                                    {invoices.links.map((link, index) => (
                                        <Link
                                            key={index}
                                            href={link.url || '#'}
                                            className={`px-3 py-1 rounded ${
                                                link.active ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                            } ${!link.url && 'opacity-50 cursor-not-allowed'}`}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
