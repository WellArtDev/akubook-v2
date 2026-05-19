import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Create({ auth, devices, punchTypes }) {
    const form = useForm({ zkteco_device_id: devices[0]?.id || '', employee_identifier: '', punch_at: '', punch_type: punchTypes[0] || 'check_in', notes: '' });

    const submit = (e) => {
        e.preventDefault();
        form.post(route('zkteco-attendance.store'));
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Import Log ZKTeco</h2>}>
            <Head title="Import Log ZKTeco" />
            <div className="py-6 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <form onSubmit={submit} className="bg-white shadow rounded-lg p-6 space-y-4">
                    <select className="border rounded px-3 py-2 w-full" value={form.data.zkteco_device_id} onChange={(e) => form.setData('zkteco_device_id', e.target.value)}>
                        <option value="">Pilih Device</option>
                        {devices.map((d) => <option key={d.id} value={d.id}>{d.device_code} - {d.name}</option>)}
                    </select>
                    <input className="border rounded px-3 py-2 w-full" placeholder="Employee Identifier (employee_id)" value={form.data.employee_identifier} onChange={(e) => form.setData('employee_identifier', e.target.value)} />
                    <input type="datetime-local" className="border rounded px-3 py-2 w-full" value={form.data.punch_at} onChange={(e) => form.setData('punch_at', e.target.value)} />
                    <select className="border rounded px-3 py-2 w-full" value={form.data.punch_type} onChange={(e) => form.setData('punch_type', e.target.value)}>
                        {punchTypes.map((t) => <option key={t} value={t}>{t}</option>)}
                    </select>
                    <textarea className="border rounded px-3 py-2 w-full" placeholder="Catatan" value={form.data.notes} onChange={(e) => form.setData('notes', e.target.value)} />
                    <button className="px-4 py-2 bg-indigo-600 text-white rounded" disabled={form.processing}>Import</button>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
