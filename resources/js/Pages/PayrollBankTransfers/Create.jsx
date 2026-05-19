import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Create({ auth, period, payrollRun }) {
    const form = useForm({ period: period || new Date().toISOString().slice(0, 7) });

    const submit = (e) => {
        e.preventDefault();
        form.post(route('payroll-bank-transfers.store'));
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Generate Bank Transfer File</h2>}>
            <Head title="Generate Bank Transfer File" />
            <div className="py-6 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <form onSubmit={submit} className="bg-white rounded shadow p-6 space-y-4">
                    <label className="block text-sm">Period</label>
                    <input type="month" className="border rounded px-3 py-2" value={form.data.period} onChange={(e) => form.setData('period', e.target.value)} />
                    {Object.values(form.errors).length > 0 && <div className="text-sm text-red-600">{Object.values(form.errors).join(' ')}</div>}
                    <button disabled={form.processing} className="px-4 py-2 bg-indigo-600 text-white rounded">Generate</button>
                </form>
                <div className="bg-white rounded shadow p-4 text-sm">
                    <div>Payroll Run: {payrollRun ? payrollRun.run_number : '-'}</div>
                    <div>Status: {payrollRun ? payrollRun.status : '-'}</div>
                    <div>Lines: {payrollRun ? payrollRun.lines.length : 0}</div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
