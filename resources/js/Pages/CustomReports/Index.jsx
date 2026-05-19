import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, reports, filters, sources }) {
    const updateFilter = (key, value) => {
        router.get(route('custom-reports.index'), { ...filters, [key]: value }, { preserveState: true, replace: true });
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Custom Reports</h2>}>
            <Head title="Custom Reports" />
            <div className="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div className="flex justify-between items-center gap-2">
                    <div className="flex gap-2">
                        <input className="border rounded px-3 py-2" placeholder="Search" value={filters.search || ''} onChange={(e) => updateFilter('search', e.target.value)} />
                        <select className="border rounded px-3 py-2" value={filters.source_key || ''} onChange={(e) => updateFilter('source_key', e.target.value)}>
                            <option value="">All Source</option>
                            {sources.map((source) => <option key={source.key} value={source.key}>{source.label}</option>)}
                        </select>
                        <select className="border rounded px-3 py-2" value={filters.is_active || ''} onChange={(e) => updateFilter('is_active', e.target.value)}>
                            <option value="">All Status</option>
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                    <Link href={route('custom-reports.create')} className="px-4 py-2 bg-indigo-600 text-white rounded">Tambah</Link>
                </div>
                <div className="bg-white rounded shadow overflow-hidden">
                    <table className="min-w-full divide-y">
                        <thead className="bg-gray-50"><tr><th className="px-4 py-2 text-left">Code</th><th className="px-4 py-2 text-left">Name</th><th className="px-4 py-2 text-left">Source</th><th className="px-4 py-2 text-left">Status</th><th className="px-4 py-2 text-left">Aksi</th></tr></thead>
                        <tbody className="divide-y">
                            {reports.data.map((report) => (
                                <tr key={report.id}>
                                    <td className="px-4 py-2">{report.code}</td>
                                    <td className="px-4 py-2">{report.name}</td>
                                    <td className="px-4 py-2">{report.source_key}</td>
                                    <td className="px-4 py-2">{report.is_active ? 'active' : 'inactive'}</td>
                                    <td className="px-4 py-2 space-x-2">
                                        <Link href={route('custom-reports.show', report.id)} className="text-indigo-600">Detail</Link>
                                        <Link href={route('custom-reports.edit', report.id)} className="text-blue-600">Edit</Link>
                                        <button onClick={() => router.delete(route('custom-reports.destroy', report.id))} className="text-red-600">Hapus</button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
