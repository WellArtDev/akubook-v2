import React, { useMemo, useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

const OFFLINE_KEY = 'akubook_offline_attendance_events';

function readQueue() {
    try {
        const raw = localStorage.getItem(OFFLINE_KEY);
        const parsed = raw ? JSON.parse(raw) : [];
        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}

function writeQueue(queue) {
    localStorage.setItem(OFFLINE_KEY, JSON.stringify(queue));
}

export default function Create({ auth, employees }) {
    const { data, setData, post, processing, errors } = useForm({
        employee_id: employees[0]?.id ?? '',
        attendance_date: new Date().toISOString().slice(0, 10),
        check_in_at: '08:00',
        notes: '',
    });

    const selectedEmployee = useMemo(() => employees.find((employee) => String(employee.id) === String(data.employee_id)), [employees, data.employee_id]);
    const [offlineQueueCount, setOfflineQueueCount] = useState(typeof window !== 'undefined' ? readQueue().length : 0);
    const [syncing, setSyncing] = useState(false);
    const [syncMessage, setSyncMessage] = useState('');

    const submit = (e) => {
        e.preventDefault();

        if (!navigator.onLine && selectedEmployee) {
            const queue = readQueue();
            queue.push({
                employee_identifier: selectedEmployee.employee_id,
                clock_type: 'check_in',
                clock_at: `${data.attendance_date} ${data.check_in_at}:00`,
                source: 'offline_form',
            });
            writeQueue(queue);
            setOfflineQueueCount(queue.length);
            setSyncMessage('Offline: check-in masuk antrian lokal.');
            return;
        }

        post(route('attendance-records.store'));
    };

    const syncOffline = async () => {
        const queue = readQueue();
        if (!queue.length) {
            setSyncMessage('Tidak ada antrian offline.');
            return;
        }

        setSyncing(true);
        setSyncMessage('');

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const response = await fetch(route('offline-attendance-sync.sync'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    Accept: 'application/json',
                },
                body: JSON.stringify({ events: queue }),
            });

            if (!response.ok) {
                setSyncMessage('Sync gagal.');
                return;
            }

            const payload = await response.json();
            const failed = (payload.results || []).filter((row) => row.status === 'failed');
            if (failed.length) {
                setSyncMessage(`Sync sebagian. gagal: ${failed.length}`);
                setOfflineQueueCount(queue.length);
                return;
            }

            writeQueue([]);
            setOfflineQueueCount(0);
            setSyncMessage(`Sync sukses: ${payload.processed || 0} event.`);
        } catch {
            setSyncMessage('Sync error.');
        } finally {
            setSyncing(false);
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Check In Attendance" />
            <div className="py-12"><div className="max-w-4xl mx-auto sm:px-6 lg:px-8"><div className="bg-white shadow-sm sm:rounded-lg p-6">
                <div className="flex items-center justify-between mb-6">
                    <h1 className="text-2xl font-semibold">Check In Attendance</h1>
                    <div className="text-sm text-gray-600">Offline Queue: {offlineQueueCount}</div>
                </div>
                <div className="mb-4 flex items-center gap-2">
                    <button type="button" onClick={syncOffline} disabled={syncing} className="px-3 py-2 bg-emerald-600 text-white rounded">{syncing ? 'Syncing...' : 'Sync Offline Queue'}</button>
                    {syncMessage && <span className="text-sm text-gray-700">{syncMessage}</span>}
                </div>
                <form onSubmit={submit} className="space-y-4">
                    <div><label className="block text-sm mb-1">Employee</label><select value={data.employee_id} onChange={(e) => setData('employee_id', e.target.value)} className="w-full border rounded px-3 py-2">{employees.map((employee) => <option key={employee.id} value={employee.id}>{employee.employee_id} - {employee.full_name}</option>)}</select>{errors.employee_id && <p className="text-red-600 text-sm">{errors.employee_id}</p>}</div>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label className="block text-sm mb-1">Attendance Date</label><input type="date" value={data.attendance_date} onChange={(e) => setData('attendance_date', e.target.value)} className="w-full border rounded px-3 py-2" />{errors.attendance_date && <p className="text-red-600 text-sm">{errors.attendance_date}</p>}</div>
                        <div><label className="block text-sm mb-1">Check In Time</label><input type="time" value={data.check_in_at} onChange={(e) => setData('check_in_at', e.target.value)} className="w-full border rounded px-3 py-2" />{errors.check_in_at && <p className="text-red-600 text-sm">{errors.check_in_at}</p>}</div>
                    </div>
                    <div><label className="block text-sm mb-1">Notes</label><textarea value={data.notes} onChange={(e) => setData('notes', e.target.value)} className="w-full border rounded px-3 py-2" rows={3} />{errors.notes && <p className="text-red-600 text-sm">{errors.notes}</p>}</div>
                    <button disabled={processing} className="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
                </form>
            </div></div></div>
        </AuthenticatedLayout>
    );
}
