import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, salesOrders, filters }) {
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

    const handleDelete = (so) => {
        if (confirm(`Hapus Sales Order ${so.so_number}?`)) {
            router.delete(route('sales-orders.destroy', so.id));
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Sales Orders</h2>}
        >
            <Head title="Sales Orders" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex items-center justify-between mb-6">
                                <Link
                                    href={route('sales-orders.create')}
                                    className="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700"
                                >
                                    Tambah Sales Order
                                </Link>
                            </div>

                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">SO Number</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Date</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Customer</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Grand Total</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Status</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {salesOrders.data.map((so) => (
                                            <tr key={so.id}>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <Link href={route('sales-orders.show', so.id)} className="text-blue-600 hover:text-blue-900">
                                                        {so.so_number}
                                                    </Link>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">{so.so_date}</td>
                                                <td className="px-6 py-4">{so.customer?.name}</td>
                                                <td className="px-6 py-4 text-right whitespace-nowrap">
                                                    Rp {new Intl.NumberFormat('id-ID').format(so.grand_total)}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">{getStatusBadge(so.status)}</td>
                                                <td className="px-6 py-4 text-right whitespace-nowrap">
                                                    <div className="flex justify-end gap-2">
                                                        {(so.status === 'draft' || so.status === 'pending_approval') && (
                                                            <>
                                                                <Link href={route('sales-orders.edit', so.id)} className="text-blue-600 hover:text-blue-900">
                                                                    Edit
                                                                </Link>
                                                                {so.status === 'draft' && (
                                                                    <button onClick={() => handleDelete(so)} className="text-red-600 hover:text-red-900">
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

                            {salesOrders.links.length > 3 && (
                                <div className="flex justify-center mt-6 gap-2">
                                    {salesOrders.links.map((link, index) => (
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
