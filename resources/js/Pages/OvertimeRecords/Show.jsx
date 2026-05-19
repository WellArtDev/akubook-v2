import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Show({ auth, record }) {
    const reject = () => {
        const rejection_reason = prompt('Alasan tolak overtime');
        if (!rejection_reason) return;
        router.post(route('overtime-records.reject', record.id), { rejection_reason });
    };

    const cancel = () => {
        const cancellation_reason = prompt('Alasan batal overtime');
        if (!cancellation_reason) return;
        router.post(route('overtime-records.cancel', record.id), { cancellation_reason });
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Overtime Detail</h2>}>
            <Head title="Overtime Detail" />
            <div className="py-6 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div className="flex gap-2">
                    <Link href={route('overtime-records.index')} className="px-3 py-2 bg-gray-200 rounded">Kembali</Link>
                    {record.status === 'pending' && (
                        <>
                            <button onClick={() => router.post(route('overtime-records.approve', record.id))} className="px-3 py-2 bg-green-600 text-white rounded">Approve</button>
                            <button onClick={reject} className="px-3 py-2 bg-red-600 text-white rounded">Reject</button>
                            <button onClick={cancel} className="px-3 py-2 bg-yellow-600 text-white rounded">Cancel</button>
                        </>
                    )}
                </div>
                <div className="bg-white rounded shadow p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><div className="text-sm text-gray-500">Employee</div><div>{record.employee?.employee_id} - {record.employee?.full_name}</div></div>
                    <div><div className="text-sm text-gray-500">Date</div><div>{record.overtime_date}</div></div>
                    <div><div className="text-sm text-gray-500">Start</div><div>{record.start_at}</div></div>
                    <div><div className="text-sm text-gray-500">End</div><div>{record.end_at}</div></div>
                    <div><div className="text-sm text-gray-500">Hours</div><div>{record.hours}</div></div>
                    <div><div className="text-sm text-gray-500">Status</div><div>{record.status}</div></div>
                    <div className="md:col-span-2"><div className="text-sm text-gray-500">Reason</div><div>{record.reason || '-'}</div></div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
