import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

export default function Show({ auth, purchaseReturn }) {
    const approve = () => router.post(route('purchase-returns.approve', purchaseReturn.id));
    const complete = () => router.post(route('purchase-returns.complete', purchaseReturn.id));
    const reject = () => {
        const reason = window.prompt('Alasan reject return:');
        if (!reason) return;
        router.post(route('purchase-returns.reject', purchaseReturn.id), { reason });
    };
    const receive = () => {
        const lines = purchaseReturn.lines.map((line) => ({
            id: line.id,
            accepted_quantity: line.return_quantity,
            rejected_quantity: 0,
            inspection_notes: '',
        }));
        router.post(route('purchase-returns.receive', purchaseReturn.id), { lines });
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Purchase Return {purchaseReturn.return_number}</h2>}>
            <Head title={purchaseReturn.return_number} />

            <div className="py-6">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
                    <div className="bg-white p-6 shadow-sm sm:rounded-lg space-y-2">
                        <div className="text-sm text-gray-600">Supplier: {purchaseReturn.supplier?.name}</div>
                        <div className="text-sm text-gray-600">Invoice: {purchaseReturn.purchase_invoice?.invoice_number}</div>
                        <div className="text-sm text-gray-600">Status: <span className="capitalize">{purchaseReturn.status}</span></div>
                        <div className="text-sm text-gray-600">Reason: {purchaseReturn.return_reason}</div>
                    </div>

                    <div className="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                        <table className="min-w-full text-sm">
                            <thead className="bg-gray-50 text-left">
                                <tr>
                                    <th className="px-4 py-3">Product</th>
                                    <th className="px-4 py-3 text-right">Return Qty</th>
                                    <th className="px-4 py-3 text-right">Accepted</th>
                                    <th className="px-4 py-3 text-right">Rejected</th>
                                    <th className="px-4 py-3 text-right">Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                {purchaseReturn.lines.map((line) => (
                                    <tr key={line.id} className="border-t">
                                        <td className="px-4 py-3">{line.product_name}</td>
                                        <td className="px-4 py-3 text-right">{line.return_quantity}</td>
                                        <td className="px-4 py-3 text-right">{line.accepted_quantity}</td>
                                        <td className="px-4 py-3 text-right">{line.rejected_quantity}</td>
                                        <td className="px-4 py-3 text-right">{Number(line.line_total || 0).toLocaleString('id-ID')}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <div className="bg-white p-6 shadow-sm sm:rounded-lg flex flex-wrap gap-2 justify-end">
                        {purchaseReturn.status === 'draft' && (
                            <>
                                <button onClick={approve} className="px-4 py-2 bg-green-600 text-white rounded text-sm">Approve</button>
                                <button onClick={reject} className="px-4 py-2 bg-red-600 text-white rounded text-sm">Reject</button>
                            </>
                        )}
                        {purchaseReturn.status === 'approved' && (
                            <button onClick={receive} className="px-4 py-2 bg-indigo-600 text-white rounded text-sm">Receive</button>
                        )}
                        {purchaseReturn.status === 'received' && (
                            <button onClick={complete} className="px-4 py-2 bg-emerald-700 text-white rounded text-sm">Complete</button>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
