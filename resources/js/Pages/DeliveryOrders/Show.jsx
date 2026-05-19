import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';

export default function Show({ auth, deliveryOrder }) {
    const { data, setData, post, processing } = useForm({
        received_by: '',
        received_at: new Date().toISOString().slice(0, 16),
        signature_path: '',
        pod_notes: '',
    });

    const cancel = () => {
        const reason = window.prompt('Alasan pembatalan');
        if (!reason) return;
        router.post(route('delivery-orders.cancel', deliveryOrder.id), { reason });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={deliveryOrder.do_number} />
            <div className="py-6">
                <div className="mx-auto max-w-6xl space-y-4 sm:px-6 lg:px-8">
                    <div className="bg-white p-6 shadow sm:rounded-lg">
                        <div className="mb-4 flex items-center justify-between">
                            <div>
                                <h1 className="text-xl font-semibold">{deliveryOrder.do_number}</h1>
                                <p className="text-sm text-gray-500">Status: {deliveryOrder.status}</p>
                            </div>
                            <div className="flex gap-2">
                                {deliveryOrder.can_edit && <Link href={route('delivery-orders.edit', deliveryOrder.id)} className="rounded bg-gray-800 px-3 py-2 text-sm text-white">Edit</Link>}
                                {deliveryOrder.status === 'draft' && <button onClick={() => router.post(route('delivery-orders.confirm', deliveryOrder.id))} className="rounded bg-blue-600 px-3 py-2 text-sm text-white">Confirm</button>}
                                {deliveryOrder.status === 'ready_to_ship' && <button onClick={() => router.post(route('delivery-orders.ship', deliveryOrder.id))} className="rounded bg-indigo-600 px-3 py-2 text-sm text-white">Ship</button>}
                                {deliveryOrder.can_cancel && deliveryOrder.status !== 'delivered' && <button onClick={cancel} className="rounded bg-red-600 px-3 py-2 text-sm text-white">Cancel</button>}
                            </div>
                        </div>

                        <div className="grid gap-4 md:grid-cols-3">
                            <div><p className="text-xs text-gray-500">SO</p><p>{deliveryOrder.sales_order?.so_number}</p></div>
                            <div><p className="text-xs text-gray-500">Customer</p><p>{deliveryOrder.customer?.name}</p></div>
                            <div><p className="text-xs text-gray-500">Tanggal</p><p>{deliveryOrder.do_date}</p></div>
                        </div>

                        <div className="mt-4 overflow-hidden rounded border">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-3 py-2 text-left text-xs uppercase">Item</th>
                                        <th className="px-3 py-2 text-left text-xs uppercase">SO Qty</th>
                                        <th className="px-3 py-2 text-left text-xs uppercase">Terkirim</th>
                                        <th className="px-3 py-2 text-left text-xs uppercase">Qty DO</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {deliveryOrder.lines.map((line) => (
                                        <tr key={line.id}>
                                            <td className="px-3 py-2 text-sm">{line.item?.name || line.description}</td>
                                            <td className="px-3 py-2 text-sm">{line.so_quantity}</td>
                                            <td className="px-3 py-2 text-sm">{line.previously_delivered_quantity}</td>
                                            <td className="px-3 py-2 text-sm">{line.delivery_quantity}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {deliveryOrder.status === 'in_transit' && (
                        <form onSubmit={(e) => {
                            e.preventDefault();
                            post(route('delivery-orders.deliver', deliveryOrder.id));
                        }} className="space-y-3 bg-white p-6 shadow sm:rounded-lg">
                            <h2 className="text-lg font-semibold">Proof of Delivery</h2>
                            <div className="grid gap-3 md:grid-cols-2">
                                <input className="rounded border-gray-300" placeholder="Received by" value={data.received_by} onChange={(e) => setData('received_by', e.target.value)} />
                                <input type="datetime-local" className="rounded border-gray-300" value={data.received_at} onChange={(e) => setData('received_at', e.target.value)} />
                                <input className="rounded border-gray-300" placeholder="Signature path" value={data.signature_path} onChange={(e) => setData('signature_path', e.target.value)} />
                                <input className="rounded border-gray-300" placeholder="POD notes" value={data.pod_notes} onChange={(e) => setData('pod_notes', e.target.value)} />
                            </div>
                            <button type="submit" disabled={processing} className="rounded bg-green-600 px-4 py-2 text-white">Mark Delivered</button>
                        </form>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
