import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';

export default function Create({ auth, deliveryOrders, selectedDeliveryOrder, availableLines, customers }) {
    const { data, setData, post, processing, errors } = useForm({
        invoice_date: new Date().toISOString().split('T')[0],
        due_date: '',
        delivery_order_id: selectedDeliveryOrder?.id ? String(selectedDeliveryOrder.id) : '',
        sales_order_id: selectedDeliveryOrder?.sales_order_id ? String(selectedDeliveryOrder.sales_order_id) : '',
        customer_id: selectedDeliveryOrder?.customer_id ? String(selectedDeliveryOrder.customer_id) : '',
        billing_address: '',
        payment_terms: selectedDeliveryOrder?.sales_order?.payment_terms ?? '',
        reference: selectedDeliveryOrder?.do_number ?? '',
        notes: '',
        generate_tax_invoice: false,
        lines: availableLines.map((line) => ({
            delivery_order_line_id: line.delivery_order_line_id,
            quantity: line.remaining_to_invoice,
        })),
    });

    const onDeliveryOrderChange = (value) => {
        if (!value) {
            setData('delivery_order_id', '');
            setData('sales_order_id', '');
            setData('customer_id', '');
            setData('lines', []);
            return;
        }

        const selected = deliveryOrders.find((order) => String(order.id) === value);
        setData('delivery_order_id', value);
        setData('sales_order_id', selected?.sales_order_id ? String(selected.sales_order_id) : '');
        setData('customer_id', selected?.customer_id ? String(selected.customer_id) : '');
        router.get(route('sales-invoices.create'), { delivery_order_id: value }, { preserveState: false, replace: true });
    };

    const updateLineQuantity = (index, quantity) => {
        const nextLines = [...data.lines];
        nextLines[index] = {
            ...nextLines[index],
            quantity,
        };
        setData('lines', nextLines);
    };

    const removeLine = (index) => {
        setData('lines', data.lines.filter((_, i) => i !== index));
    };

    const totalSubtotal = data.lines.reduce((sum, line) => {
        const source = availableLines.find((item) => item.delivery_order_line_id === line.delivery_order_line_id);
        return sum + (Number(line.quantity || 0) * Number(source?.unit_price || 0));
    }, 0);

    const totalTax = totalSubtotal * 0.11;
    const totalGrand = totalSubtotal + totalTax;

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('sales-invoices.store'));
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Buat Invoice</h2>}>
            <Head title="Buat Invoice" />
            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <form onSubmit={handleSubmit} className="p-6">
                            <div className="grid grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label className="block mb-2 text-sm font-medium">Tanggal Invoice *</label>
                                    <input type="date" value={data.invoice_date} onChange={(e) => setData('invoice_date', e.target.value)} className="w-full px-3 py-2 border rounded-md" required />
                                    {errors.invoice_date && <p className="mt-1 text-sm text-red-600">{errors.invoice_date}</p>}
                                </div>
                                <div>
                                    <label className="block mb-2 text-sm font-medium">Jatuh Tempo *</label>
                                    <input type="date" value={data.due_date} onChange={(e) => setData('due_date', e.target.value)} className="w-full px-3 py-2 border rounded-md" required />
                                    {errors.due_date && <p className="mt-1 text-sm text-red-600">{errors.due_date}</p>}
                                </div>
                                <div>
                                    <label className="block mb-2 text-sm font-medium">Delivery Order *</label>
                                    <select value={data.delivery_order_id} onChange={(e) => onDeliveryOrderChange(e.target.value)} className="w-full px-3 py-2 border rounded-md" required>
                                        <option value="">Pilih Delivery Order</option>
                                        {deliveryOrders.map((order) => (
                                            <option key={order.id} value={order.id}>{order.do_number} - {order.customer?.name}</option>
                                        ))}
                                    </select>
                                    {errors.delivery_order_id && <p className="mt-1 text-sm text-red-600">{errors.delivery_order_id}</p>}
                                </div>
                                <div>
                                    <label className="block mb-2 text-sm font-medium">Customer *</label>
                                    <select value={data.customer_id} onChange={(e) => setData('customer_id', e.target.value)} className="w-full px-3 py-2 border rounded-md" required>
                                        <option value="">Pilih Customer</option>
                                        {customers.map((customer) => (
                                            <option key={customer.id} value={customer.id}>{customer.name}</option>
                                        ))}
                                    </select>
                                    {errors.customer_id && <p className="mt-1 text-sm text-red-600">{errors.customer_id}</p>}
                                </div>
                            </div>

                            <div className="mb-6">
                                <h3 className="mb-4 text-lg font-semibold">Line Delivery</h3>
                                {availableLines.length === 0 ? (
                                    <div className="p-4 text-sm text-gray-500 border rounded-md">Tidak ada line tersisa untuk ditagihkan.</div>
                                ) : (
                                    <div className="space-y-4">
                                        {data.lines.map((line, index) => {
                                            const source = availableLines.find((item) => item.delivery_order_line_id === line.delivery_order_line_id);
                                            if (!source) return null;
                                            return (
                                                <div key={line.delivery_order_line_id} className="grid grid-cols-12 gap-4 p-4 border rounded-md">
                                                    <div className="col-span-5">
                                                        <p className="text-sm font-medium">{source.product_name}</p>
                                                        <p className="text-xs text-gray-500">Sisa qty: {source.remaining_to_invoice}</p>
                                                    </div>
                                                    <div className="col-span-3">
                                                        <input type="number" value={line.quantity} onChange={(e) => updateLineQuantity(index, Number(e.target.value || 0))} className="w-full px-3 py-2 border rounded-md" min="0.001" max={source.remaining_to_invoice} step="0.001" required />
                                                    </div>
                                                    <div className="col-span-2 text-sm flex items-center">{Number(source.unit_price).toLocaleString('id-ID')}</div>
                                                    <div className="col-span-2 flex items-center justify-end">
                                                        {data.lines.length > 1 && <button type="button" onClick={() => removeLine(index)} className="px-3 py-2 text-white bg-red-600 rounded-md hover:bg-red-700">Hapus</button>}
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                )}
                                {errors.lines && <p className="mt-2 text-sm text-red-600">{errors.lines}</p>}
                            </div>

                            <div className="p-4 mb-6 border rounded-md bg-gray-50 text-sm">
                                <div className="flex justify-between"><span>Subtotal</span><span>{totalSubtotal.toLocaleString('id-ID')}</span></div>
                                <div className="flex justify-between"><span>PPN 11%</span><span>{totalTax.toLocaleString('id-ID')}</span></div>
                                <div className="flex justify-between font-semibold"><span>Grand Total</span><span>{totalGrand.toLocaleString('id-ID')}</span></div>
                            </div>

                            <div className="flex justify-end gap-4">
                                <Link href={route('sales-invoices.index')} className="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">Batal</Link>
                                <button type="submit" disabled={processing} className="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
