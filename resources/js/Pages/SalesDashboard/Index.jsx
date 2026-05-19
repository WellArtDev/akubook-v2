import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Index({ auth, filters, kpis, charts, recent_activity, alerts, generated_at }) {
    const form = useForm({
        date_from: filters?.date_from ?? '',
        date_to: filters?.date_to ?? '',
    });

    const submit = (e) => {
        e.preventDefault();
        form.get(route('sales-dashboard.index'));
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Sales Dashboard</h2>}>
            <Head title="Sales Dashboard" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                    <form onSubmit={submit} className="grid grid-cols-1 gap-3 rounded-lg bg-white p-4 shadow sm:grid-cols-3">
                        <input type="date" value={form.data.date_from} onChange={(e) => form.setData('date_from', e.target.value)} className="rounded border-gray-300" />
                        <input type="date" value={form.data.date_to} onChange={(e) => form.setData('date_to', e.target.value)} className="rounded border-gray-300" />
                        <button type="submit" className="rounded bg-indigo-600 px-4 py-2 text-white">Filter</button>
                    </form>

                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <Card title="Sales Today" value={formatCurrency(kpis.today_sales)} />
                        <Card title="Sales This Month" value={formatCurrency(kpis.this_month_sales)} />
                        <Card title="Sales This Year" value={formatCurrency(kpis.this_year_sales)} />
                        <Card title="Pending Quotations" value={kpis.pending_quotations} />
                        <Card title="Pending Approvals" value={kpis.pending_approvals} />
                        <Card title="Overdue Invoices" value={kpis.overdue_invoices} />
                    </div>

                    <Section title="Sales Trend (12 months)">
                        <SimpleTable
                            headers={['Month', 'This Year', 'Previous Year']}
                            rows={(charts.sales_trend || []).map((r) => [r.month, formatCurrency(r.total), formatCurrency(r.previous_year_total)])}
                        />
                    </Section>

                    <Section title="Top 10 Customers">
                        <SimpleTable
                            headers={['Customer', 'Orders', 'Total']}
                            rows={(charts.top_customers || []).map((r) => [r.name, r.order_count, formatCurrency(r.total)])}
                        />
                    </Section>

                    <Section title="Top 10 Products">
                        <SimpleTable
                            headers={['Product', 'Qty', 'Revenue']}
                            rows={(charts.top_products || []).map((r) => [r.product_name, Number(r.quantity).toLocaleString('id-ID'), formatCurrency(r.revenue)])}
                        />
                    </Section>

                    <Section title="Top 10 Salespeople">
                        <SimpleTable
                            headers={['Salesperson', 'Orders', 'Total']}
                            rows={(charts.top_salespeople || []).map((r) => [r.name, r.order_count, formatCurrency(r.total)])}
                        />
                    </Section>

                    <Section title="Recent Activity">
                        <SimpleTable
                            headers={['Type', 'Number', 'Date', 'Party', 'Status', 'Amount']}
                            rows={[
                                ...(recent_activity.quotations || []).map((q) => ['Quotation', q.quotation_number, q.quotation_date, q.customer?.name, q.status, formatCurrency(q.grand_total)]),
                                ...(recent_activity.orders || []).map((o) => ['Order', o.so_number, o.so_date, o.customer?.name, o.status, formatCurrency(o.grand_total)]),
                                ...(recent_activity.invoices || []).map((i) => ['Invoice', i.invoice_number, i.invoice_date, i.customer?.name, i.status, formatCurrency(i.grand_total)]),
                                ...(recent_activity.payments || []).map((p) => ['Payment', p.payment_number, p.payment_date, p.customer?.name, p.status, formatCurrency(p.total_amount)]),
                            ]}
                        />
                    </Section>

                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Card title="Alert: Pending Approvals" value={alerts.pending_approvals} />
                        <Card title="Alert: Overdue Invoices" value={alerts.overdue_invoices} />
                    </div>

                    <div className="text-xs text-gray-500">Generated: {generated_at}</div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function formatCurrency(value) {
    return Number(value || 0).toLocaleString('id-ID');
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
