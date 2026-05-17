import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';

const statusColors = {
    draft: 'bg-gray-500',
    pending_approval: 'bg-yellow-500',
    approved: 'bg-green-500',
    rejected: 'bg-red-500',
    converted: 'bg-blue-500',
    cancelled: 'bg-gray-400',
};

const statusLabels = {
    draft: 'Draft',
    pending_approval: 'Pending Approval',
    approved: 'Approved',
    rejected: 'Rejected',
    converted: 'Converted to PO',
    cancelled: 'Cancelled',
};

export default function Show({ auth, purchaseRequest }) {
    const handleSubmit = () => {
        if (confirm('Submit this PR for approval?')) {
            router.post(route('purchase-requests.submit', purchaseRequest.id));
        }
    };

    const handleApprove = () => {
        if (confirm('Approve this PR?')) {
            router.post(route('purchase-requests.approve', purchaseRequest.id));
        }
    };

    const handleReject = () => {
        if (confirm('Reject this PR?')) {
            router.post(route('purchase-requests.reject', purchaseRequest.id));
        }
    };

    const handleCancel = () => {
        if (confirm('Cancel this PR?')) {
            router.post(route('purchase-requests.cancel', purchaseRequest.id));
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Purchase Request Detail</h2>}
        >
            <Head title={`PR ${purchaseRequest.pr_number}`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {/* Header */}
                            <div className="flex justify-between items-start mb-6">
                                <div>
                                    <h3 className="text-2xl font-bold">{purchaseRequest.pr_number}</h3>
                                    <Badge className={`${statusColors[purchaseRequest.status]} mt-2`}>
                                        {statusLabels[purchaseRequest.status]}
                                    </Badge>
                                </div>
                                <div className="flex gap-2">
                                    <Link href={route('purchase-requests.index')}>
                                        <Button variant="outline">Back</Button>
                                    </Link>
                                    {purchaseRequest.status === 'draft' && (
                                        <>
                                            <Link href={route('purchase-requests.edit', purchaseRequest.id)}>
                                                <Button variant="outline">Edit</Button>
                                            </Link>
                                            <Button onClick={handleSubmit}>Submit for Approval</Button>
                                        </>
                                    )}
                                    {purchaseRequest.status === 'pending_approval' && (
                                        <>
                                            <Button onClick={handleApprove} className="bg-green-600 hover:bg-green-700">
                                                Approve
                                            </Button>
                                            <Button onClick={handleReject} variant="destructive">
                                                Reject
                                            </Button>
                                        </>
                                    )}
                                    {['draft', 'pending_approval', 'approved'].includes(purchaseRequest.status) && (
                                        <Button onClick={handleCancel} variant="outline">
                                            Cancel
                                        </Button>
                                    )}
                                </div>
                            </div>

                            {/* PR Info */}
                            <div className="grid grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">PR Date</label>
                                    <p className="mt-1 text-sm text-gray-900">
                                        {new Date(purchaseRequest.pr_date).toLocaleDateString('id-ID')}
                                    </p>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Required Date</label>
                                    <p className="mt-1 text-sm text-gray-900">
                                        {new Date(purchaseRequest.required_date).toLocaleDateString('id-ID')}
                                    </p>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Department</label>
                                    <p className="mt-1 text-sm text-gray-900">
                                        {purchaseRequest.department?.name || '-'}
                                    </p>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Created By</label>
                                    <p className="mt-1 text-sm text-gray-900">
                                        {purchaseRequest.created_by?.name || '-'}
                                    </p>
                                </div>
                                {purchaseRequest.approved_by && (
                                    <>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Approved By</label>
                                            <p className="mt-1 text-sm text-gray-900">
                                                {purchaseRequest.approved_by?.name || '-'}
                                            </p>
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Approved At</label>
                                            <p className="mt-1 text-sm text-gray-900">
                                                {new Date(purchaseRequest.approved_at).toLocaleString('id-ID')}
                                            </p>
                                        </div>
                                    </>
                                )}
                                {purchaseRequest.justification && (
                                    <div className="col-span-2">
                                        <label className="block text-sm font-medium text-gray-700">Justification</label>
                                        <p className="mt-1 text-sm text-gray-900">{purchaseRequest.justification}</p>
                                    </div>
                                )}
                            </div>

                            {/* Line Items */}
                            <div className="mt-6">
                                <h4 className="text-lg font-medium mb-4">Line Items</h4>
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Est. Price</th>
                                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {purchaseRequest.lines.map((line) => (
                                            <tr key={line.id}>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {line.line_number}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-gray-900">
                                                    {line.product_name}
                                                    {line.product_code && (
                                                        <span className="text-gray-500 text-xs block">{line.product_code}</span>
                                                    )}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-gray-500">{line.description || '-'}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                                    {parseFloat(line.quantity).toLocaleString('id-ID')}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{line.unit}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                                    Rp {parseFloat(line.estimated_price).toLocaleString('id-ID')}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                                    Rp {parseFloat(line.line_total).toLocaleString('id-ID')}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                    <tfoot className="bg-gray-50">
                                        <tr>
                                            <td colSpan="6" className="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                                Total Estimated Amount:
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right">
                                                Rp {parseFloat(purchaseRequest.total_estimated_amount).toLocaleString('id-ID')}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
