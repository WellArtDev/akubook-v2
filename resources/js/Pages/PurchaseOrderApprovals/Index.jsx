import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, approvals, metrics, filters }) {
    const currentFilters = filters || {};

    const applyFilter = (key, value) => {
        router.get(route('purchase-order-approvals.index'), {
            ...currentFilters,
            [key]: value,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleApprove = (approvalId) => {
        if (confirm('Approve PO ini?')) {
            router.post(route('purchase-order-approvals.approve', approvalId));
        }
    };

    const handleReject = (approvalId) => {
        const reason = prompt('Alasan reject PO:');
        if (reason && reason.trim() !== '') {
            router.post(route('purchase-order-approvals.reject', approvalId), {
                rejection_reason: reason,
            });
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Purchase Order Approvals</h2>}
        >
            <Head title="Purchase Order Approvals" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div className="p-4 bg-white rounded-lg shadow-sm">
                            <p className="text-sm text-gray-500">Pending</p>
                            <p className="text-2xl font-bold text-gray-900">{metrics?.pending_count ?? 0}</p>
                        </div>
                        <div className="p-4 bg-white rounded-lg shadow-sm">
                            <p className="text-sm text-gray-500">Approval Rate</p>
                            <p className="text-2xl font-bold text-gray-900">{metrics?.approval_rate ?? 0}%</p>
                        </div>
                        <div className="p-4 bg-white rounded-lg shadow-sm">
                            <p className="text-sm text-gray-500">Avg Approval Time</p>
                            <p className="text-2xl font-bold text-gray-900">{metrics?.avg_approval_time_hours ?? 0}h</p>
                        </div>
                    </div>

                    <div className="p-4 bg-white rounded-lg shadow-sm">
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <input
                                type="text"
                                placeholder="Cari PO / supplier"
                                value={currentFilters.search || ''}
                                onChange={(e) => applyFilter('search', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm"
                            />
                            <select
                                value={currentFilters.reason_type || ''}
                                onChange={(e) => applyFilter('reason_type', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm"
                            >
                                <option value="">Semua alasan</option>
                                <option value="high_value">High value</option>
                            </select>
                        </div>
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm rounded-lg">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">PO</th>
                                        <th className="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Supplier</th>
                                        <th className="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Submitted By</th>
                                        <th className="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Submitted At</th>
                                        <th className="px-4 py-3 text-xs font-medium text-right text-gray-500 uppercase">Total</th>
                                        <th className="px-4 py-3 text-xs font-medium text-right text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {approvals?.data?.map((approval) => (
                                        <tr key={approval.id}>
                                            <td className="px-4 py-3 text-sm text-gray-900">
                                                <Link href={route('purchase-order-approvals.show', approval.id)} className="text-blue-600 hover:underline">
                                                    {approval.purchase_order?.po_number}
                                                </Link>
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-900">{approval.purchase_order?.supplier?.name}</td>
                                            <td className="px-4 py-3 text-sm text-gray-900">{approval.submitter?.name}</td>
                                            <td className="px-4 py-3 text-sm text-gray-900">{approval.submitted_at}</td>
                                            <td className="px-4 py-3 text-sm text-right text-gray-900">
                                                Rp {new Intl.NumberFormat('id-ID').format(approval.purchase_order?.grand_total || 0)}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-right">
                                                <div className="flex justify-end gap-2">
                                                    <button
                                                        onClick={() => handleApprove(approval.id)}
                                                        className="px-3 py-1 text-xs text-white bg-green-600 rounded hover:bg-green-700"
                                                    >
                                                        Approve
                                                    </button>
                                                    <button
                                                        onClick={() => handleReject(approval.id)}
                                                        className="px-3 py-1 text-xs text-white bg-red-600 rounded hover:bg-red-700"
                                                    >
                                                        Reject
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
