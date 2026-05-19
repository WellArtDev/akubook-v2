import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { useMemo, useState } from 'react';

const STORAGE_KEY = 'akubook_offline_sync_events';

function readQueue() {
    try {
        return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
    } catch {
        return [];
    }
}

function writeQueue(events) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(events));
}

export default function Index({ auth }) {
    const form = useForm({
        employee_identifier: '',
        clock_type: 'check_in',
        clock_at: new Date().toISOString().slice(0, 16),
    });
    const [queue, setQueue] = useState(typeof window !== 'undefined' ? readQueue() : []);
    const [message, setMessage] = useState('');
    const [syncing, setSyncing] = useState(false);

    const online = typeof navigator !== 'undefined' ? navigator.onLine : true;

    const stats = useMemo(() => ({ count: queue.length }), [queue]);

    const addEvent = (e) => {
        e.preventDefault();
        const event = {
            client_event_id: `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
            entity: 'attendance',
            action: form.data.clock_type,
            payload: {
                employee_identifier: form.data.employee_identifier,
                clock_type: form.data.clock_type,
                clock_at: form.data.clock_at,
            },
        };
        const next = [...queue, event];
        setQueue(next);
        writeQueue(next);
        setMessage('Event masuk queue offline.');
    };

    const syncNow = async () => {
        if (!queue.length) {
            setMessage('Queue kosong.');
            return;
        }
        setSyncing(true);
        setMessage('Sync berjalan...');
        try {
            const response = await fetch(route('offline-sync.sync'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({ events: queue }),
            });
            const data = await response.json();
            const failed = new Set(data.results.filter((result) => result.status === 'failed').map((result) => result.client_event_id));
            const remaining = queue.filter((event) => failed.has(event.client_event_id));
            setQueue(remaining);
            writeQueue(remaining);
            setMessage(`Sync selesai. Processed: ${data.processed}. Failed: ${failed.size}.`);
        } catch {
            setMessage('Sync gagal, cek koneksi.');
        } finally {
            setSyncing(false);
        }
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Offline Data Sync</h2>}>
            <Head title="Offline Data Sync" />
            <div className="py-6 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div className="bg-white rounded shadow p-4 space-y-2">
                    <div>Network: <strong>{online ? 'Online' : 'Offline'}</strong></div>
                    <div>Queue: <strong>{stats.count}</strong></div>
                    {message && <div className="text-sm text-indigo-700">{message}</div>}
                </div>

                <form onSubmit={addEvent} className="bg-white rounded shadow p-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                    <input className="border rounded px-3 py-2" placeholder="Employee ID" value={form.data.employee_identifier} onChange={(e) => form.setData('employee_identifier', e.target.value)} required />
                    <select className="border rounded px-3 py-2" value={form.data.clock_type} onChange={(e) => form.setData('clock_type', e.target.value)}>
                        <option value="check_in">check_in</option>
                        <option value="check_out">check_out</option>
                    </select>
                    <input type="datetime-local" className="border rounded px-3 py-2" value={form.data.clock_at} onChange={(e) => form.setData('clock_at', e.target.value)} required />
                    <button className="px-4 py-2 bg-gray-800 text-white rounded">Tambah ke Queue</button>
                </form>

                <div className="bg-white rounded shadow p-4">
                    <button onClick={syncNow} disabled={syncing} className="px-4 py-2 bg-indigo-600 text-white rounded disabled:opacity-50">{syncing ? 'Sync...' : 'Sync Sekarang'}</button>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
