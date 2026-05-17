import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, suppliers, filters }) {
    const handleSearch = (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        router.get(route('suppliers.index'), {
            search: formData.get('search'),
            category: formData.get('category'),
            tax_type: formData.get('tax_type'),
        });
    };

    const handleDelete = (id) => {
        if (confirm('Are you sure you want to delete this supplier?')) {
            router.delete(route('suppliers.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Suppliers" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex justify-between items-center mb-6">
                                <h2 className="text-2xl font-semibold">Suppliers</h2>
                                <Link
                                    href={route('suppliers.create')}
                                    className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    Add Supplier
                                </Link>
                            </div>

                            {/* Filters */}
                            <form onSubmit={handleSearch} className="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                                <input
                                    type="text"
                                    name="search"
                                    placeholder="Search..."
                                    defaultValue={filters.search}
                                    className="border rounded px-3 py-2"
                                />
                                <select
                                    name="category"
                                    defaultValue={filters.category}
                                    className="border rounded px-3 py-2"
                                >
                                    <option value="">All Categories</option>
                                    <option value="Raw Material">Raw Material</option>
                                    <option value="Packaging">Packaging</option>
                                    <option value="Service">Service</option>
                                    <option value="Equipment">Equipment</option>
                                </select>
                                <select
                                    name="tax_type"
                                    defaultValue={filters.tax_type}
                                    className="border rounded px-3 py-2"
                                >
                                    <option value="">All Tax Types</option>
                                    <option value="pkp">PKP</option>
                                    <option value="non_pkp">Non-PKP</option>
                                </select>
                                <button
                                    type="submit"
                                    className="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    Filter
                                </button>
                            </form>

                            {/* Table */}
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tax Type</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {suppliers.data.map((supplier) => (
                                            <tr key={supplier.id}>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm">{supplier.supplier_code}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">{supplier.name}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm">{supplier.category || '-'}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm">
                                                    <span className={`px-2 py-1 rounded text-xs ${
                                                        supplier.tax_type === 'pkp' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                                                    }`}>
                                                        {supplier.tax_type === 'pkp' ? 'PKP' : 'Non-PKP'}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm">
                                                    {supplier.phone && <div>{supplier.phone}</div>}
                                                    {supplier.email && <div className="text-gray-500">{supplier.email}</div>}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                                    <Link
                                                        href={route('suppliers.show', supplier.id)}
                                                        className="text-blue-600 hover:text-blue-900"
                                                    >
                                                        View
                                                    </Link>
                                                    <Link
                                                        href={route('suppliers.edit', supplier.id)}
                                                        className="text-indigo-600 hover:text-indigo-900"
                                                    >
                                                        Edit
                                                    </Link>
                                                    <button
                                                        onClick={() => handleDelete(supplier.id)}
                                                        className="text-red-600 hover:text-red-900"
                                                    >
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            {/* Pagination */}
                            {suppliers.links && (
                                <div className="mt-4 flex justify-between items-center">
                                    <div className="text-sm text-gray-700">
                                        Showing {suppliers.from} to {suppliers.to} of {suppliers.total} results
                                    </div>
                                    <div className="flex space-x-2">
                                        {suppliers.links.map((link, index) => (
                                            <Link
                                                key={index}
                                                href={link.url}
                                                className={`px-3 py-1 rounded ${
                                                    link.active
                                                        ? 'bg-blue-500 text-white'
                                                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                                }`}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
