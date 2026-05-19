import React from 'react';
import { Head, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, valuations, totalValue, filters }) {
    const submit = (e) => {
        e.preventDefault();
        const form = new FormData(e.target);
        router.get(route('inventory-valuations.index'), {
            search: form.get('search') || '',
        });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Inventory Valuation" />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="bg-white shadow-sm sm:rounded-lg p-6">
                        <div className="flex justify-between items-center mb-4">
                            <h1 className="text-2xl font-semibold">Inventory Valuation</h1>
                            <div className="text-right">
                                <div className="text-xs text-gray-500">Total Inventory Value</div>
                                <div className="text-xl font-bold">{Number(totalValue).toLocaleString()}</div>
                            </div>
                        </div>

                        <form onSubmit={submit} className="mb-4 flex gap-2">
                            <input
                                name="search"
                                defaultValue={filters.search || ''}
                                placeholder="Cari code / name"
                                className="border rounded px-3 py-2 w-full max-w-sm"
                            />
                            <button className="bg-gray-700 text-white px-4 py-2 rounded">Filter</button>
                        </form>

                        <div className="overflow-x-auto">
                            <table className="min-w-full text-sm">
                                <thead>
                                    <tr className="border-b">
                                        <th className="text-left py-2">Code</th>
                                        <th className="text-left py-2">Name</th>
                                        <th className="text-left py-2">Method</th>
                                        <th className="text-right py-2">Stock</th>
                                        <th className="text-right py-2">Avg Cost</th>
                                        <th className="text-right py-2">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {valuations.map((row) => (
                                        <tr key={row.id} className="border-b">
                                            <td className="py-2">{row.code}</td>
                                            <td className="py-2">{row.name}</td>
                                            <td className="py-2">{row.valuation_method}</td>
                                            <td className="py-2 text-right">{Number(row.current_stock).toLocaleString()}</td>
                                            <td className="py-2 text-right">{Number(row.average_cost).toLocaleString()}</td>
                                            <td className="py-2 text-right font-medium">{Number(row.inventory_value).toLocaleString()}</td>
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
