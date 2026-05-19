import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Show({ auth, report, preview, filters }) {
    const updateFilter = (key, value) => {
        router.get(route('custom-reports.show', report.id), { ...filters, [key]: value }, { preserveState: true, replace: true });
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Custom Report Detail</h2>}>
            <Head title="Custom Report Detail" />
            <div className="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div className="flex gap-2">
                    <Link href={route('custom-reports.index')} className="px-3 py-2 bg-gray-200 rounded">Kembali</Link>
                    <Link href={route('custom-reports.edit', report.id)} className="px-3 py-2 bg-indigo-600 text-white rounded">Edit</Link>
                    <a href={route('report-exports.custom-report', { custom_report: report.id, ...filters })} className="px-3 py-2 bg-emerald-600 text-white rounded">Export CSV</a>
                </div>
                <div className="bg-white rounded shadow p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><div className="text-sm text-gray-500">Code</div><div>{report.code}</div></div>
                    <div><div className="text-sm text-gray-500">Name</div><div>{report.name}</div></div>
                    <div><div className="text-sm text-gray-500">Source</div><div>{report.source_key}</div></div>
                    <div><div className="text-sm text-gray-500">Status</div><div>{report.is_active ? 'active' : 'inactive'}</div></div>
                </div>
                <div className="flex gap-2">
                    <input type="date" className="border rounded px-3 py-2" value={filters.date_from || ''} onChange={(e) => updateFilter('date_from', e.target.value)} />
                    <input type="date" className="border rounded px-3 py-2" value={filters.date_to || ''} onChange={(e) => updateFilter('date_to', e.target.value)} />
                    <input className="border rounded px-3 py-2" placeholder="Search" value={filters.search || ''} onChange={(e) => updateFilter('search', e.target.value)} />
                </div>
                <div className="bg-white rounded shadow overflow-hidden">
                    <div className="px-4 py-2 text-sm text-gray-500">Generated: {preview.generated_at}</div>
                    <table className="min-w-full divide-y">
                        <thead className="bg-gray-50"><tr>{preview.columns.map((column) => <th key={column} className="px-4 py-2 text-left">{column}</th>)}</tr></thead>
                        <tbody className="divide-y">
                            {preview.rows.map((row, index) => (
                                <tr key={index}>
                                    {preview.columns.map((column) => <td key={column} className="px-4 py-2">{String(row[column] ?? '-')}</td>)}
                                </tr>
                            ))}
                            {preview.rows.length === 0 && <tr><td className="px-4 py-4 text-center text-gray-500" colSpan={preview.columns.length || 1}>No data</td></tr>}
                        </tbody>
                    </table>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
