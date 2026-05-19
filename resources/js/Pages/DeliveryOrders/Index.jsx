import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, deliveryOrders, filters = {} }) {
    const updateFilter = (name, value) => {
        const next = { ...filters, [name]: value };
        router.get(route('delivery-orders.index'), next, { preserveState: true, replace: true });
    };

    const statuses = ['draft', 'ready_to_ship', 'in_transit', 'delivered', 'cancelled'];

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Delivery Orders" />
            <div className="py-6">
                <div className="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
                    <div className="bg-white p-4 shadow sm:rounded-lg">
                        <div className="mb-4 flex items-center justify-between">
                            <h1 className="text-xl font-semibold text-gray-900">Delivery Orders</h1>
                            <Link href={route('delivery-orders.create')} className="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                                Buat DO
                            </Link>
                        </div>
                        <div className="grid gap-3 md:grid-cols-5">
                            <input className="rounded border-gray-300" placeholder="Cari DO/SO" value={filters.search || ''} onChange={(e) => updateFilter('search', e.target.value)} />
                            <select className="rounded border-gray-300" value={filters.status || ''} onChange={(e) => updateFilter('status', e.target.value)}>
                                <option value="">Semua Status</option>
                                {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
                            </select>
                            <input type="date" className="rounded border-gray-300" value={filters.date_from || ''} onChange={(e) => updateFilter('date_from', e.target.value)} />
                            <input type="date" className="rounded border-gray-300" value={filters.date_to || ''} onChange={(e) => updateFilter('date_to', e.target.value)} />
                            <input className="rounded border-gray-300" placeholder="Driver" value={filters.driver || ''} onChange={(e) => updateFilter('driver', e.target.value)} />
                        </div>
                    </div>

                    <div className="overflow-hidden bg-white shadow sm:rounded-lg">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">DO</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tanggal</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">SO</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Customer</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                    <th className="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 bg-white">
                                {deliveryOrders.data.map((deliveryOrder) => (
                                    <tr key={deliveryOrder.id}>
                                        <td className="px-4 py-3 text-sm">{deliveryOrder.do_number}</td>
                                        <td className="px-4 py-3 text-sm">{deliveryOrder.do_date}</td>
                                        <td className="px-4 py-3 text-sm">{deliveryOrder.sales_order?.so_number}</td>
                                        <td className="px-4 py-3 text-sm">{deliveryOrder.customer?.name}</td>
                                        <td className="px-4 py-3 text-sm">{deliveryOrder.status}</td>
                                        <td className="px-4 py-3 text-right text-sm">
                                            <Link className="text-blue-600 hover:text-blue-800" href={route('delivery-orders.show', deliveryOrder.id)}>Detail</Link>
                                        </td>
                                    </tr>
                                ))}
                                {deliveryOrders.data.length === 0 && (
                                    <tr>
                                        <td colSpan={6} className="px-4 py-6 text-center text-sm text-gray-500">Tidak ada data</td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
