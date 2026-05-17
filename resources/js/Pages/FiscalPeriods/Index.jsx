import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ auth, periods, fiscalYears, filters }) {
    const [search, setSearch] = useState(filters.search || '');
    const [fiscalYear, setFiscalYear] = useState(filters.fiscal_year || '');
    const [status, setStatus] = useState(filters.status || '');

    const handleSearch = (value) => {
        setSearch(value);
        router.get(route('fiscal-periods.index'), {
            search: value,
            fiscal_year: fiscalYear,
            status: status,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleFilterChange = (key, value) => {
        const params = { search, fiscal_year: fiscalYear, status };
        params[key] = value;
        
        if (key === 'fiscal_year') setFiscalYear(value);
        if (key === 'status') setStatus(value);

        router.get(route('fiscal-periods.index'), params, {
            preserveState: true,
            replace: true,
        });
    };

    const handleClose = (period) => {
        if (confirm(`Tutup periode ${period.name}? Periode yang ditutup tidak dapat menerima transaksi baru.`)) {
            router.post(route('fiscal-periods.close', period.id));
        }
    };

    const handleReopen = (period) => {
        if (confirm(`Buka kembali periode ${period.name}? Harap dokumentasikan alasan pembukaan kembali.`)) {
            router.post(route('fiscal-periods.reopen', period.id));
        }
    };

    const handleDelete = (period) => {
        if (confirm(`Hapus periode ${period.name}?`)) {
            router.delete(route('fiscal-periods.destroy', period.id));
        }
    };

    const getStatusBadge = (status) => {
        return status === 'open' 
            ? <span className="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Terbuka</span>
            : <span className="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Tertutup</span>;
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Periode Fiskal</h2>}
        >
            <Head title="Periode Fiskal" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {/* Header Actions */}
                            <div className="flex items-center justify-between mb-6">
                                <Link
                                    href={route('fiscal-periods.create')}
                                    className="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700"
                                >
                                    Tambah Periode
                                </Link>
                            </div>

                            {/* Filters */}
                            <div className="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Cari Nama</label>
                                    <input
                                        type="text"
                                        value={search}
                                        onChange={(e) => handleSearch(e.target.value)}
                                        placeholder="Cari nama periode..."
                                        className="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Tahun Fiskal</label>
                                    <select
                                        value={fiscalYear}
                                        onChange={(e) => handleFilterChange('fiscal_year', e.target.value)}
                                        className="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="">Semua Tahun</option>
                                        {fiscalYears.map(year => (
                                            <option key={year} value={year}>{year}</option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Status</label>
                                    <select
                                        value={status}
                                        onChange={(e) => handleFilterChange('status', e.target.value)}
                                        className="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="">Semua Status</option>
                                        <option value="open">Terbuka</option>
                                        <option value="closed">Tertutup</option>
                                    </select>
                                </div>
                            </div>

                            {/* Table */}
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Nama</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Tipe</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Tanggal Mulai</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Tanggal Akhir</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Tahun Fiskal</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Status</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {periods.data.map((period) => (
                                            <tr key={period.id}>
                                                <td className="px-6 py-4 whitespace-nowrap">{period.name}</td>
                                                <td className="px-6 py-4 whitespace-nowrap capitalize">{period.period_type}</td>
                                                <td className="px-6 py-4 whitespace-nowrap">{period.start_date}</td>
                                                <td className="px-6 py-4 whitespace-nowrap">{period.end_date}</td>
                                                <td className="px-6 py-4 whitespace-nowrap">{period.fiscal_year}</td>
                                                <td className="px-6 py-4 whitespace-nowrap">{getStatusBadge(period.status)}</td>
                                                <td className="px-6 py-4 text-right whitespace-nowrap">
                                                    <div className="flex justify-end gap-2">
                                                        {period.status === 'open' && (
                                                            <>
                                                                <Link
                                                                    href={route('fiscal-periods.edit', period.id)}
                                                                    className="text-blue-600 hover:text-blue-900"
                                                                >
                                                                    Edit
                                                                </Link>
                                                                <button
                                                                    onClick={() => handleClose(period)}
                                                                    className="text-orange-600 hover:text-orange-900"
                                                                >
                                                                    Tutup
                                                                </button>
                                                            </>
                                                        )}
                                                        {period.status === 'closed' && (
                                                            <button
                                                                onClick={() => handleReopen(period)}
                                                                className="text-green-600 hover:text-green-900"
                                                            >
                                                                Buka Kembali
                                                            </button>
                                                        )}
                                                        <button
                                                            onClick={() => handleDelete(period)}
                                                            className="text-red-600 hover:text-red-900"
                                                        >
                                                            Hapus
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            {/* Pagination */}
                            {periods.links.length > 3 && (
                                <div className="flex justify-center mt-6 gap-2">
                                    {periods.links.map((link, index) => (
                                        <Link
                                            key={index}
                                            href={link.url || '#'}
                                            className={`px-3 py-1 rounded ${
                                                link.active
                                                    ? 'bg-blue-600 text-white'
                                                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
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
