import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function Show({ auth, shift, active_assignment_count }) {
    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Detail Shift</h2>}>
            <Head title="Detail Shift" />
            <div className="py-6 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div className="bg-white rounded shadow p-6 space-y-2">
                    <div><b>Code:</b> {shift.shift_code}</div>
                    <div><b>Nama:</b> {shift.name}</div>
                    <div><b>Jam:</b> {shift.check_in_time} - {shift.check_out_time}</div>
                    <div><b>Toleransi:</b> {shift.tolerance_minutes} menit</div>
                    <div><b>Overnight:</b> {shift.is_overnight ? 'Ya' : 'Tidak'}</div>
                    <div><b>Status:</b> {shift.is_active ? 'Aktif' : 'Nonaktif'}</div>
                    <div><b>Active Assignment:</b> {active_assignment_count}</div>
                </div>
                <Link href={route('work-shifts.index')} className="text-indigo-600">Kembali</Link>
            </div>
        </AuthenticatedLayout>
    );
}
