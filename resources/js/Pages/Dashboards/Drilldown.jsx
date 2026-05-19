import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Drilldown({ auth, widget, title, columns, rows, summary, filters }) {
    const setFilter = (key, value) => {
        router.get(route('role-dashboard.drilldown', widget), { ...filters, [key]: value }, { preserveState: true, replace: true });
    };

    const formatValue = (value) => {
        if (typeof value === 'number') return value.toLocaleString('id-ID');
        return value ?? '-';
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Dashboard Drill Down</h2>}>
            <Head title={`Drill Down - ${title}`} />
            <div className="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">{title}</h1>
                        <p className="text-sm text-gray-500">Widget: {widget}</p>
                    </div>
                    <Link href={route('role-dashboard.index')} className="px-3 py-2 rounded bg-gray-200">Kembali Dashboard</Link>
                </div>

                <div className="bg-white rounded shadow p-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                    <input type="date" className="border rounded px-3 py-2" value={filters.date_from || ''} onChange={(e) => setFilter('date_from', e.target.value)} />
                    <input type="date" className="border rounded px-3 py-2" value={filters.date_to || ''} onChange={(e) => setFilter('date_to', e.target.value)} />
                    <input className="border rounded px-3 py-2" placeholder="Search" value={filters.search || ''} onChange={(e) => setFilter('search', e.target.value)} />
                </div>

                <div className="bg-white rounded shadow p-4">
                    <h2 className="font-semibold mb-3">Summary</h2>
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                        {Object.entries(summary || {}).map(([key, value]) => (
                            <div key={key} className="border rounded p-3">
                                <div className="text-xs text-gray-500 uppercase">{key.replaceAll('_', ' ')}</div>
                                <div className="text-lg font-semibold">{formatValue(value)}</div>
                            </div>
                        ))}
                    </div>
                </div>

                <div className="bg-white rounded shadow overflow-x-auto">
                    <table className="min-w-full text-sm">
                        <thead className="bg-gray-50">
                            <tr>{columns.map((column) => <th key={column} className="px-4 py-2 text-left uppercase text-xs">{column.replaceAll('_', ' ')}</th>)}</tr>
                        </thead>
                        <tbody className="divide-y">
                            {rows.length === 0 && (
                                <tr><td colSpan={columns.length} className="px-4 py-6 text-center text-gray-500">Tidak ada data</td></tr>
                            )}
                            {rows.map((row, index) => (
                                <tr key={index}>
                                    {columns.map((column) => <td key={column} className="px-4 py-2">{formatValue(row[column])}</td>)}
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
