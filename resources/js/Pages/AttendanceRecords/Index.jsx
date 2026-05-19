import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, attendanceRecords, filters, statuses }) {
    const submit = (e) => {
        e.preventDefault();
        const form = new FormData(e.target);
        router.get(route('attendance-records.index'), Object.fromEntries(form.entries()));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Attendance Records" />
            <div className="py-12"><div className="max-w-7xl mx-auto sm:px-6 lg:px-8"><div className="bg-white shadow-sm sm:rounded-lg p-6">
                <div className="flex justify-between items-center mb-6"><h1 className="text-2xl font-semibold">Attendance Records</h1><Link href={route('attendance-records.create')} className="bg-blue-600 text-white px-4 py-2 rounded">Check In</Link></div>
                <form onSubmit={submit} className="grid grid-cols-1 md:grid-cols-5 gap-3 mb-6">
                    <input name="search" defaultValue={filters.search || ''} placeholder="Search employee" className="border rounded px-3 py-2" />
                    <select name="status" defaultValue={filters.status || ''} className="border rounded px-3 py-2"><option value="">All Status</option>{statuses.map((status) => <option key={status} value={status}>{status}</option>)}</select>
                    <input type="date" name="date_from" defaultValue={filters.date_from || ''} className="border rounded px-3 py-2" />
                    <input type="date" name="date_to" defaultValue={filters.date_to || ''} className="border rounded px-3 py-2" />
                    <button className="bg-gray-700 text-white px-4 py-2 rounded">Filter</button>
                </form>
                <table className="min-w-full text-sm"><thead><tr className="border-b"><th className="text-left py-2">Employee</th><th className="text-left py-2">Date</th><th className="text-left py-2">Check In</th><th className="text-left py-2">Check Out</th><th className="text-left py-2">Hours</th><th className="text-left py-2">Status</th><th className="text-right py-2">Action</th></tr></thead>
                    <tbody>{attendanceRecords.data.map((row) => <tr key={row.id} className="border-b"><td className="py-2">{row.employee?.employee_id} - {row.employee?.full_name}</td><td className="py-2">{row.attendance_date}</td><td className="py-2">{row.check_in_at || '-'}</td><td className="py-2">{row.check_out_at || '-'}</td><td className="py-2">{row.work_hours}</td><td className="py-2">{row.status}</td><td className="py-2 text-right"><Link href={route('attendance-records.show', row.id)} className="text-blue-600">View</Link></td></tr>)}</tbody>
                </table>
            </div></div></div>
        </AuthenticatedLayout>
    );
}
