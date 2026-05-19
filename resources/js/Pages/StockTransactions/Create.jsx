import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Create({ auth, items, movementTypes }) {
    const { data, setData, post, processing, errors } = useForm({
        item_id: items[0]?.id || '',
        movement_type: 'adjustment',
        quantity: 1,
        direction: 'in',
        movement_date: new Date().toISOString().slice(0, 10),
        reference_type: 'manual',
        reference_id: '',
        notes: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('stock-transactions.store'));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Stock Adjustment" />
            <div className="py-12">
                <div className="max-w-3xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white shadow-sm sm:rounded-lg p-6">
                        <div className="flex justify-between items-center mb-6">
                            <h1 className="text-2xl font-semibold">Stock Movement</h1>
                            <Link href={route('stock-transactions.index')} className="bg-gray-600 text-white px-4 py-2 rounded">Back</Link>
                        </div>
                        <form onSubmit={submit} className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm mb-1">Item</label>
                                <select value={data.item_id} onChange={(e) => setData('item_id', e.target.value)} className="w-full border rounded px-3 py-2">
                                    {items.map((item) => <option key={item.id} value={item.id}>{item.code} - {item.name}</option>)}
                                </select>
                                {errors.item_id && <p className="text-red-500 text-xs">{errors.item_id}</p>}
                            </div>
                            <div>
                                <label className="block text-sm mb-1">Movement Type</label>
                                <select value={data.movement_type} onChange={(e) => setData('movement_type', e.target.value)} className="w-full border rounded px-3 py-2">
                                    {movementTypes.map((type) => <option key={type} value={type}>{type}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm mb-1">Direction</label>
                                <select value={data.direction} onChange={(e) => setData('direction', e.target.value)} className="w-full border rounded px-3 py-2">
                                    <option value="in">In</option>
                                    <option value="out">Out</option>
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm mb-1">Quantity</label>
                                <input type="number" step="0.001" value={data.quantity} onChange={(e) => setData('quantity', e.target.value)} className="w-full border rounded px-3 py-2" />
                                {errors.quantity && <p className="text-red-500 text-xs">{errors.quantity}</p>}
                            </div>
                            <div>
                                <label className="block text-sm mb-1">Date</label>
                                <input type="date" value={data.movement_date} onChange={(e) => setData('movement_date', e.target.value)} className="w-full border rounded px-3 py-2" />
                            </div>
                            <div>
                                <label className="block text-sm mb-1">Reference Type</label>
                                <input value={data.reference_type} onChange={(e) => setData('reference_type', e.target.value)} className="w-full border rounded px-3 py-2" />
                            </div>
                            <div>
                                <label className="block text-sm mb-1">Reference ID</label>
                                <input type="number" value={data.reference_id} onChange={(e) => setData('reference_id', e.target.value)} className="w-full border rounded px-3 py-2" />
                            </div>
                            <div className="md:col-span-2">
                                <label className="block text-sm mb-1">Notes</label>
                                <textarea value={data.notes} onChange={(e) => setData('notes', e.target.value)} className="w-full border rounded px-3 py-2" rows={3} />
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
