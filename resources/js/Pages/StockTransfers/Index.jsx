import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, transfers, branches, filters }) {
    const updateFilter = (key, value) => {
        const next = { ...filters, [key]: value };
        router.get(route('stock-transfers.index'), next, {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Stock Transfer</h2>}
        >
            <Head title="Stock Transfer" />

            <div className="py-6">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
                    <div className="bg-white p-4 shadow sm:rounded-lg flex justify-between items-center">
                        <div className="font-semibold">Daftar Transfer</div>
                        <Link href={route('stock-transfers.create')} className="px-3 py-2 bg-indigo-600 text-white rounded text-sm">
                            Buat Transfer
                        </Link>
                    </div>

                    <div className="bg-white p-4 shadow sm:rounded-lg grid grid-cols-1 md:grid-cols-5 gap-3">
                        <input
                            type="text"
                            value={filters.search || ''}
                            onChange={(e) => updateFilter('search', e.target.value)}
                            placeholder="Cari nomor transfer"
                            className="border rounded px-3 py-2 text-sm"
                        />
                        <select
                            value={filters.status || ''}
                            onChange={(e) => updateFilter('status', e.target.value)}
                            className="border rounded px-3 py-2 text-sm"
                        >
                            <option value="">Semua Status</option>
                            <option value="draft">Draft</option>
                            <option value="confirmed">Confirmed</option>
                        </select>
                        <select
                            value={filters.branch_id || ''}
                            onChange={(e) => updateFilter('branch_id', e.target.value)}
                            className="border rounded px-3 py-2 text-sm"
                        >
                            <option value="">Semua Lokasi</option>
                            {branches.map((branch) => (
                                <option key={branch.id} value={branch.id}>{branch.name}</option>
                            ))}
                        </select>
                        <input
                            type="date"
                            value={filters.date_from || ''}
                            onChange={(e) => updateFilter('date_from', e.target.value)}
                            className="border rounded px-3 py-2 text-sm"
                        />
                        <input
                            type="date"
                            value={filters.date_to || ''}
                            onChange={(e) => updateFilter('date_to', e.target.value)}
                            className="border rounded px-3 py-2 text-sm"
                        />
                    </div>

                    <div className="bg-white shadow sm:rounded-lg overflow-x-auto">
                        <table className="min-w-full text-sm">
                            <thead className="bg-gray-50 text-left text-gray-600 uppercase text-xs">
                                <tr>
                                    <th className="px-4 py-3">Nomor</th>
                                    <th className="px-4 py-3">Tanggal</th>
                                    <th className="px-4 py-3">Dari</th>
                                    <th className="px-4 py-3">Ke</th>
                                    <th className="px-4 py-3">Status</th>
                                    <th className="px-4 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {transfers.data.map((transfer) => (
                                    <tr key={transfer.id} className="border-t">
                                        <td className="px-4 py-3 font-medium">{transfer.transfer_number}</td>
                                        <td className="px-4 py-3">{transfer.transfer_date}</td>
                                        <td className="px-4 py-3">{transfer.from_branch?.name}</td>
                                        <td className="px-4 py-3">{transfer.to_branch?.name}</td>
                                        <td className="px-4 py-3">{transfer.status}</td>
                                        <td className="px-4 py-3">
                                            <Link href={route('stock-transfers.show', transfer.id)} className="text-indigo-600 hover:underline">
                                                Detail
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                                {transfers.data.length === 0 && (
                                    <tr>
                                        <td colSpan={6} className="px-4 py-6 text-center text-gray-500">Belum ada transfer.</td>
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
