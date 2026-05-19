import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Show({ auth, leaveRequest }) {
    const approve = () => router.post(route('leave-requests.approve', leaveRequest.id));
    const reject = () => {
        const reason = prompt('Reason');
        if (!reason) return;
        router.post(route('leave-requests.reject', leaveRequest.id), { rejection_reason: reason });
    };
    const cancel = () => {
        const reason = prompt('Reason');
        if (!reason) return;
        router.post(route('leave-requests.cancel', leaveRequest.id), { cancellation_reason: reason });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Leave Request Detail" />
            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
                    <div className="bg-white shadow-sm sm:rounded-lg p-6">
                        <div className="flex justify-between items-center mb-6">
                            <h1 className="text-2xl font-semibold">Leave Request Detail</h1>
                            <div className="space-x-2">
                                {leaveRequest.status === 'pending' && <button onClick={approve} className="bg-green-600 text-white px-4 py-2 rounded">Approve</button>}
                                {leaveRequest.status === 'pending' && <button onClick={reject} className="bg-red-600 text-white px-4 py-2 rounded">Reject</button>}
                                {leaveRequest.status === 'pending' && <button onClick={cancel} className="bg-gray-700 text-white px-4 py-2 rounded">Cancel</button>}
                                <Link href={route('leave-requests.index')} className="bg-gray-200 px-4 py-2 rounded">Back</Link>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div><span className="font-medium">Employee:</span> {leaveRequest.employee?.employee_id} - {leaveRequest.employee?.full_name}</div>
                            <div><span className="font-medium">Type:</span> {leaveRequest.leave_type}</div>
                            <div><span className="font-medium">Start Date:</span> {leaveRequest.start_date}</div>
                            <div><span className="font-medium">End Date:</span> {leaveRequest.end_date}</div>
                            <div><span className="font-medium">Total Days:</span> {leaveRequest.total_days}</div>
                            <div><span className="font-medium">Status:</span> {leaveRequest.status}</div>
                            <div><span className="font-medium">Approved At:</span> {leaveRequest.approved_at || '-'}</div>
                            <div><span className="font-medium">Rejected At:</span> {leaveRequest.rejected_at || '-'}</div>
                            <div><span className="font-medium">Cancelled At:</span> {leaveRequest.cancelled_at || '-'}</div>
                        </div>

                        <div className="mt-4 text-sm">
                            <div className="font-medium mb-1">Reason</div>
                            <div className="border rounded p-3 bg-gray-50">{leaveRequest.reason || '-'}</div>
                        </div>

                        <div className="mt-4 text-sm">
                            <div className="font-medium mb-1">Rejection Reason</div>
                            <div className="border rounded p-3 bg-gray-50">{leaveRequest.rejection_reason || '-'}</div>
                        </div>

                        <div className="mt-4 text-sm">
                            <div className="font-medium mb-1">Cancellation Reason</div>
                            <div className="border rounded p-3 bg-gray-50">{leaveRequest.cancellation_reason || '-'}</div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
