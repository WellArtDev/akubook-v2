import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Show({ auth, salesOrder }) {
    const getStatusBadge = (status) => {
        const badges = {
            draft: <span className="px-3 py-1 text-sm font-semibold text-gray-800 bg-gray-100 rounded-full">Draft</span>,
            pending_approval: <span className="px-3 py-1 text-sm font-semibold text-yellow-800 bg-yellow-100 rounded-full">Pending Approval</span>,
            approved: <span className="px-3 py-1 text-sm font-semibold text-green-800 bg-green-100 rounded-full">Approved</span>,
            in_progress: <span className="px-3 py-1 text-sm font-semibold text-blue-800 bg-blue-100 rounded-full">In Progress</span>,
            completed: <span className="px-3 py-1 text-sm font-semibold text-purple-800 bg-purple-100 rounded-full">Completed</span>,
            cancelled: <span className="px-3 py-1 text-sm font-semibold text-red-800 bg-red-100 rounded-full">Cancelled</span>,
        };
        return badges[status] || status;
    };

    const handleSubmitForApproval = () => {
        if (confirm(`Submit Sales Order ${salesOrder.so_number} untuk approval?`)) {
            router.post(route('sales-orders.submit-approval', salesOrder.id));
        }
    };

    const handleApprove = () => {
        if (confirm(`Approve Sales Order ${salesOrder.so_number}?`)) {
            router.post(route('sales-orders.approve', salesOrder.id));
        }
    };

    const handleDelete = () => {
        if (confirm(`Hapus Sales Order ${salesOrder.so_number}?`)) {
            router.delete(route('sales-orders.destroy', salesOrder.id));
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Sales Order - {salesOrder.so_number}
                    </h2>
                    {getStatusBadge(salesOrder.status)}
                </div>
            }
        >
            <Head title={`Sales Order - ${salesOrder.so_number}`} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {/* Actions */}
                            <div className="flex justify-end gap-2 mb-6">
                                {salesOrder.status === 'draft' && (
                                    <>
                                        <Link
                                            href={route('sales-orders.edit', salesOrder.id)}
                                            className="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700"
                                        >
                                            Edit
                                        </Link>
                                        {salesOrder.approval_required && (
                                            <button
                                                onClick={handleSubmitForApproval}
                                                className="px-4 py-2 text-white bg-yellow-600 rounded-md hover:bg-yellow-700"
                                            >
                                                Submit untuk Approval
                                            </button>
                                        )}
                                        <button
                                            onClick={handleDelete}
                                            className="px-4 py-2 text-white bg-red-600 rounded-md hover:bg-red-700"
                                        >
                                            Hapus
                                        </button>
                                    </>
                                )}
                                {salesOrder.status === 'pending_approval' && (
                                    <>
                                        <Link
                                            href={route('sales-orders.edit', salesOrder.id)}
                                            className="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            onClick={handleApprove}
                                            className="px-4 py-2 text-white bg-green-600 rounded-md hover:bg-green-700"
                                        >
                                            Approve
                                        </button>
                                    </>
                                )}
                            </div>

                            {/* Credit Limit Warning */}
                            {!salesOrder.credit_check_passed && (
                                <div className="p-4 mb-6 text-red-800 bg-red-100 border border-red-200 rounded-md">
                                    <p className="font-semibold">⚠️ Credit Limit Warning</p>
                                    <p className="text-sm">{salesOrder.credit_check_notes}</p>
                                </div>
                            )}

                            {/* Header Info */}
                            <div className="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2">
                                <div>
                                    <h3 className="mb-4 text-lg font-semibold text-gray-900">SO Information</h3>
                                    <dl className="space-y-2">
                                        <div className="flex">
                                            <dt className="w-40 text-sm font-medium text-gray-500">SO Number:</dt>
                                            <dd className="text-sm text-gray-900">{salesOrder.so_number}</dd>
                                        </div>
                                        <div className="flex">
                                            <dt className="w-40 text-sm font-medium text-gray-500">SO Date:</dt>
                                            <dd className="text-sm text-gray-900">{salesOrder.so_date}</dd>
                                        </div>
                                        <div className="flex">
                                            <dt className="w-40 text-sm font-medium text-gray-500">Customer PO:</dt>
                                            <dd className="text-sm text-gray-900">{salesOrder.customer_po_number || '-'}</dd>
                                        </div>
                                        <div className="flex">
                                            <dt className="w-40 text-sm font-medium text-gray-500">Requested Delivery:</dt>
                                            <dd className="text-sm text-gray-900">{salesOrder.requested_delivery_date || '-'}</dd>
                                        </div>
                                        <div className="flex">
                                            <dt className="w-40 text-sm font-medium text-gray-500">Sales Person:</dt>
                                            <dd className="text-sm text-gray-900">{salesOrder.sales_person?.name}</dd>
                                        </div>
                                        <div className="flex">
                                            <dt className="w-40 text-sm font-medium text-gray-500">Created By:</dt>
                                            <dd className="text-sm text-gray-900">{salesOrder.created_by?.name}</dd>
                                        </div>
                                        {salesOrder.approved_by && (
                                            <>
                                                <div className="flex">
                                                    <dt className="w-40 text-sm font-medium text-gray-500">Approved By:</dt>
                                                    <dd className="text-sm text-gray-900">{salesOrder.approved_by.name}</dd>
                                                </div>
                                                <div className="flex">
                                                    <dt className="w-40 text-sm font-medium text-gray-500">Approved At:</dt>
                                                    <dd className="text-sm text-gray-900">{salesOrder.approved_at}</dd>
                                                </div>
                                            </>
                                        )}
                                    </dl>
                                </div>

                                <div>
                                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Customer Information</h3>
                                    <dl className="space-y-2">
                                        <div className="flex">
                                            <dt className="w-40 text-sm font-medium text-gray-500">Customer:</dt>
                                            <dd className="text-sm text-gray-900">{salesOrder.customer?.name}</dd>
                                        </div>
                                        <div className="flex">
                                            <dt className="w-40 text-sm font-medium text-gray-500">Delivery Address:</dt>
                                            <dd className="text-sm text-gray-900">{salesOrder.delivery_address?.name || '-'}</dd>
                                        </div>
                                        <div className="flex">
                                            <dt className="w-40 text-sm font-medium text-gray-500">Payment Terms:</dt>
                                            <dd className="text-sm text-gray-900">{salesOrder.payment_terms || '-'}</dd>
                                        </div>
                                        <div className="flex">
                                            <dt className="w-40 text-sm font-medium text-gray-500">Delivery Terms:</dt>
                                            <dd className="text-sm text-gray-900">{salesOrder.delivery_terms || '-'}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>

                            {salesOrder.notes && (
                                <div className="mb-8">
                                    <h3 className="mb-2 text-lg font-semibold text-gray-900">Notes</h3>
                                    <p className="text-sm text-gray-700">{salesOrder.notes}</p>
                                </div>
                            )}

                            {/* Line Items */}
                            <div className="mb-8">
                                <h3 className="mb-4 text-lg font-semibold text-gray-900">Line Items</h3>
                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">#</th>
                                                <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Item</th>
                                                <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Description</th>
                                                <th className="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Qty</th>
                                                <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Unit</th>
                                                <th className="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Unit Price</th>
                                                <th className="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Discount</th>
                                                <th className="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Tax</th>
                                                <th className="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {salesOrder.lines.map((line) => (
                                                <tr key={line.id}>
                                                    <td className="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">{line.line_number}</td>
                                                    <td className="px-6 py-4 text-sm text-gray-900">{line.item?.name}</td>
                                                    <td className="px-6 py-4 text-sm text-gray-500">{line.description || '-'}</td>
                                                    <td className="px-6 py-4 text-sm text-right text-gray-900 whitespace-nowrap">
                                                        {new Intl.NumberFormat('id-ID', { minimumFractionDigits: 3 }).format(line.quantity)}
                                                    </td>
                                                    <td className="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">{line.unit}</td>
                                                    <td className="px-6 py-4 text-sm text-right text-gray-900 whitespace-nowrap">
                                                        Rp {new Intl.NumberFormat('id-ID').format(line.unit_price)}
                                                    </td>
                                                    <td className="px-6 py-4 text-sm text-right text-gray-900 whitespace-nowrap">
                                                        Rp {new Intl.NumberFormat('id-ID').format(line.discount_amount)}
                                                    </td>
                                                    <td className="px-6 py-4 text-sm text-right text-gray-900 whitespace-nowrap">
                                                        Rp {new Intl.NumberFormat('id-ID').format(line.tax_amount)}
                                                    </td>
                                                    <td className="px-6 py-4 text-sm font-medium text-right text-gray-900 whitespace-nowrap">
                                                        Rp {new Intl.NumberFormat('id-ID').format(line.line_total)}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {/* Totals */}
                            <div className="flex justify-end">
                                <div className="w-64 space-y-2">
                                    <div className="flex justify-between text-sm">
                                        <span className="font-medium text-gray-500">Subtotal:</span>
                                        <span className="text-gray-900">Rp {new Intl.NumberFormat('id-ID').format(salesOrder.subtotal)}</span>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <span className="font-medium text-gray-500">Discount:</span>
                                        <span className="text-gray-900">Rp {new Intl.NumberFormat('id-ID').format(salesOrder.discount_amount)}</span>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <span className="font-medium text-gray-500">Tax:</span>
                                        <span className="text-gray-900">Rp {new Intl.NumberFormat('id-ID').format(salesOrder.tax_amount)}</span>
                                    </div>
                                    <div className="flex justify-between pt-2 text-lg font-bold border-t">
                                        <span className="text-gray-900">Grand Total:</span>
                                        <span className="text-gray-900">Rp {new Intl.NumberFormat('id-ID').format(salesOrder.grand_total)}</span>
                                    </div>
                                </div>
                            </div>

                            {/* Back Button */}
                            <div className="flex justify-start mt-8">
                                <Link
                                    href={route('sales-orders.index')}
                                    className="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
                                >
                                    Kembali ke List
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
