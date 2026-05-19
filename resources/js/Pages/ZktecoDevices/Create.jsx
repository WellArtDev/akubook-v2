import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Create({ auth }) {
    const form = useForm({ device_code: '', name: '', ip_address: '', port: 4370, is_active: true, notes: '' });

    const submit = (e) => {
        e.preventDefault();
        form.post(route('zkteco-devices.store'));
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Tambah Device ZKTeco</h2>}>
            <Head title="Tambah Device ZKTeco" />
            <div className="py-6 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <form onSubmit={submit} className="bg-white shadow rounded-lg p-6 space-y-4">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input className="border rounded px-3 py-2" placeholder="Device Code" value={form.data.device_code} onChange={(e) => form.setData('device_code', e.target.value)} />
                        <input className="border rounded px-3 py-2" placeholder="Nama Device" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} />
                        <input className="border rounded px-3 py-2" placeholder="IP Address" value={form.data.ip_address} onChange={(e) => form.setData('ip_address', e.target.value)} />
                        <input type="number" className="border rounded px-3 py-2" placeholder="Port" value={form.data.port} onChange={(e) => form.setData('port', Number(e.target.value))} />
                    </div>
                    <textarea className="border rounded px-3 py-2 w-full" placeholder="Catatan" value={form.data.notes} onChange={(e) => form.setData('notes', e.target.value)} />
                    <label className="inline-flex items-center gap-2"><input type="checkbox" checked={form.data.is_active} onChange={(e) => form.setData('is_active', e.target.checked)} /> Aktif</label>
                    <button className="px-4 py-2 bg-indigo-600 text-white rounded" disabled={form.processing}>Simpan</button>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
