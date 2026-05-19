import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function Show({ auth, log }) {
    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Detail Log ZKTeco</h2>}>
            <Head title="Detail Log ZKTeco" />
            <div className="py-6 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div className="bg-white shadow rounded-lg p-6 space-y-2">
                    <div><b>Device:</b> {log.device?.device_code} - {log.device?.name}</div>
                    <div><b>Employee Identifier:</b> {log.employee_identifier}</div>
                    <div><b>Punch At:</b> {new Date(log.punch_at).toLocaleString()}</div>
                    <div><b>Punch Type:</b> {log.punch_type}</div>
                    <div><b>Mapped Employee:</b> {log.employee?.employee_id || '-'}</div>
                    <div><b>Mapped Attendance:</b> {log.attendance_record_id || '-'}</div>
                    <div><b>Mapped:</b> {log.is_mapped ? 'Yes' : 'No'}</div>
                    <div><b>Source Key:</b> {log.source_key}</div>
                    <div><b>Catatan:</b> {log.notes || '-'}</div>
                </div>
                <Link href={route('zkteco-attendance.index')} className="text-indigo-600">Kembali</Link>
            </div>
        </AuthenticatedLayout>
    );
}
