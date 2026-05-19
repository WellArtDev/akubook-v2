import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, logs, devices, filters }) {
    const applyFilter = (key, value) => {
        router.get(route('zkteco-attendance.index'), { ...filters, [key]: value }, { preserveState: true, replace: true });
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">ZKTeco Attendance Logs</h2>}>
            <Head title="ZKTeco Attendance Logs" />
            <div className="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div className="bg-white shadow rounded-lg p-4 flex flex-col md:flex-row gap-3 md:items-center md:justify-between">
                    <div className="flex flex-wrap gap-3">
                        <select className="border rounded px-3 py-2" value={filters.device_id ?? ''} onChange={(e) => applyFilter('device_id', e.target.value)}>
                            <option value="">Semua Device</option>
                            {devices.map((d) => <option key={d.id} value={d.id}>{d.device_code} - {d.name}</option>)}
                        </select>
                        <input className="border rounded px-3 py-2" placeholder="Employee ID" defaultValue={filters.employee_identifier || ''} onBlur={(e) => applyFilter('employee_identifier', e.target.value)} />
                    </div>
                    <div className="flex gap-2">
                        <Link href={route('zkteco-devices.index')} className="px-4 py-2 border rounded">Devices</Link>
                        <Link href={route('zkteco-attendance.create')} className="px-4 py-2 bg-indigo-600 text-white rounded">Import Log</Link>
                    </div>
                </div>
                <div className="bg-white shadow rounded-lg overflow-x-auto">
                    <table className="min-w-full text-sm">
                        <thead className="bg-gray-50 text-gray-600"><tr><th className="px-4 py-2 text-left">Waktu</th><th className="px-4 py-2 text-left">Device</th><th className="px-4 py-2 text-left">Emp ID</th><th className="px-4 py-2 text-left">Punch</th><th className="px-4 py-2 text-left">Mapped</th><th className="px-4 py-2 text-left">Aksi</th></tr></thead>
                        <tbody>
                            {logs.data.map((log) => (
                                <tr key={log.id} className="border-t">
                                    <td className="px-4 py-2">{new Date(log.punch_at).toLocaleString()}</td>
                                    <td className="px-4 py-2">{log.device?.device_code}</td>
                                    <td className="px-4 py-2">{log.employee_identifier}</td>
                                    <td className="px-4 py-2">{log.punch_type}</td>
                                    <td className="px-4 py-2">{log.is_mapped ? 'Yes' : 'No'}</td>
                                    <td className="px-4 py-2"><Link href={route('zkteco-attendance.show', log.id)} className="text-indigo-600">Detail</Link></td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
