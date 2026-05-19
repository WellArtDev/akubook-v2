import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Show({ auth, approval }) {
    const purchaseOrder = approval.purchase_order;

    const handleApprove = () => {
        if (confirm('Approve PO ini?')) {
            router.post(route('purchase-order-approvals.approve', approval.id));
        }
    };

    const handleReject = () => {
        const reason = prompt('Alasan reject PO:');
        if (reason && reason.trim() !== '') {
            router.post(route('purchase-order-approvals.reject', approval.id), {
                rejection_reason: reason,
            });
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">PO Approval Detail</h2>}
        >
            <Head title="PO Approval Detail" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                    <div className="p-6 bg-white rounded-lg shadow-sm">
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <p className="text-sm text-gray-500">PO Number</p>
                                <p className="text-lg font-semibold text-gray-900">{purchaseOrder?.po_number}</p>
                            </div>
                            <div>
                                <p className="text-sm text-gray-500">Supplier</p>
                                <p className="text-lg font-semibold text-gray-900">{purchaseOrder?.supplier?.name}</p>
                            </div>
                            <div>
                                <p className="text-sm text-gray-500">Submitted By</p>
                                <p className="text-gray-900">{approval.submitter?.name}</p>
                            </div>
                            <div>
                                <p className="text-sm text-gray-500">Submitted At</p>
                                <p className="text-gray-900">{approval.submitted_at}</p>
                            </div>
                            <div>
                                <p className="text-sm text-gray-500">Grand Total</p>
                                <p className="text-gray-900">Rp {new Intl.NumberFormat('id-ID').format(purchaseOrder?.grand_total || 0)}</p>
                            </div>
                            <div>
                                <p className="text-sm text-gray-500">Status</p>
                                <p className="text-gray-900">{approval.status}</p>
                            </div>
                        </div>
                    </div>

                    <div className="p-6 bg-white rounded-lg shadow-sm">
                        <h3 className="mb-4 text-lg font-semibold text-gray-900">Approval Reasons</h3>
                        <ul className="space-y-2">
                            {(approval.approval_reasons || []).map((reason, index) => (
                                <li key={index} className="text-sm text-gray-700">
                                    <span className="font-semibold">{reason.type}</span>: {reason.message}
                                </li>
                            ))}
                        </ul>
                    </div>

                    <div className="p-6 bg-white rounded-lg shadow-sm">
                        <h3 className="mb-4 text-lg font-semibold text-gray-900">Line Items</h3>
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">#</th>
                                        <th className="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Item</th>
                                        <th className="px-4 py-3 text-xs font-medium text-right text-gray-500 uppercase">Qty</th>
                                        <th className="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Unit</th>
                                        <th className="px-4 py-3 text-xs font-medium text-right text-gray-500 uppercase">Unit Price</th>
                                        <th className="px-4 py-3 text-xs font-medium text-right text-gray-500 uppercase">Total</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {(purchaseOrder?.lines || []).map((line) => (
                                        <tr key={line.id}>
                                            <td className="px-4 py-3 text-sm text-gray-900">{line.line_number}</td>
                                            <td className="px-4 py-3 text-sm text-gray-900">{line.product_name}</td>
                                            <td className="px-4 py-3 text-sm text-right text-gray-900">{line.quantity}</td>
                                            <td className="px-4 py-3 text-sm text-gray-900">{line.unit}</td>
                                            <td className="px-4 py-3 text-sm text-right text-gray-900">Rp {new Intl.NumberFormat('id-ID').format(line.unit_price)}</td>
                                            <td className="px-4 py-3 text-sm text-right text-gray-900">Rp {new Intl.NumberFormat('id-ID').format(line.line_total)}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {approval.status === 'pending' && (
                        <div className="flex justify-end gap-2">
                            <button
                                onClick={handleApprove}
                                className="px-4 py-2 text-white bg-green-600 rounded-md hover:bg-green-700"
                            >
                                Approve
                            </button>
                            <button
                                onClick={handleReject}
                                className="px-4 py-2 text-white bg-red-600 rounded-md hover:bg-red-700"
                            >
                                Reject
                            </button>
                        </div>
                    )}

                    <div>
                        <Link href={route('purchase-order-approvals.index')} className="text-blue-600 hover:underline">
                            Kembali ke dashboard approval
                        </Link>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
