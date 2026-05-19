import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, records, statuses, filters }) {
    const updateFilter = (key, value) => {
        router.get(route('overtime-records.index'), { ...filters, [key]: value }, { preserveState: true, replace: true });
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Overtime Records</h2>}>
            <Head title="Overtime Records" />
            <div className="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div className="flex justify-between items-center">
                    <div className="flex gap-2">
                        <input className="border rounded px-3 py-2" placeholder="Search employee" value={filters.search || ''} onChange={(e) => updateFilter('search', e.target.value)} />
                        <select className="border rounded px-3 py-2" value={filters.status || ''} onChange={(e) => updateFilter('status', e.target.value)}>
                            <option value="">All Status</option>
                            {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
                        </select>
                        <input type="date" className="border rounded px-3 py-2" value={filters.date_from || ''} onChange={(e) => updateFilter('date_from', e.target.value)} />
                        <input type="date" className="border rounded px-3 py-2" value={filters.date_to || ''} onChange={(e) => updateFilter('date_to', e.target.value)} />
                    </div>
                    <Link href={route('overtime-records.create')} className="px-4 py-2 bg-indigo-600 text-white rounded">Tambah</Link>
                </div>
                <div className="bg-white shadow rounded overflow-hidden">
                    <table className="min-w-full divide-y">
                        <thead className="bg-gray-50"><tr><th className="px-4 py-2 text-left">Employee</th><th className="px-4 py-2 text-left">Date</th><th className="px-4 py-2 text-left">Start</th><th className="px-4 py-2 text-left">End</th><th className="px-4 py-2 text-left">Hours</th><th className="px-4 py-2 text-left">Status</th><th className="px-4 py-2 text-left">Aksi</th></tr></thead>
                        <tbody className="divide-y">
                            {records.data.map((record) => (
                                <tr key={record.id}>
                                    <td className="px-4 py-2">{record.employee?.employee_id} - {record.employee?.full_name}</td>
                                    <td className="px-4 py-2">{record.overtime_date}</td>
                                    <td className="px-4 py-2">{record.start_at}</td>
                                    <td className="px-4 py-2">{record.end_at}</td>
                                    <td className="px-4 py-2">{record.hours}</td>
                                    <td className="px-4 py-2">{record.status}</td>
                                    <td className="px-4 py-2"><Link className="text-indigo-600" href={route('overtime-records.show', record.id)}>Detail</Link></td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
