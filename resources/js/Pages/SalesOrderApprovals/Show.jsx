import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Show({ auth, approval, creditStatus, stockStatus }) {
    const approve = () => {
        router.post(route('sales-order-approvals.approve', approval.id));
    };

    const reject = () => {
        const reason = prompt('Alasan reject');
        if (!reason) return;
        router.post(route('sales-order-approvals.reject', approval.id), { rejection_reason: reason });
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Approval Detail</h2>}>
            <Head title="Approval Detail" />
            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-lg bg-white p-6 shadow">
                        <div className="mb-4 flex items-center justify-between">
                            <div>
                                <div className="text-sm text-gray-500">SO Number</div>
                                <div className="text-xl font-semibold">{approval.sales_order?.so_number}</div>
                            </div>
                            <Link href={route('sales-order-approvals.index')} className="text-blue-600">Kembali</Link>
                        </div>

                        <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div className="rounded border p-3">Credit Limit: Rp {new Intl.NumberFormat('id-ID').format(creditStatus.credit_limit || 0)}</div>
                            <div className="rounded border p-3">Outstanding: Rp {new Intl.NumberFormat('id-ID').format(creditStatus.outstanding || 0)}</div>
                            <div className="rounded border p-3">Exceeded: Rp {new Intl.NumberFormat('id-ID').format(creditStatus.exceeded_amount || 0)}</div>
                        </div>

                        <div className="mt-6 overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-4 py-2 text-left text-xs uppercase">Item</th>
                                        <th className="px-4 py-2 text-right text-xs uppercase">Qty</th>
                                        <th className="px-4 py-2 text-right text-xs uppercase">Price</th>
                                        <th className="px-4 py-2 text-right text-xs uppercase">Total</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {approval.sales_order?.lines?.map((line) => (
                                        <tr key={line.id}>
                                            <td className="px-4 py-2">{line.item?.name || line.description}</td>
                                            <td className="px-4 py-2 text-right">{line.quantity}</td>
                                            <td className="px-4 py-2 text-right">Rp {new Intl.NumberFormat('id-ID').format(line.unit_price)}</td>
                                            <td className="px-4 py-2 text-right">Rp {new Intl.NumberFormat('id-ID').format(line.line_total)}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        <div className="mt-6 flex gap-3">
                            <button onClick={approve} className="rounded bg-green-600 px-4 py-2 text-white">Approve</button>
                            <button onClick={reject} className="rounded bg-red-600 px-4 py-2 text-white">Reject</button>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
