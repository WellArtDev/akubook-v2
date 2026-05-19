import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Create({ auth, salesOrders, salesOrder, lines }) {
    const orderOptions = salesOrders || [];
    const selectedSalesOrder = salesOrder || null;
    const availableLines = lines || [];

    const { data, setData, post, processing, errors } = useForm({
        do_date: new Date().toISOString().slice(0, 10),
        sales_order_id: selectedSalesOrder?.id || '',
        delivery_date: '',
        driver_name: '',
        vehicle_number: '',
        notes: '',
        lines: availableLines.map((line) => ({
            sales_order_line_id: line.id,
            delivery_quantity: line.remaining_quantity,
            notes: '',
        })),
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('delivery-orders.store'));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Buat Delivery Order" />
            <div className="py-6">
                <div className="mx-auto max-w-5xl sm:px-6 lg:px-8">
                    <form onSubmit={submit} className="space-y-4 bg-white p-6 shadow sm:rounded-lg">
                        <div className="flex items-center justify-between">
                            <h1 className="text-xl font-semibold">Buat Delivery Order</h1>
                            <Link href={route('delivery-orders.index')} className="text-sm text-gray-600 hover:text-gray-900">Kembali</Link>
                        </div>

                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <label className="mb-1 block text-sm">Tanggal DO</label>
                                <input type="date" className="w-full rounded border-gray-300" value={data.do_date} onChange={(e) => setData('do_date', e.target.value)} />
                                {errors.do_date && <p className="text-sm text-red-600">{errors.do_date}</p>}
                            </div>
                            <div>
                                <label className="mb-1 block text-sm">Sales Order</label>
                                <select className="w-full rounded border-gray-300" value={data.sales_order_id} onChange={(e) => setData('sales_order_id', e.target.value)}>
                                    <option value="">Pilih SO</option>
                                    {orderOptions.map((order) => <option key={order.id} value={order.id}>{order.so_number}</option>)}
                                </select>
                                {errors.sales_order_id && <p className="text-sm text-red-600">{errors.sales_order_id}</p>}
                            </div>
                            <div>
                                <label className="mb-1 block text-sm">Delivery Date</label>
                                <input type="date" className="w-full rounded border-gray-300" value={data.delivery_date} onChange={(e) => setData('delivery_date', e.target.value)} />
                            </div>
                            <div>
                                <label className="mb-1 block text-sm">Driver</label>
                                <input className="w-full rounded border-gray-300" value={data.driver_name} onChange={(e) => setData('driver_name', e.target.value)} />
                            </div>
                            <div>
                                <label className="mb-1 block text-sm">Kendaraan</label>
                                <input className="w-full rounded border-gray-300" value={data.vehicle_number} onChange={(e) => setData('vehicle_number', e.target.value)} />
                            </div>
                            <div className="md:col-span-2">
                                <label className="mb-1 block text-sm">Catatan</label>
                                <textarea className="w-full rounded border-gray-300" rows={3} value={data.notes} onChange={(e) => setData('notes', e.target.value)} />
                            </div>
                        </div>

                        <div className="overflow-hidden rounded border">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-3 py-2 text-left text-xs uppercase">Item</th>
                                        <th className="px-3 py-2 text-left text-xs uppercase">Sisa</th>
                                        <th className="px-3 py-2 text-left text-xs uppercase">Qty Kirim</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {availableLines.map((line, index) => (
                                        <tr key={line.id}>
                                            <td className="px-3 py-2 text-sm">{line.item?.name || line.description}</td>
                                            <td className="px-3 py-2 text-sm">{line.remaining_quantity} {line.unit}</td>
                                            <td className="px-3 py-2 text-sm">
                                                <input type="number" step="0.001" min="0" className="w-full rounded border-gray-300" value={data.lines[index]?.delivery_quantity || ''} onChange={(e) => {
                                                    const next = [...data.lines];
                                                    next[index] = { ...next[index], delivery_quantity: e.target.value };
                                                    setData('lines', next);
                                                }} />
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        {errors.lines && <p className="text-sm text-red-600">{errors.lines}</p>}

                        <div className="flex justify-end">
                            <button type="submit" disabled={processing} className="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
