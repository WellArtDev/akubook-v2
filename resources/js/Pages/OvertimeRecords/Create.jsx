import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Create({ auth, employees }) {
    const form = useForm({
        employee_id: '',
        overtime_date: new Date().toISOString().slice(0, 10),
        start_at: '',
        end_at: '',
        reason: '',
    });

    const submit = (e) => {
        e.preventDefault();
        form.post(route('overtime-records.store'));
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Create Overtime</h2>}>
            <Head title="Create Overtime" />
            <div className="py-6 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <form onSubmit={submit} className="bg-white rounded shadow p-6 space-y-4">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <select className="border rounded px-3 py-2" value={form.data.employee_id} onChange={(e) => form.setData('employee_id', e.target.value)}>
                            <option value="">Pilih Employee</option>
                            {employees.map((employee) => <option key={employee.id} value={employee.id}>{employee.employee_id} - {employee.full_name}</option>)}
                        </select>
                        <input type="date" className="border rounded px-3 py-2" value={form.data.overtime_date} onChange={(e) => form.setData('overtime_date', e.target.value)} />
                        <input type="datetime-local" className="border rounded px-3 py-2" value={form.data.start_at} onChange={(e) => form.setData('start_at', e.target.value)} />
                        <input type="datetime-local" className="border rounded px-3 py-2" value={form.data.end_at} onChange={(e) => form.setData('end_at', e.target.value)} />
                    </div>
                    <textarea className="border rounded px-3 py-2 w-full" placeholder="Reason" value={form.data.reason} onChange={(e) => form.setData('reason', e.target.value)} />
                    {Object.values(form.errors).length > 0 && <div className="text-sm text-red-600">{Object.values(form.errors).join(' ')}</div>}
                    <button disabled={form.processing} className="px-4 py-2 bg-indigo-600 text-white rounded">Simpan</button>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
