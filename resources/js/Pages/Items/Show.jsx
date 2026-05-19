import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Show({ auth, item, usage }) {
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`Item ${item.code}`} />
            <div className="py-12">
                <div className="max-w-5xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div className="flex justify-between items-center mb-6">
                            <div>
                                <h1 className="text-2xl font-semibold">{item.name}</h1>
                                <p className="text-gray-600">{item.code}</p>
                            </div>
                            <div className="space-x-2">
                                <Link href={route('items.edit', item.id)} className="bg-blue-600 text-white px-4 py-2 rounded">Edit</Link>
                                <Link href={route('items.index')} className="bg-gray-600 text-white px-4 py-2 rounded">Back</Link>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            {[
                                ['Category', item.category || '-'],
                                ['Item Type', item.item_type],
                                ['Inventory Type', item.inventory_type],
                                ['Valuation', item.valuation_method],
                                ['Unit', item.unit],
                                ['Purchase Price', Number(item.purchase_price).toLocaleString()],
                                ['Selling Price', Number(item.selling_price).toLocaleString()],
                                ['Minimum Stock', Number(item.minimum_stock).toLocaleString()],
                                ['Reorder Point', Number(item.reorder_point).toLocaleString()],
                                ['Status', item.is_active ? 'Active' : 'Inactive'],
                            ].map(([label, value]) => (
                                <div key={label} className="border rounded p-3">
                                    <div className="text-xs text-gray-500 uppercase">{label}</div>
                                    <div className="font-medium">{value}</div>
                                </div>
                            ))}
                        </div>

                        <div className="mb-6">
                            <h2 className="font-semibold mb-2">Usage</h2>
                            <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                                {Object.entries(usage).map(([key, value]) => (
                                    <div key={key} className="border rounded p-3">
                                        <div className="text-xs text-gray-500">{key}</div>
                                        <div className="text-xl font-semibold">{value}</div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {item.description && (
                            <div>
                                <h2 className="font-semibold mb-2">Description</h2>
                                <p className="whitespace-pre-wrap text-sm text-gray-700">{item.description}</p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
