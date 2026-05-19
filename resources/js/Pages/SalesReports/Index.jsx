import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

const formatCurrency = (value) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value || 0));

export default function Index({ auth, filters, generated_at, summary, by_customer, by_product, by_salesperson, pipeline, aging }) {
    const form = useForm({ date_from: filters.date_from, date_to: filters.date_to });

    const submit = (e) => {
        e.preventDefault();
        form.get(route('sales-reports.index'), { preserveState: true, preserveScroll: true });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Sales Reports" />
            <div className="mx-auto max-w-7xl p-6 space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">Sales Reports</h1>
                    <a href={route('sales-reports.export', filters)} className="rounded bg-slate-900 px-3 py-2 text-sm text-white">Export JSON</a>
                </div>
                <form onSubmit={submit} className="grid gap-3 rounded bg-white p-4 shadow sm:grid-cols-3">
                    <input type="date" value={form.data.date_from} onChange={(e) => form.setData('date_from', e.target.value)} className="rounded border" />
                    <input type="date" value={form.data.date_to} onChange={(e) => form.setData('date_to', e.target.value)} className="rounded border" />
                    <button className="rounded bg-blue-600 px-3 py-2 text-white" disabled={form.processing}>Apply</button>
                </form>
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Card title="Total Sales" value={formatCurrency(summary.total_sales)} />
                    <Card title="Transactions" value={summary.transaction_count} />
                    <Card title="Avg Order" value={formatCurrency(summary.average_order_value)} />
                    <Card title="Growth" value={`${Number(summary.growth_percent || 0).toFixed(2)}%`} />
                </div>
                <Section title="Sales by Customer" rows={by_customer} cols={['customer_name', 'total_sales', 'order_count']} />
                <Section title="Sales by Product" rows={by_product} cols={['product_name', 'quantity_sold', 'revenue']} />
                <Section title="Sales by Salesperson" rows={by_salesperson} cols={['salesperson_name', 'total_sales', 'order_count']} />
                <Section title="Pipeline" rows={[pipeline]} cols={['quotations_pending', 'quotations_approved', 'quotations_converted', 'sales_orders_pending', 'sales_orders_approved', 'invoices_paid', 'invoices_unpaid', 'invoices_overdue', 'conversion_rate']} />
                <Section title="Aging" rows={[aging]} cols={['0_30', '31_60', '61_90', 'over_90']} />
                <p className="text-xs text-gray-500">Generated at: {generated_at}</p>
            </div>
        </AuthenticatedLayout>
    );
}

function Card({ title, value }) {
    return <div className="rounded bg-white p-4 shadow"><p className="text-xs text-gray-500">{title}</p><p className="mt-1 text-xl font-semibold">{value}</p></div>;
}

function Section({ title, rows, cols }) {
    return (
        <div className="rounded bg-white p-4 shadow">
            <h2 className="mb-3 text-sm font-semibold">{title}</h2>
            <div className="overflow-x-auto">
                <table className="w-full text-sm">
                    <thead><tr>{cols.map((c) => <th key={c} className="border-b px-2 py-1 text-left">{c}</th>)}</tr></thead>
                    <tbody>{rows.length ? rows.map((row, i) => <tr key={i}>{cols.map((c) => <td key={c} className="border-b px-2 py-1">{String(row[c] ?? '-')}</td>)}</tr>) : <tr><td colSpan={cols.length} className="px-2 py-2 text-gray-500">No data</td></tr>}</tbody>
                </table>
            </div>
        </div>
    );
}
