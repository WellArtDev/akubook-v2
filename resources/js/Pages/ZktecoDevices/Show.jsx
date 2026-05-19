import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function Show({ auth, device, log_count }) {
    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Detail Device ZKTeco</h2>}>
            <Head title="Detail Device ZKTeco" />
            <div className="py-6 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div className="bg-white shadow rounded-lg p-6 space-y-2">
                    <div><b>Code:</b> {device.device_code}</div>
                    <div><b>Nama:</b> {device.name}</div>
                    <div><b>IP:Port:</b> {device.ip_address}:{device.port}</div>
                    <div><b>Status:</b> {device.is_active ? 'Aktif' : 'Nonaktif'}</div>
                    <div><b>Jumlah Log:</b> {log_count}</div>
                    <div><b>Catatan:</b> {device.notes || '-'}</div>
                </div>
                <Link href={route('zkteco-devices.index')} className="text-indigo-600">Kembali</Link>
            </div>
        </AuthenticatedLayout>
    );
}
