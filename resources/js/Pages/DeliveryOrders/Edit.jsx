import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Edit({ auth, deliveryOrder, lines }) {
    const availableLines = lines || [];

    const { data, setData, put, processing, errors } = useForm({
        do_date: deliveryOrder.do_date,
        sales_order_id: deliveryOrder.sales_order_id,
        delivery_date: deliveryOrder.delivery_date || '',
        driver_name: deliveryOrder.driver_name || '',
        vehicle_number: deliveryOrder.vehicle_number || '',
        notes: deliveryOrder.notes || '',
        lines: deliveryOrder.lines.map((line) => ({
            sales_order_line_id: line.sales_order_line_id,
            delivery_quantity: line.delivery_quantity,
            notes: line.notes || '',
        })),
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('delivery-orders.update', deliveryOrder.id));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`Edit ${deliveryOrder.do_number}`} />
            <div className="py-6">
                <div className="mx-auto max-w-5xl sm:px-6 lg:px-8">
                    <form onSubmit={submit} className="space-y-4 bg-white p-6 shadow sm:rounded-lg">
                        <div className="flex items-center justify-between">
                            <h1 className="text-xl font-semibold">Edit {deliveryOrder.do_number}</h1>
                            <Link href={route('delivery-orders.show', deliveryOrder.id)} className="text-sm text-gray-600 hover:text-gray-900">Kembali</Link>
                        </div>

                        <div className="grid gap-4 md:grid-cols-2">
                            <input type="date" className="w-full rounded border-gray-300" value={data.do_date} onChange={(e) => setData('do_date', e.target.value)} />
                            <input type="date" className="w-full rounded border-gray-300" value={data.delivery_date} onChange={(e) => setData('delivery_date', e.target.value)} />
                            <input className="w-full rounded border-gray-300" placeholder="Driver" value={data.driver_name} onChange={(e) => setData('driver_name', e.target.value)} />
                            <input className="w-full rounded border-gray-300" placeholder="Kendaraan" value={data.vehicle_number} onChange={(e) => setData('vehicle_number', e.target.value)} />
                            <textarea className="w-full rounded border-gray-300 md:col-span-2" rows={3} placeholder="Catatan" value={data.notes} onChange={(e) => setData('notes', e.target.value)} />
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
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
