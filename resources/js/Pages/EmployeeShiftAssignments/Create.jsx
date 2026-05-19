import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export function ShiftAssignmentForm({ auth, employees, shifts, statuses, assignment = null, method = 'post', submitRoute }) {
    const form = useForm({
        employee_id: assignment?.employee_id || '',
        work_shift_id: assignment?.work_shift_id || '',
        effective_date: assignment?.effective_date?.slice(0, 10) || new Date().toISOString().slice(0, 10),
        status: assignment?.status || 'active',
        notes: assignment?.notes || '',
    });

    const submit = (e) => {
        e.preventDefault();
        if (method === 'put') form.put(submitRoute);
        else form.post(submitRoute);
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Shift Assignment Form</h2>}>
            <Head title="Shift Assignment Form" />
            <div className="py-6 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <form onSubmit={submit} className="bg-white rounded shadow p-6 space-y-4">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <select className="border rounded px-3 py-2" value={form.data.employee_id} onChange={(e) => form.setData('employee_id', e.target.value)}>
                            <option value="">Pilih Employee</option>
                            {employees.map((employee) => <option key={employee.id} value={employee.id}>{employee.employee_id} - {employee.full_name}</option>)}
                        </select>
                        <select className="border rounded px-3 py-2" value={form.data.work_shift_id} onChange={(e) => form.setData('work_shift_id', e.target.value)}>
                            <option value="">Pilih Shift</option>
                            {shifts.map((shift) => <option key={shift.id} value={shift.id}>{shift.shift_code} - {shift.name}</option>)}
                        </select>
                        <input type="date" className="border rounded px-3 py-2" value={form.data.effective_date} onChange={(e) => form.setData('effective_date', e.target.value)} />
                        <select className="border rounded px-3 py-2" value={form.data.status} onChange={(e) => form.setData('status', e.target.value)}>
                            {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
                        </select>
                    </div>
                    <textarea className="border rounded px-3 py-2 w-full" placeholder="Catatan" value={form.data.notes} onChange={(e) => form.setData('notes', e.target.value)} />
                    {Object.values(form.errors).length > 0 && <div className="text-sm text-red-600">{Object.values(form.errors).join(' ')}</div>}
                    <button disabled={form.processing} className="px-4 py-2 bg-indigo-600 text-white rounded">Simpan</button>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}

export default function Create({ auth, employees, shifts, statuses }) {
    return <ShiftAssignmentForm auth={auth} employees={employees} shifts={shifts} statuses={statuses} submitRoute={route('employee-shift-assignments.store')} />;
}
