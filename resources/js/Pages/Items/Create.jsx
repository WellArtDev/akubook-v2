import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Create({ auth, options }) {
    const { data, setData, post, processing, errors } = useForm({
        code: '',
        name: '',
        category: '',
        description: '',
        item_type: 'goods',
        inventory_type: 'stock',
        valuation_method: 'moving_average',
        unit: 'pcs',
        purchase_price: 0,
        selling_price: 0,
        minimum_stock: 0,
        reorder_point: 0,
        is_active: true,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('items.store'));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Create Item" />
            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div className="flex justify-between items-center mb-6">
                            <h1 className="text-2xl font-semibold">Create Item</h1>
                            <Link href={route('items.index')} className="bg-gray-600 text-white px-4 py-2 rounded">Back</Link>
                        </div>

                        <form onSubmit={submit} className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {[
                                ['code', 'Code'], ['name', 'Name'], ['category', 'Category'], ['unit', 'Unit'],
                            ].map(([key, label]) => (
                                <div key={key}>
                                    <label className="block text-sm mb-1">{label}</label>
                                    <input value={data[key]} onChange={(e) => setData(key, e.target.value)} className="w-full border rounded px-3 py-2" />
                                    {errors[key] && <p className="text-red-500 text-xs">{errors[key]}</p>}
                                </div>
                            ))}

                            <div>
                                <label className="block text-sm mb-1">Item Type</label>
                                <select value={data.item_type} onChange={(e) => setData('item_type', e.target.value)} className="w-full border rounded px-3 py-2">
                                    {options.itemTypes.map((v) => <option key={v} value={v}>{v}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm mb-1">Inventory Type</label>
                                <select value={data.inventory_type} onChange={(e) => setData('inventory_type', e.target.value)} className="w-full border rounded px-3 py-2">
                                    {options.inventoryTypes.map((v) => <option key={v} value={v}>{v}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm mb-1">Valuation Method</label>
                                <select value={data.valuation_method} onChange={(e) => setData('valuation_method', e.target.value)} className="w-full border rounded px-3 py-2">
                                    {options.valuationMethods.map((v) => <option key={v} value={v}>{v}</option>)}
                                </select>
                            </div>

                            {[
                                ['purchase_price', 'Purchase Price'], ['selling_price', 'Selling Price'],
                                ['minimum_stock', 'Minimum Stock'], ['reorder_point', 'Reorder Point'],
                            ].map(([key, label]) => (
                                <div key={key}>
                                    <label className="block text-sm mb-1">{label}</label>
                                    <input type="number" step="0.001" value={data[key]} onChange={(e) => setData(key, e.target.value)} className="w-full border rounded px-3 py-2" />
                                    {errors[key] && <p className="text-red-500 text-xs">{errors[key]}</p>}
                                </div>
                            ))}

                            <div className="md:col-span-2">
                                <label className="block text-sm mb-1">Description</label>
                                <textarea value={data.description} onChange={(e) => setData('description', e.target.value)} className="w-full border rounded px-3 py-2" rows={3} />
                            </div>

                            <div className="md:col-span-2 flex items-center gap-2">
                                <input type="checkbox" checked={data.is_active} onChange={(e) => setData('is_active', e.target.checked)} />
                                <span>Active</span>
                            </div>

                            <div className="md:col-span-2 flex justify-end">
                                <button disabled={processing} className="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
