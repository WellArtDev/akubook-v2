import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export function ShiftForm({ auth, initialData = null, submitRoute, method = 'post' }) {
    const form = useForm({
        shift_code: initialData?.shift_code || '',
        name: initialData?.name || '',
        check_in_time: initialData?.check_in_time?.slice(0, 5) || '08:00',
        check_out_time: initialData?.check_out_time?.slice(0, 5) || '17:00',
        tolerance_minutes: initialData?.tolerance_minutes ?? 15,
        is_overnight: initialData?.is_overnight ?? false,
        is_active: initialData?.is_active ?? true,
        notes: initialData?.notes || '',
    });

    const submit = (e) => {
        e.preventDefault();
        if (method === 'put') form.put(submitRoute);
        else form.post(submitRoute);
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Shift Form</h2>}>
            <Head title="Shift Form" />
            <div className="py-6 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <form onSubmit={submit} className="bg-white rounded shadow p-6 space-y-4">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input className="border rounded px-3 py-2" placeholder="Shift Code" value={form.data.shift_code} onChange={(e) => form.setData('shift_code', e.target.value)} />
                        <input className="border rounded px-3 py-2" placeholder="Nama Shift" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} />
                        <input type="time" className="border rounded px-3 py-2" value={form.data.check_in_time} onChange={(e) => form.setData('check_in_time', e.target.value)} />
                        <input type="time" className="border rounded px-3 py-2" value={form.data.check_out_time} onChange={(e) => form.setData('check_out_time', e.target.value)} />
                        <input type="number" className="border rounded px-3 py-2" value={form.data.tolerance_minutes} onChange={(e) => form.setData('tolerance_minutes', Number(e.target.value))} />
                    </div>
                    <textarea className="border rounded px-3 py-2 w-full" placeholder="Catatan" value={form.data.notes} onChange={(e) => form.setData('notes', e.target.value)} />
                    <label className="inline-flex items-center gap-2"><input type="checkbox" checked={form.data.is_overnight} onChange={(e) => form.setData('is_overnight', e.target.checked)} /> Overnight</label>
                    <label className="inline-flex items-center gap-2"><input type="checkbox" checked={form.data.is_active} onChange={(e) => form.setData('is_active', e.target.checked)} /> Aktif</label>
                    <button className="px-4 py-2 bg-indigo-600 text-white rounded">Simpan</button>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}

export default function Create({ auth }) {
    return <ShiftForm auth={auth} submitRoute={route('work-shifts.store')} />;
}
