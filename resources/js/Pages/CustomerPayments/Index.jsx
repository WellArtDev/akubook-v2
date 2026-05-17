import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, payments, filters }) {
    const getStatusBadge = (status) => {
        const badges = {
            draft: <span className="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">Draft</span>,
            posted: <span className="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Posted</span>,
            reconciled: <span className="px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full">Reconciled</span>,
            voided: <span className="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Voided</span>,
        };
        return badges[status] || status;
    };

    const getMethodLabel = (method) => {
        const labels = {
            cash: 'Cash',
            bank_transfer: 'Bank Transfer',
            check: 'Check',
            credit_card: 'Credit Card',
            giro: 'Giro',
        };
        return labels[method] || method;
    };

    const handleDelete = (payment) => {
        if (confirm(`Hapus Payment ${payment.payment_number}?`)) {
            router.delete(route('customer-payments.destroy', payment.id));
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Customer Payments</h2>}
        >
            <Head title="Customer Payments" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex items-center justify-between mb-6">
                                <Link
                                    href={route('customer-payments.create')}
                                    className="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700"
                                >
                                    Record Payment
                                </Link>
                            </div>

                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Payment Number</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Date</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Customer</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Method</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Total Amount</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Allocated</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Unapplied</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Status</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {payments.data.map((payment) => (
                                            <tr key={payment.id}>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <Link href={route('customer-payments.show', payment.id)} className="text-blue-600 hover:text-blue-900">
                                                        {payment.payment_number}
                                                    </Link>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">{payment.payment_date}</td>
                                                <td className="px-6 py-4">{payment.customer?.name}</td>
                                                <td className="px-6 py-4 whitespace-nowrap">{getMethodLabel(payment.payment_method)}</td>
                                                <td className="px-6 py-4 text-right whitespace-nowrap">
                                                    Rp {new Intl.NumberFormat('id-ID').format(payment.total_amount)}
                                                </td>
                                                <td className="px-6 py-4 text-right whitespace-nowrap">
                                                    Rp {new Intl.NumberFormat('id-ID').format(payment.allocated_amount)}
                                                </td>
                                                <td className="px-6 py-4 text-right whitespace-nowrap">
                                                    Rp {new Intl.NumberFormat('id-ID').format(payment.unapplied_amount)}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">{getStatusBadge(payment.status)}</td>
                                                <td className="px-6 py-4 text-right whitespace-nowrap">
                                                    <div className="flex justify-end gap-2">
                                                        {payment.status === 'draft' && (
                                                            <>
                                                                <Link href={route('customer-payments.edit', payment.id)} className="text-blue-600 hover:text-blue-900">
                                                                    Edit
                                                                </Link>
                                                                <button onClick={() => handleDelete(payment)} className="text-red-600 hover:text-red-900">
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

                            {payments.links.length > 3 && (
                                <div className="flex justify-center mt-6 gap-2">
                                    {payments.links.map((link, index) => (
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
