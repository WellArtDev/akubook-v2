import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, devices, filters }) {
    const applyFilter = (key, value) => {
        router.get(route('zkteco-devices.index'), { ...filters, [key]: value }, { preserveState: true, replace: true });
    };

    const deactivate = (device) => {
        if (!confirm(`Nonaktifkan device ${device.device_code}?`)) return;
        router.delete(route('zkteco-devices.destroy', device.id));
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">ZKTeco Devices</h2>}>
            <Head title="ZKTeco Devices" />
            <div className="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div className="bg-white shadow rounded-lg p-4 flex flex-col md:flex-row gap-3 md:items-center md:justify-between">
                    <div className="flex gap-3">
                        <input className="border rounded px-3 py-2" placeholder="Cari code/nama/ip" defaultValue={filters.search || ''} onBlur={(e) => applyFilter('search', e.target.value)} />
                        <select className="border rounded px-3 py-2" value={filters.is_active ?? ''} onChange={(e) => applyFilter('is_active', e.target.value)}>
                            <option value="">Semua Status</option>
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                    <Link href={route('zkteco-devices.create')} className="px-4 py-2 bg-indigo-600 text-white rounded">Tambah Device</Link>
                </div>
                <div className="bg-white shadow rounded-lg overflow-x-auto">
                    <table className="min-w-full text-sm">
                        <thead className="bg-gray-50 text-gray-600">
                            <tr>
                                <th className="px-4 py-2 text-left">Code</th><th className="px-4 py-2 text-left">Nama</th><th className="px-4 py-2 text-left">IP:Port</th><th className="px-4 py-2 text-left">Status</th><th className="px-4 py-2 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {devices.data.map((device) => (
                                <tr key={device.id} className="border-t">
                                    <td className="px-4 py-2">{device.device_code}</td>
                                    <td className="px-4 py-2">{device.name}</td>
                                    <td className="px-4 py-2">{device.ip_address}:{device.port}</td>
                                    <td className="px-4 py-2">{device.is_active ? 'Aktif' : 'Nonaktif'}</td>
                                    <td className="px-4 py-2 space-x-2">
                                        <Link href={route('zkteco-devices.show', device.id)} className="text-indigo-600">Detail</Link>
                                        {device.is_active && <button onClick={() => deactivate(device)} className="text-red-600">Nonaktifkan</button>}
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
