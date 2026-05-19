import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Index({ auth, filters, generated_at, summary, purchase_summary, by_supplier, by_product, by_department, pipeline, ap_aging, payments }) {
    const form = useForm({
        date_from: filters.date_from,
        date_to: filters.date_to,
    });

    const submit = (e) => {
        e.preventDefault();
        form.get(route('purchase-reports.index'));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Purchase Reports" />
            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                    <h1 className="text-2xl font-semibold text-gray-900">Purchase Reports</h1>
                    <form onSubmit={submit} className="grid grid-cols-1 gap-3 rounded-lg bg-white p-4 shadow sm:grid-cols-4">
                        <input type="date" value={form.data.date_from} onChange={(e) => form.setData('date_from', e.target.value)} className="rounded border-gray-300" />
                        <input type="date" value={form.data.date_to} onChange={(e) => form.setData('date_to', e.target.value)} className="rounded border-gray-300" />
                        <button type="submit" className="rounded bg-indigo-600 px-4 py-2 text-white">Apply</button>
                        <a href={route('purchase-reports.export', form.data)} className="rounded bg-gray-800 px-4 py-2 text-center text-white">Export JSON</a>
                    </form>

                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        <Card title="PO Count" value={summary.purchase_order_count} />
                        <Card title="PO Total" value={formatCurrency(summary.purchase_order_total)} />
                        <Card title="Suppliers" value={summary.supplier_count} />
                        <Card title="AP Outstanding" value={formatCurrency(summary.ap_outstanding_total)} />
                        <Card title="Supplier Payments" value={formatCurrency(summary.payment_total)} />
                    </div>

                    <Section title="Purchase Summary" rows={purchase_summary.map((r) => [r.status, r.count, formatCurrency(r.total)])} />
                    <Section title="Purchase by Supplier" rows={by_supplier.map((r) => [r.supplier_name, r.order_count, formatCurrency(r.total)])} />
                    <Section title="Purchase by Product" rows={by_product.map((r) => [r.product_code || '-', r.product_name, r.quantity, formatCurrency(r.total)])} />
                    <Section title="Purchase by Department" rows={by_department.map((r) => [r.department_name, r.order_count, formatCurrency(r.total)])} />
                    <Section title="Purchase Pipeline" rows={pipeline.map((r) => [r.status, r.count, formatCurrency(r.total)])} />
                    <Section title="AP Aging" rows={ap_aging.map((r) => [r.bucket, r.count, formatCurrency(r.total)])} />
                    <Section title="Payment Summary" rows={payments.map((r) => [r.status, r.count, formatCurrency(r.total)])} />

                    <p className="text-xs text-gray-500">Generated: {generated_at}</p>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function Card({ title, value }) {
    return (
        <div className="rounded-lg bg-white p-4 shadow">
            <p className="text-xs text-gray-500">{title}</p>
            <p className="text-lg font-semibold text-gray-900">{value}</p>
        </div>
    );
}

function Section({ title, rows }) {
    return (
        <div className="rounded-lg bg-white p-4 shadow">
            <h2 className="mb-3 text-sm font-semibold text-gray-900">{title}</h2>
            <div className="overflow-x-auto">
                <table className="min-w-full text-sm">
                    <tbody>
                        {rows.length ? rows.map((row, idx) => (
                            <tr key={idx} className="border-t">
                                {row.map((cell, cidx) => <td key={cidx} className="px-2 py-1">{cell}</td>)}
                            </tr>
                        )) : (
                            <tr><td className="px-2 py-1 text-gray-500">No data</td></tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0);
}
