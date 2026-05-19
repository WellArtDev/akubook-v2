import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Index({ auth, filters, kpis, charts, generated_at }) {
    const form = useForm({
        date_from: filters?.date_from ?? '',
        date_to: filters?.date_to ?? '',
    });

    const submit = (e) => {
        e.preventDefault();
        form.get(route('purchase-dashboard.index'));
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Purchase Dashboard</h2>}>
            <Head title="Purchase Dashboard" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                    <form onSubmit={submit} className="grid grid-cols-1 gap-3 rounded-lg bg-white p-4 shadow sm:grid-cols-3">
                        <input type="date" value={form.data.date_from} onChange={(e) => form.setData('date_from', e.target.value)} className="rounded border-gray-300" />
                        <input type="date" value={form.data.date_to} onChange={(e) => form.setData('date_to', e.target.value)} className="rounded border-gray-300" />
                        <button type="submit" className="rounded bg-indigo-600 px-4 py-2 text-white">Filter</button>
                    </form>

                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <Card title="This Month Purchases" value={Number(kpis.this_month_purchases || 0).toLocaleString('id-ID')} />
                        <Card title="Pending PRs" value={kpis.pending_prs} />
                        <Card title="Pending PO Approvals" value={kpis.pending_po_approvals} />
                        <Card title="Overdue Invoices" value={kpis.overdue_invoices} />
                    </div>

                    <Section title="Purchase Trend (12 months)">
                        <SimpleTable headers={['Month', 'Total']} rows={(charts.purchase_trend || []).map((r) => [r.month, Number(r.total).toLocaleString('id-ID')])} />
                    </Section>

                    <Section title="Top 10 Suppliers">
                        <SimpleTable headers={['Supplier', 'Total']} rows={(charts.top_suppliers || []).map((r) => [r.name, Number(r.total).toLocaleString('id-ID')])} />
                    </Section>

                    <Section title="Top 10 Products">
                        <SimpleTable headers={['Code', 'Product', 'Qty', 'Amount']} rows={(charts.top_products || []).map((r) => [r.product_code, r.product_name, Number(r.total_qty).toLocaleString('id-ID'), Number(r.total_amount).toLocaleString('id-ID')])} />
                    </Section>

                    <div className="text-xs text-gray-500">Generated: {generated_at}</div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function Card({ title, value }) {
    return (
        <div className="rounded-lg bg-white p-4 shadow">
            <div className="text-sm text-gray-500">{title}</div>
            <div className="mt-1 text-2xl font-semibold text-gray-900">{value}</div>
        </div>
    );
}

function Section({ title, children }) {
    return (
        <div className="rounded-lg bg-white p-4 shadow">
            <h3 className="mb-3 text-sm font-semibold text-gray-700">{title}</h3>
            {children}
        </div>
    );
}

function SimpleTable({ headers, rows }) {
    return (
        <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200 text-sm">
                <thead className="bg-gray-50">
                    <tr>{headers.map((h) => <th key={h} className="px-3 py-2 text-left font-medium text-gray-600">{h}</th>)}</tr>
                </thead>
                <tbody className="divide-y divide-gray-100 bg-white">
                    {(rows || []).length === 0 ? (
                        <tr><td colSpan={headers.length} className="px-3 py-3 text-gray-500">No data</td></tr>
                    ) : (
                        rows.map((row, idx) => (
                            <tr key={idx}>{row.map((col, i) => <td key={i} className="px-3 py-2 text-gray-800">{col}</td>)}</tr>
                        ))
                    )}
                </tbody>
            </table>
        </div>
    );
}
