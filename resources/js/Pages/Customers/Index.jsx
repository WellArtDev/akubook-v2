import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, customers, filters }) {
    const handleSearch = (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        router.get(route('customers.index'), {
            search: formData.get('search'),
            category: formData.get('category'),
            credit_status: formData.get('credit_status'),
            sort: formData.get('sort'),
        });
    };

    const handleDelete = (id) => {
        if (confirm('Are you sure you want to delete this customer?')) {
            router.delete(route('customers.destroy', id));
        }
    };

    const creditStatusClass = (status) => ({
        good: 'bg-green-100 text-green-800',
        warning: 'bg-yellow-100 text-yellow-800',
        exceeded: 'bg-red-100 text-red-800',
    }[status] || 'bg-gray-100 text-gray-800');

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Customers" />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex justify-between items-center mb-6">
                                <h2 className="text-2xl font-semibold">Customers</h2>
                                <Link href={route('customers.create')} className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Add Customer</Link>
                            </div>
                            <form onSubmit={handleSearch} className="mb-6 grid grid-cols-1 md:grid-cols-5 gap-4">
                                <input type="text" name="search" placeholder="Search code, name, phone" defaultValue={filters.search} className="border rounded px-3 py-2" />
                                <select name="category" defaultValue={filters.category} className="border rounded px-3 py-2">
                                    <option value="">All Categories</option>
                                    <option value="retail">Retail</option>
                                    <option value="wholesale">Wholesale</option>
                                    <option value="corporate">Corporate</option>
                                </select>
                                <select name="credit_status" defaultValue={filters.credit_status} className="border rounded px-3 py-2">
                                    <option value="">All Credit Status</option>
                                    <option value="good">Good</option>
                                    <option value="warning">Warning</option>
                                    <option value="exceeded">Exceeded</option>
                                </select>
                                <select name="sort" defaultValue={filters.sort} className="border rounded px-3 py-2">
                                    <option value="">Newest</option>
                                    <option value="code">Code</option>
                                    <option value="name">Name</option>
                                    <option value="outstanding_balance">Outstanding</option>
                                </select>
                                <button type="submit" className="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Filter</button>
                            </form>
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                            <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                            <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                            <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                                            <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Credit Limit</th>
                                            <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Outstanding</th>
                                            <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                            <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {customers.data.map((customer) => (
                                            <tr key={customer.id}>
                                                <td className="px-4 py-3 text-sm">{customer.code}</td>
                                                <td className="px-4 py-3 text-sm font-medium">{customer.name}</td>
                                                <td className="px-4 py-3 text-sm capitalize">{customer.category}</td>
                                                <td className="px-4 py-3 text-sm">{customer.phone}</td>
                                                <td className="px-4 py-3 text-sm">Rp {Number(customer.credit_limit).toLocaleString()}</td>
                                                <td className="px-4 py-3 text-sm">Rp {Number(customer.outstanding_balance).toLocaleString()}</td>
                                                <td className="px-4 py-3 text-sm"><span className={`px-2 py-1 rounded text-xs capitalize ${creditStatusClass(customer.credit_status)}`}>{customer.credit_status}</span></td>
                                                <td className="px-4 py-3 text-sm space-x-2">
                                                    <Link href={route('customers.show', customer.id)} className="text-blue-600 hover:text-blue-900">View</Link>
                                                    <Link href={route('customers.edit', customer.id)} className="text-indigo-600 hover:text-indigo-900">Edit</Link>
                                                    <button onClick={() => handleDelete(customer.id)} className="text-red-600 hover:text-red-900">Delete</button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                            {customers.links && <div className="mt-4 flex space-x-2">{customers.links.map((link, index) => <Link key={index} href={link.url || '#'} className={`px-3 py-1 rounded ${link.active ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700'}`} dangerouslySetInnerHTML={{ __html: link.label }} />)}</div>}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
