import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Show({ auth, record }) {
    const { data, setData, post, processing, errors } = useForm({
        check_out_at: '17:00',
        notes: record.notes || '',
    });

    const submitCheckout = (e) => {
        e.preventDefault();
        post(route('attendance-records.check-out', record.id));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Attendance Detail" />
            <div className="py-12"><div className="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
                <div className="bg-white shadow-sm sm:rounded-lg p-6">
                    <div className="flex justify-between items-center mb-6"><h1 className="text-2xl font-semibold">Attendance Detail</h1><Link href={route('attendance-records.index')} className="bg-gray-200 px-4 py-2 rounded">Back</Link></div>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div><span className="font-medium">Employee:</span> {record.employee?.employee_id} - {record.employee?.full_name}</div>
                        <div><span className="font-medium">Date:</span> {record.attendance_date}</div>
                        <div><span className="font-medium">Check In:</span> {record.check_in_at || '-'}</div>
                        <div><span className="font-medium">Check Out:</span> {record.check_out_at || '-'}</div>
                        <div><span className="font-medium">Hours:</span> {record.work_hours}</div>
                        <div><span className="font-medium">Status:</span> {record.status}</div>
                    </div>
                    <div className="mt-4 text-sm"><div className="font-medium mb-1">Notes</div><div className="border rounded p-3 bg-gray-50">{record.notes || '-'}</div></div>
                </div>
                {!record.check_out_at && (
                    <div className="bg-white shadow-sm sm:rounded-lg p-6">
                        <h2 className="text-lg font-semibold mb-4">Check Out</h2>
                        <form onSubmit={submitCheckout} className="space-y-4">
                            <div><label className="block text-sm mb-1">Check Out Time</label><input type="time" value={data.check_out_at} onChange={(e) => setData('check_out_at', e.target.value)} className="w-full border rounded px-3 py-2" />{errors.check_out_at && <p className="text-red-600 text-sm">{errors.check_out_at}</p>}</div>
                            <div><label className="block text-sm mb-1">Notes</label><textarea value={data.notes} onChange={(e) => setData('notes', e.target.value)} className="w-full border rounded px-3 py-2" rows={3} />{errors.notes && <p className="text-red-600 text-sm">{errors.notes}</p>}</div>
                            <button disabled={processing} className="bg-green-600 text-white px-4 py-2 rounded">Submit Check Out</button>
                        </form>
                    </div>
                )}
            </div></div>
        </AuthenticatedLayout>
    );
}
