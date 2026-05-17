import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function Edit({ auth, salesOrder, customers, branches, items }) {
    const { data, setData, put, processing, errors } = useForm({
        so_date: salesOrder.so_date,
        customer_id: salesOrder.customer_id,
        customer_po_number: salesOrder.customer_po_number || '',
        payment_terms: salesOrder.payment_terms || '',
        delivery_terms: salesOrder.delivery_terms || '',
        delivery_address_id: salesOrder.delivery_address_id || '',
        requested_delivery_date: salesOrder.requested_delivery_date || '',
        notes: salesOrder.notes || '',
        lines: salesOrder.lines.map(line => ({
            item_id: line.item_id,
            description: line.description || '',
            quantity: line.quantity,
            unit: line.unit,
            unit_price: line.unit_price,
            discount_percent: line.discount_percent,
            discount_amount: line.discount_amount,
            tax_amount: line.tax_amount,
        })),
    });

    const [selectedCustomer, setSelectedCustomer] = useState(
        customers.find(c => c.id === salesOrder.customer_id)
    );

    const addLine = () => {
        setData('lines', [...data.lines, { item_id: '', description: '', quantity: 1, unit: '', unit_price: 0, discount_percent: 0, discount_amount: 0, tax_amount: 0 }]);
    };

    const removeLine = (index) => {
        if (data.lines.length > 1) {
            setData('lines', data.lines.filter((_, i) => i !== index));
        }
    };

    const updateLine = (index, field, value) => {
        const newLines = [...data.lines];
        newLines[index][field] = value;

        if (field === 'item_id') {
            const item = items.find(i => i.id === parseInt(value));
            if (item) {
                newLines[index].unit = item.unit;
                newLines[index].unit_price = item.selling_price || 0;
            }
        }

        if (field === 'discount_percent') {
            const subtotal = newLines[index].quantity * newLines[index].unit_price;
            newLines[index].discount_amount = (subtotal * parseFloat(value || 0)) / 100;
        }

        setData('lines', newLines);
    };

    const handleCustomerChange = (customerId) => {
        setData('customer_id', customerId);
        const customer = customers.find(c => c.id === parseInt(customerId));
        setSelectedCustomer(customer);
        if (customer) {
            setData('payment_terms', `Net ${customer.payment_terms_days || 0}`);
        }
    };

    const calculateLineTotal = (line) => {
        const subtotal = parseFloat(line.quantity || 0) * parseFloat(line.unit_price || 0);
        return subtotal - parseFloat(line.discount_amount || 0);
    };

    const subtotal = data.lines.reduce((sum, line) => sum + calculateLineTotal(line), 0);
    const totalTax = data.lines.reduce((sum, line) => sum + parseFloat(line.tax_amount || 0), 0);
    const grandTotal = subtotal + totalTax;

    const creditLimitWarning = selectedCustomer && selectedCustomer.credit_limit > 0 && grandTotal > selectedCustomer.credit_limit;

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('sales-orders.update', salesOrder.id));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Edit Sales Order - {salesOrder.so_number}</h2>}
        >
            <Head title={`Edit Sales Order - ${salesOrder.so_number}`} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <form onSubmit={handleSubmit} className="p-6">
                            {/* Header Fields */}
                            <div className="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">
                                        Tanggal SO <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        value={data.so_date}
                                        onChange={(e) => setData('so_date', e.target.value)}
                                        className="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                    {errors.so_date && <p className="mt-1 text-sm text-red-600">{errors.so_date}</p>}
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700">
                                        Customer <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={data.customer_id}
                                        onChange={(e) => handleCustomerChange(e.target.value)}
                                        className="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="">Pilih Customer</option>
                                        {customers.map((customer) => (
                                            <option key={customer.id} value={customer.id}>
                                                {customer.code} - {customer.name}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.customer_id && <p className="mt-1 text-sm text-red-600">{errors.customer_id}</p>}
                                    {selectedCustomer && selectedCustomer.credit_limit > 0 && (
                                        <p className="mt-1 text-xs text-gray-500">
                                            Credit Limit: Rp {new Intl.NumberFormat('id-ID').format(selectedCustomer.credit_limit)}
                                        </p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Customer PO Number</label>
                                    <input
                                        type="text"
                                        value={data.customer_po_number}
                                        onChange={(e) => setData('customer_po_number', e.target.value)}
                                        className="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="Reference"
                                    />
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Delivery Address</label>
                                    <select
                                        value={data.delivery_address_id}
                                        onChange={(e) => setData('delivery_address_id', e.target.value)}
                                        className="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="">Pilih Branch</option>
                                        {branches.map((branch) => (
                                            <option key={branch.id} value={branch.id}>
                                                {branch.code} - {branch.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Requested Delivery Date</label>
                                    <input
                                        type="date"
                                        value={data.requested_delivery_date}
                                        onChange={(e) => setData('requested_delivery_date', e.target.value)}
                                        className="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Payment Terms</label>
                                    <input
                                        type="text"
                                        value={data.payment_terms}
                                        onChange={(e) => setData('payment_terms', e.target.value)}
                                        className="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="e.g., Net 30"
                                    />
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Delivery Terms</label>
                                    <input
                                        type="text"
                                        value={data.delivery_terms}
                                        onChange={(e) => setData('delivery_terms', e.target.value)}
                                        className="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="e.g., FOB"
                                    />
                                </div>
                            </div>

                            <div className="mb-6">
                                <label className="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea
                                    value={data.notes}
                                    onChange={(e) => setData('notes', e.target.value)}
                                    rows="3"
                                    className="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Additional notes"
                                />
                            </div>

                            {/* Lines Table */}
                            <div className="mb-6">
                                <div className="flex items-center justify-between mb-4">
                                    <h3 className="text-lg font-medium text-gray-900">Line Items</h3>
                                    <button
                                        type="button"
                                        onClick={addLine}
                                        className="px-3 py-1 text-sm text-white bg-green-600 rounded-md hover:bg-green-700"
                                    >
                                        + Tambah Baris
                                    </button>
                                </div>

                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th className="px-3 py-2 text-xs font-medium text-left text-gray-500">Item</th>
                                                <th className="px-3 py-2 text-xs font-medium text-left text-gray-500">Description</th>
                                                <th className="px-3 py-2 text-xs font-medium text-right text-gray-500">Qty</th>
                                                <th className="px-3 py-2 text-xs font-medium text-left text-gray-500">Unit</th>
                                                <th className="px-3 py-2 text-xs font-medium text-right text-gray-500">Unit Price</th>
                                                <th className="px-3 py-2 text-xs font-medium text-right text-gray-500">Disc %</th>
                                                <th className="px-3 py-2 text-xs font-medium text-right text-gray-500">Disc Amt</th>
                                                <th className="px-3 py-2 text-xs font-medium text-right text-gray-500">Tax</th>
                                                <th className="px-3 py-2 text-xs font-medium text-right text-gray-500">Total</th>
                                                <th className="px-3 py-2 text-xs font-medium text-center text-gray-500">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {data.lines.map((line, index) => (
                                                <tr key={index}>
                                                    <td className="px-3 py-2">
                                                        <select
                                                            value={line.item_id}
                                                            onChange={(e) => updateLine(index, 'item_id', e.target.value)}
                                                            className="block w-full text-sm border-gray-300 rounded-md"
                                                        >
                                                            <option value="">Pilih Item</option>
                                                            {items.map((item) => (
                                                                <option key={item.id} value={item.id}>
                                                                    {item.code} - {item.name}
                                                                </option>
                                                            ))}
                                                        </select>
                                                    </td>
                                                    <td className="px-3 py-2">
                                                        <input
                                                            type="text"
                                                            value={line.description}
                                                            onChange={(e) => updateLine(index, 'description', e.target.value)}
                                                            className="block w-full text-sm border-gray-300 rounded-md"
                                                            placeholder="Optional"
                                                        />
                                                    </td>
                                                    <td className="px-3 py-2">
                                                        <input
                                                            type="number"
                                                            step="0.001"
                                                            value={line.quantity}
                                                            onChange={(e) => updateLine(index, 'quantity', e.target.value)}
                                                            className="block w-20 text-sm text-right border-gray-300 rounded-md"
                                                        />
                                                    </td>
                                                    <td className="px-3 py-2">
                                                        <input
                                                            type="text"
                                                            value={line.unit}
                                                            onChange={(e) => updateLine(index, 'unit', e.target.value)}
                                                            className="block w-20 text-sm border-gray-300 rounded-md"
                                                        />
                                                    </td>
                                                    <td className="px-3 py-2">
                                                        <input
                                                            type="number"
                                                            step="0.01"
                                                            value={line.unit_price}
                                                            onChange={(e) => updateLine(index, 'unit_price', e.target.value)}
                                                            className="block w-32 text-sm text-right border-gray-300 rounded-md"
                                                        />
                                                    </td>
                                                    <td className="px-3 py-2">
                                                        <input
                                                            type="number"
                                                            step="0.01"
                                                            value={line.discount_percent}
                                                            onChange={(e) => updateLine(index, 'discount_percent', e.target.value)}
                                                            className="block w-20 text-sm text-right border-gray-300 rounded-md"
                                                        />
                                                    </td>
                                                    <td className="px-3 py-2">
                                                        <input
                                                            type="number"
                                                            step="0.01"
                                                            value={line.discount_amount}
                                                            onChange={(e) => updateLine(index, 'discount_amount', e.target.value)}
                                                            className="block w-24 text-sm text-right border-gray-300 rounded-md"
                                                        />
                                                    </td>
                                                    <td className="px-3 py-2">
                                                        <input
                                                            type="number"
                                                            step="0.01"
                                                            value={line.tax_amount}
                                                            onChange={(e) => updateLine(index, 'tax_amount', e.target.value)}
                                                            className="block w-24 text-sm text-right border-gray-300 rounded-md"
                                                        />
                                                    </td>
                                                    <td className="px-3 py-2 text-sm text-right">
                                                        {new Intl.NumberFormat('id-ID').format(calculateLineTotal(line))}
                                                    </td>
                                                    <td className="px-3 py-2 text-center">
                                                        <button
                                                            type="button"
                                                            onClick={() => removeLine(index)}
                                                            className="text-red-600 hover:text-red-900"
                                                            disabled={data.lines.length === 1}
                                                        >
                                                            Hapus
                                                        </button>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {/* Totals */}
                            <div className="flex justify-end mb-6">
                                <div className="w-64 space-y-2">
                                    <div className="flex justify-between text-sm">
                                        <span>Subtotal:</span>
                                        <span>Rp {new Intl.NumberFormat('id-ID').format(subtotal)}</span>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <span>Tax:</span>
                                        <span>Rp {new Intl.NumberFormat('id-ID').format(totalTax)}</span>
                                    </div>
                                    <div className="flex justify-between pt-2 text-lg font-bold border-t">
                                        <span>Grand Total:</span>
                                        <span>Rp {new Intl.NumberFormat('id-ID').format(grandTotal)}</span>
                                    </div>
                                    {grandTotal > 10000000 && (
                                        <p className="text-sm text-yellow-600">* Memerlukan approval</p>
                                    )}
                                    {creditLimitWarning && (
                                        <p className="text-sm text-red-600">⚠️ Melebihi credit limit customer!</p>
                                    )}
                                </div>
                            </div>

                            {/* Actions */}
                            <div className="flex justify-end gap-4">
                                <Link
                                    href={route('sales-orders.index')}
                                    className="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
                                >
                                    Batal
                                </Link>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50"
                                >
                                    {processing ? 'Menyimpan...' : 'Update'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
