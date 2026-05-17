import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function Create({ auth, salesOrders, customers }) {
    const { data, setData, post, processing, errors } = useForm({
        invoice_date: new Date().toISOString().split('T')[0],
        due_date: '',
        sales_order_id: '',
        customer_id: '',
        billing_address: '',
        payment_terms: '',
        reference: '',
        notes: '',
        generate_tax_invoice: false,
        lines: [{ product_name: '', quantity: 1, unit: 'pcs', unit_price: 0, discount_amount: 0 }],
    });

    const addLine = () => {
        setData('lines', [...data.lines, { product_name: '', quantity: 1, unit: 'pcs', unit_price: 0, discount_amount: 0 }]);
    };

    const removeLine = (index) => {
        setData('lines', data.lines.filter((_, i) => i !== index));
    };

    const updateLine = (index, field, value) => {
        const newLines = [...data.lines];
        newLines[index][field] = value;
        setData('lines', newLines);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('sales-invoices.store'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Buat Invoice</h2>}
        >
            <Head title="Buat Invoice" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <form onSubmit={handleSubmit} className="p-6">
                            <div className="grid grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label className="block mb-2 text-sm font-medium">Tanggal Invoice *</label>
                                    <input
                                        type="date"
                                        value={data.invoice_date}
                                        onChange={(e) => setData('invoice_date', e.target.value)}
                                        className="w-full px-3 py-2 border rounded-md"
                                        required
                                    />
                                    {errors.invoice_date && <p className="mt-1 text-sm text-red-600">{errors.invoice_date}</p>}
                                </div>

                                <div>
                                    <label className="block mb-2 text-sm font-medium">Jatuh Tempo *</label>
                                    <input
                                        type="date"
                                        value={data.due_date}
                                        onChange={(e) => setData('due_date', e.target.value)}
                                        className="w-full px-3 py-2 border rounded-md"
                                        required
                                    />
                                    {errors.due_date && <p className="mt-1 text-sm text-red-600">{errors.due_date}</p>}
                                </div>

                                <div>
                                    <label className="block mb-2 text-sm font-medium">Sales Order *</label>
                                    <select
                                        value={data.sales_order_id}
                                        onChange={(e) => setData('sales_order_id', e.target.value)}
                                        className="w-full px-3 py-2 border rounded-md"
                                        required
                                    >
                                        <option value="">Pilih Sales Order</option>
                                        {salesOrders.map((so) => (
                                            <option key={so.id} value={so.id}>
                                                {so.so_number} - {so.customer?.name}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.sales_order_id && <p className="mt-1 text-sm text-red-600">{errors.sales_order_id}</p>}
                                </div>

                                <div>
                                    <label className="block mb-2 text-sm font-medium">Customer *</label>
                                    <select
                                        value={data.customer_id}
                                        onChange={(e) => setData('customer_id', e.target.value)}
                                        className="w-full px-3 py-2 border rounded-md"
                                        required
                                    >
                                        <option value="">Pilih Customer</option>
                                        {customers.map((customer) => (
                                            <option key={customer.id} value={customer.id}>
                                                {customer.name}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.customer_id && <p className="mt-1 text-sm text-red-600">{errors.customer_id}</p>}
                                </div>

                                <div className="col-span-2">
                                    <label className="block mb-2 text-sm font-medium">Billing Address</label>
                                    <textarea
                                        value={data.billing_address}
                                        onChange={(e) => setData('billing_address', e.target.value)}
                                        className="w-full px-3 py-2 border rounded-md"
                                        rows="2"
                                    />
                                </div>

                                <div>
                                    <label className="block mb-2 text-sm font-medium">Payment Terms</label>
                                    <input
                                        type="text"
                                        value={data.payment_terms}
                                        onChange={(e) => setData('payment_terms', e.target.value)}
                                        className="w-full px-3 py-2 border rounded-md"
                                        placeholder="Net 30"
                                    />
                                </div>

                                <div>
                                    <label className="flex items-center">
                                        <input
                                            type="checkbox"
                                            checked={data.generate_tax_invoice}
                                            onChange={(e) => setData('generate_tax_invoice', e.target.checked)}
                                            className="mr-2"
                                        />
                                        <span className="text-sm">Generate Tax Invoice (Faktur Pajak)</span>
                                    </label>
                                </div>
                            </div>

                            <div className="mb-6">
                                <div className="flex items-center justify-between mb-4">
                                    <h3 className="text-lg font-semibold">Items</h3>
                                    <button type="button" onClick={addLine} className="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700">
                                        Tambah Item
                                    </button>
                                </div>

                                <div className="space-y-4">
                                    {data.lines.map((line, index) => (
                                        <div key={index} className="grid grid-cols-12 gap-4 p-4 border rounded-md">
                                            <div className="col-span-3">
                                                <input
                                                    type="text"
                                                    value={line.product_name}
                                                    onChange={(e) => updateLine(index, 'product_name', e.target.value)}
                                                    placeholder="Product Name"
                                                    className="w-full px-3 py-2 border rounded-md"
                                                    required
                                                />
                                            </div>
                                            <div className="col-span-2">
                                                <input
                                                    type="number"
                                                    value={line.quantity}
                                                    onChange={(e) => updateLine(index, 'quantity', parseFloat(e.target.value))}
                                                    placeholder="Qty"
                                                    className="w-full px-3 py-2 border rounded-md"
                                                    step="0.001"
                                                    required
                                                />
                                            </div>
                                            <div className="col-span-1">
                                                <input
                                                    type="text"
                                                    value={line.unit}
                                                    onChange={(e) => updateLine(index, 'unit', e.target.value)}
                                                    placeholder="Unit"
                                                    className="w-full px-3 py-2 border rounded-md"
                                                    required
                                                />
                                            </div>
                                            <div className="col-span-2">
                                                <input
                                                    type="number"
                                                    value={line.unit_price}
                                                    onChange={(e) => updateLine(index, 'unit_price', parseFloat(e.target.value))}
                                                    placeholder="Price"
                                                    className="w-full px-3 py-2 border rounded-md"
                                                    step="0.01"
                                                    required
                                                />
                                            </div>
                                            <div className="col-span-2">
                                                <input
                                                    type="number"
                                                    value={line.discount_amount}
                                                    onChange={(e) => updateLine(index, 'discount_amount', parseFloat(e.target.value))}
                                                    placeholder="Discount"
                                                    className="w-full px-3 py-2 border rounded-md"
                                                    step="0.01"
                                                />
                                            </div>
                                            <div className="col-span-2 flex items-center">
                                                {data.lines.length > 1 && (
                                                    <button
                                                        type="button"
                                                        onClick={() => removeLine(index)}
                                                        className="px-3 py-2 text-white bg-red-600 rounded-md hover:bg-red-700"
                                                    >
                                                        Hapus
                                                    </button>
                                                )}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <div className="flex justify-end gap-4">
                                <Link href={route('sales-invoices.index')} className="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                                    Batal
                                </Link>
                                <button type="submit" disabled={processing} className="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50">
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
