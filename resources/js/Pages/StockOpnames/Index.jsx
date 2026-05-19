import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, opnames, filters }) {
    const filter = (e) => {
        e.preventDefault();
        const form = new FormData(e.target);
        router.get(route('stock-opnames.index'), { status: form.get('status') || '' });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Stock Opname" />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white shadow-sm sm:rounded-lg p-6">
                        <div className="flex justify-between items-center mb-6">
                            <h1 className="text-2xl font-semibold">Stock Opname</h1>
                            <Link href={route('stock-opnames.create')} className="bg-blue-600 text-white px-4 py-2 rounded">Create</Link>
                        </div>
                        <form onSubmit={filter} className="flex gap-3 mb-6">
                            <select name="status" defaultValue={filters.status || ''} className="border rounded px-3 py-2">
                                <option value="">All Status</option>
                                <option value="draft">Draft</option>
                                <option value="confirmed">Confirmed</option>
                            </select>
                            <button className="bg-gray-700 text-white px-4 py-2 rounded">Filter</button>
                        </form>
                        <table className="min-w-full text-sm">
                            <thead><tr className="border-b"><th className="text-left py-2">Number</th><th>Date</th><th>Status</th><th>Created By</th><th></th></tr></thead>
                            <tbody>
                                {opnames.data.map((opname) => (
                                    <tr key={opname.id} className="border-b">
                                        <td className="py-2">{opname.opname_number}</td>
                                        <td>{opname.opname_date}</td>
                                        <td>{opname.status}</td>
                                        <td>{opname.creator?.name || '-'}</td>
                                        <td><Link href={route('stock-opnames.show', opname.id)} className="text-blue-600">View</Link></td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
