import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, items, filters }) {
    const filter = (e) => {
        e.preventDefault();
        const form = new FormData(e.target);
        router.get(route('items.index'), {
            search: form.get('search') || '',
            is_active: form.get('is_active') || '',
            inventory_type: form.get('inventory_type') || '',
        });
    };

    const remove = (id) => {
        if (confirm('Delete item?')) {
            router.delete(route('items.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Items" />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div className="flex justify-between items-center mb-6">
                            <h1 className="text-2xl font-semibold">Item Master</h1>
                            <Link href={route('items.create')} className="bg-blue-600 text-white px-4 py-2 rounded">Add Item</Link>
                        </div>

                        <form onSubmit={filter} className="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
                            <input name="search" defaultValue={filters.search || ''} className="border rounded px-3 py-2" placeholder="Search code/name/category" />
                            <select name="is_active" defaultValue={filters.is_active || ''} className="border rounded px-3 py-2">
                                <option value="">All Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <select name="inventory_type" defaultValue={filters.inventory_type || ''} className="border rounded px-3 py-2">
                                <option value="">All Inventory Type</option>
                                <option value="stock">Stock</option>
                                <option value="non_stock">Non Stock</option>
                            </select>
                            <button className="bg-gray-700 text-white px-4 py-2 rounded">Filter</button>
                        </form>

                        <div className="overflow-x-auto">
                            <table className="min-w-full text-sm">
                                <thead>
                                    <tr className="border-b">
                                        <th className="text-left py-2">Code</th>
                                        <th className="text-left py-2">Name</th>
                                        <th className="text-left py-2">Category</th>
                                        <th className="text-left py-2">Unit</th>
                                        <th className="text-left py-2">Type</th>
                                        <th className="text-left py-2">Status</th>
                                        <th className="text-left py-2">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {items.data.map((item) => (
                                        <tr key={item.id} className="border-b">
                                            <td className="py-2">{item.code}</td>
                                            <td className="py-2">{item.name}</td>
                                            <td className="py-2">{item.category || '-'}</td>
                                            <td className="py-2">{item.unit}</td>
                                            <td className="py-2">{item.inventory_type}</td>
                                            <td className="py-2">{item.is_active ? 'Active' : 'Inactive'}</td>
                                            <td className="py-2 space-x-2">
                                                <Link href={route('items.show', item.id)} className="text-blue-600">View</Link>
                                                <Link href={route('items.edit', item.id)} className="text-indigo-600">Edit</Link>
                                                <button onClick={() => remove(item.id)} className="text-red-600">Delete</button>
                                            </td>
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
