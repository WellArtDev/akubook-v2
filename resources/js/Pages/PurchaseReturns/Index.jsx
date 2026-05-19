import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, returns, filters = {} }) {
    const updateFilter = (key, value) => {
        router.get(route('purchase-returns.index'), { ...filters, [key]: value || undefined }, { preserveState: true, replace: true });
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Purchase Returns</h2>}>
            <Head title="Purchase Returns" />

            <div className="py-6">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
                    <div className="bg-white p-4 shadow-sm sm:rounded-lg flex items-center justify-between gap-4">
                        <div className="flex gap-2">
                            <input
                                className="border rounded px-3 py-2 text-sm"
                                placeholder="Search return/supplier"
                                defaultValue={filters.search || ''}
                                onBlur={(e) => updateFilter('search', e.target.value)}
                            />
                            <select
                                className="border rounded px-3 py-2 text-sm"
                                value={filters.status || ''}
                                onChange={(e) => updateFilter('status', e.target.value)}
                            >
                                <option value="">All Status</option>
                                <option value="draft">Draft</option>
                                <option value="approved">Approved</option>
                                <option value="received">Received</option>
                                <option value="completed">Completed</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <Link href={route('purchase-returns.create')} className="px-4 py-2 bg-indigo-600 text-white rounded text-sm">
                            New Return
                        </Link>
                    </div>

                    <div className="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                        <table className="min-w-full text-sm">
                            <thead className="bg-gray-50 text-left">
                                <tr>
                                    <th className="px-4 py-3">Number</th>
                                    <th className="px-4 py-3">Date</th>
                                    <th className="px-4 py-3">Supplier</th>
                                    <th className="px-4 py-3">Status</th>
                                    <th className="px-4 py-3 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                {returns.data.map((row) => (
                                    <tr
                                        key={row.id}
                                        className="border-t hover:bg-gray-50 cursor-pointer"
                                        onClick={() => router.visit(route('purchase-returns.show', row.id))}
                                    >
                                        <td className="px-4 py-3 font-medium">{row.return_number}</td>
                                        <td className="px-4 py-3">{row.return_date}</td>
                                        <td className="px-4 py-3">{row.supplier?.name}</td>
                                        <td className="px-4 py-3 capitalize">{row.status}</td>
                                        <td className="px-4 py-3 text-right">{Number(row.total_amount || 0).toLocaleString('id-ID')}</td>
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
