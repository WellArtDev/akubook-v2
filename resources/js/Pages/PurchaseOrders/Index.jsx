import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ auth, purchaseOrders, filters }) {
    const [search, setSearch] = useState(filters.search || '');

    const getStatusBadge = (status) => {
        const badges = {
            draft: <span className="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">Draft</span>,
            pending_approval: <span className="px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded-full">Pending Approval</span>,
            approved: <span className="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Approved</span>,
            in_progress: <span className="px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full">In Progress</span>,
            completed: <span className="px-2 py-1 text-xs font-semibold text-purple-800 bg-purple-100 rounded-full">Completed</span>,
            cancelled: <span className="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Cancelled</span>,
        };
        return badges[status] || status;
    };

    const handleDelete = (po) => {
        if (confirm(`Hapus Purchase Order ${po.po_number}?`)) {
            router.delete(route('purchase-orders.destroy', po.id));
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Purchase Orders</h2>}
        >
            <Head title="Purchase Orders" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex items-center justify-between mb-6">
                                <Link
                                    href={route('purchase-orders.create')}
                                    className="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700"
                                >
                                    Tambah Purchase Order
                                </Link>
                            </div>

                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">PO Number</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Date</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Supplier</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Grand Total</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Status</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {purchaseOrders.data.map((po) => (
                                            <tr key={po.id}>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <Link href={route('purchase-orders.show', po.id)} className="text-blue-600 hover:text-blue-900">
                                                        {po.po_number}
                                                    </Link>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">{po.po_date}</td>
                                                <td className="px-6 py-4">{po.supplier?.name}</td>
                                                <td className="px-6 py-4 text-right whitespace-nowrap">
                                                    Rp {new Intl.NumberFormat('id-ID').format(po.grand_total)}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">{getStatusBadge(po.status)}</td>
                                                <td className="px-6 py-4 text-right whitespace-nowrap">
                                                    <div className="flex justify-end gap-2">
                                                        {(po.status === 'draft' || po.status === 'pending_approval') && (
                                                            <>
                                                                <Link href={route('purchase-orders.edit', po.id)} className="text-blue-600 hover:text-blue-900">
                                                                    Edit
                                                                </Link>
                                                                {po.status === 'draft' && (
                                                                    <button onClick={() => handleDelete(po)} className="text-red-600 hover:text-red-900">
                                                                        Hapus
                                                                    </button>
                                                                )}
                                                            </>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            {purchaseOrders.links.length > 3 && (
                                <div className="flex justify-center mt-6 gap-2">
                                    {purchaseOrders.links.map((link, index) => (
                                        <Link
                                            key={index}
                                            href={link.url || '#'}
                                            className={`px-3 py-1 rounded ${
                                                link.active ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                            } ${!link.url && 'opacity-50 cursor-not-allowed'}`}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
