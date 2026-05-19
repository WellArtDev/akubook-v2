import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, shifts, filters }) {
    const apply = (key, value) => router.get(route('work-shifts.index'), { ...filters, [key]: value }, { preserveState: true, replace: true });
    const deactivate = (shift) => { if (confirm(`Nonaktifkan ${shift.shift_code}?`)) router.delete(route('work-shifts.destroy', shift.id)); };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Work Shifts</h2>}>
            <Head title="Work Shifts" />
            <div className="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div className="bg-white rounded shadow p-4 flex justify-between gap-3">
                    <div className="flex gap-3">
                        <input className="border rounded px-3 py-2" placeholder="Cari shift" defaultValue={filters.search || ''} onBlur={(e) => apply('search', e.target.value)} />
                        <select className="border rounded px-3 py-2" value={filters.is_active ?? ''} onChange={(e) => apply('is_active', e.target.value)}>
                            <option value="">Semua Status</option><option value="1">Aktif</option><option value="0">Nonaktif</option>
                        </select>
                    </div>
                    <Link href={route('work-shifts.create')} className="px-4 py-2 bg-indigo-600 text-white rounded">Tambah Shift</Link>
                </div>
                <div className="bg-white rounded shadow overflow-x-auto">
                    <table className="min-w-full text-sm"><thead className="bg-gray-50"><tr><th className="px-4 py-2 text-left">Code</th><th className="px-4 py-2 text-left">Nama</th><th className="px-4 py-2 text-left">Jam</th><th className="px-4 py-2 text-left">Status</th><th className="px-4 py-2 text-left">Aksi</th></tr></thead>
                        <tbody>{shifts.data.map((s) => <tr key={s.id} className="border-t"><td className="px-4 py-2">{s.shift_code}</td><td className="px-4 py-2">{s.name}</td><td className="px-4 py-2">{s.check_in_time} - {s.check_out_time}</td><td className="px-4 py-2">{s.is_active ? 'Aktif' : 'Nonaktif'}</td><td className="px-4 py-2 space-x-2"><Link href={route('work-shifts.show', s.id)} className="text-indigo-600">Detail</Link><Link href={route('work-shifts.edit', s.id)} className="text-amber-600">Edit</Link>{s.is_active && <button onClick={() => deactivate(s)} className="text-red-600">Nonaktifkan</button>}</td></tr>)}</tbody></table>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
