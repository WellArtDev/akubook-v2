import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, transactions, balances, items, movementTypes, filters }) {
    const submit = (e) => {
        e.preventDefault();
        const form = new FormData(e.target);
        router.get(route('stock-transactions.index'), {
            item_id: form.get('item_id') || '',
            movement_type: form.get('movement_type') || '',
            date_from: form.get('date_from') || '',
            date_to: form.get('date_to') || '',
        });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Stock Tracking" />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="bg-white shadow-sm sm:rounded-lg p-6">
                        <div className="flex justify-between items-center mb-6">
                            <h1 className="text-2xl font-semibold">Stock Tracking</h1>
                            <Link href={route('stock-transactions.create')} className="bg-blue-600 text-white px-4 py-2 rounded">Adjustment</Link>
                        </div>
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-3">
                            {balances.map((item) => (
                                <div key={item.id} className="border rounded p-3">
                                    <div className="text-xs text-gray-500">{item.code}</div>
                                    <div className="font-medium">{item.name}</div>
                                    <div className="text-xl font-semibold">{Number(item.current_stock).toLocaleString()}</div>
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="bg-white shadow-sm sm:rounded-lg p-6">
                        <form onSubmit={submit} className="grid grid-cols-1 md:grid-cols-5 gap-3 mb-6">
                            <select name="item_id" defaultValue={filters.item_id || ''} className="border rounded px-3 py-2">
                                <option value="">All Items</option>
                                {items.map((item) => <option key={item.id} value={item.id}>{item.code} - {item.name}</option>)}
                            </select>
                            <select name="movement_type" defaultValue={filters.movement_type || ''} className="border rounded px-3 py-2">
                                <option value="">All Types</option>
                                {movementTypes.map((type) => <option key={type} value={type}>{type}</option>)}
                            </select>
                            <input type="date" name="date_from" defaultValue={filters.date_from || ''} className="border rounded px-3 py-2" />
                            <input type="date" name="date_to" defaultValue={filters.date_to || ''} className="border rounded px-3 py-2" />
                            <button className="bg-gray-700 text-white px-4 py-2 rounded">Filter</button>
                        </form>

                        <div className="overflow-x-auto">
                            <table className="min-w-full text-sm">
                                <thead>
                                    <tr className="border-b">
                                        <th className="text-left py-2">Date</th>
                                        <th className="text-left py-2">Item</th>
                                        <th className="text-left py-2">Type</th>
                                        <th className="text-right py-2">In</th>
                                        <th className="text-right py-2">Out</th>
                                        <th className="text-left py-2">Reference</th>
                                        <th className="text-left py-2">User</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {transactions.data.map((trx) => (
                                        <tr key={trx.id} className="border-b">
                                            <td className="py-2">{trx.movement_date}</td>
                                            <td className="py-2">{trx.item?.code} - {trx.item?.name}</td>
                                            <td className="py-2">{trx.movement_type}</td>
                                            <td className="py-2 text-right">{Number(trx.quantity_in).toLocaleString()}</td>
                                            <td className="py-2 text-right">{Number(trx.quantity_out).toLocaleString()}</td>
                                            <td className="py-2">{trx.reference_type || '-'} {trx.reference_id || ''}</td>
                                            <td className="py-2">{trx.creator?.name || '-'}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
