import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';

export default function Index({ auth, approvals, metrics, filters }) {
    const { data, setData, post, processing } = useForm({
        approval_ids: [],
        comments: '',
    });

    const updateFilter = (key, value) => {
        router.get(route('sales-order-approvals.index'), { ...filters, [key]: value || undefined }, { preserveState: true, replace: true });
    };

    const toggleSelected = (id) => {
        if (data.approval_ids.includes(id)) {
            setData('approval_ids', data.approval_ids.filter((item) => item !== id));
            return;
        }
        setData('approval_ids', [...data.approval_ids, id]);
    };

    const submitBulkApprove = (event) => {
        event.preventDefault();
        if (!data.approval_ids.length) return;
        post(route('sales-order-approvals.bulk-approve'));
    };

    const quickApprove = (approval) => {
        router.post(route('sales-order-approvals.approve', approval.id));
    };

    const quickReject = (approval) => {
        const reason = prompt('Alasan reject');
        if (!reason) return;
        router.post(route('sales-order-approvals.reject', approval.id), { rejection_reason: reason });
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Sales Order Approvals</h2>}>
            <Head title="Sales Order Approvals" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div className="rounded-lg bg-white p-4 shadow">Pending: {metrics.pending_count}</div>
                        <div className="rounded-lg bg-white p-4 shadow">Approval Rate: {metrics.approval_rate}%</div>
                        <div className="rounded-lg bg-white p-4 shadow">Avg Time: {metrics.avg_approval_time_hours} jam</div>
                    </div>

                    <div className="rounded-lg bg-white p-6 shadow">
                        <div className="mb-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                            <input
                                type="text"
                                value={filters.search || ''}
                                onChange={(event) => updateFilter('search', event.target.value)}
                                placeholder="Cari SO / customer"
                                className="rounded border-gray-300"
                            />
                            <select
                                value={filters.reason_type || ''}
                                onChange={(event) => updateFilter('reason_type', event.target.value)}
                                className="rounded border-gray-300"
                            >
                                <option value="">Semua reason</option>
                                <option value="high_value">High Value</option>
                                <option value="credit_exceeded">Credit Exceeded</option>
                            </select>
                        </div>

                        <form onSubmit={submitBulkApprove} className="mb-4 flex items-center gap-3">
                            <input
                                type="text"
                                value={data.comments}
                                onChange={(event) => setData('comments', event.target.value)}
                                placeholder="Bulk comment"
                                className="rounded border-gray-300"
                            />
                            <button type="submit" disabled={processing || !data.approval_ids.length} className="rounded bg-green-600 px-3 py-2 text-white disabled:opacity-50">
                                Bulk Approve
                            </button>
                        </form>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-4 py-2" />
                                        <th className="px-4 py-2 text-left text-xs uppercase">SO</th>
                                        <th className="px-4 py-2 text-left text-xs uppercase">Customer</th>
                                        <th className="px-4 py-2 text-left text-xs uppercase">Submitted</th>
                                        <th className="px-4 py-2 text-right text-xs uppercase">Amount</th>
                                        <th className="px-4 py-2 text-right text-xs uppercase">Action</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {approvals.data.map((approval) => (
                                        <tr key={approval.id}>
                                            <td className="px-4 py-2">
                                                <input type="checkbox" checked={data.approval_ids.includes(approval.id)} onChange={() => toggleSelected(approval.id)} />
                                            </td>
                                            <td className="px-4 py-2">
                                                <Link className="text-blue-600" href={route('sales-order-approvals.show', approval.id)}>
                                                    {approval.sales_order?.so_number}
                                                </Link>
                                            </td>
                                            <td className="px-4 py-2">{approval.sales_order?.customer?.name}</td>
                                            <td className="px-4 py-2">{approval.submitted_at}</td>
                                            <td className="px-4 py-2 text-right">Rp {new Intl.NumberFormat('id-ID').format(approval.sales_order?.grand_total || 0)}</td>
                                            <td className="px-4 py-2">
                                                <div className="flex justify-end gap-2">
                                                    <button onClick={() => quickApprove(approval)} className="text-green-600">Approve</button>
                                                    <button onClick={() => quickReject(approval)} className="text-red-600">Reject</button>
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
