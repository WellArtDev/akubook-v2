import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

const money = (value) => new Intl.NumberFormat('id-ID').format(Number(value || 0));

export default function Index({ auth, filters, generated_at, summary, sales, purchase_orders, goods_receipts, delivery_orders, stock_movements }) {
    const update = (key, value) => {
        router.get(route('operational-reports.index'), { ...filters, [key]: value }, { preserveState: true, replace: true });
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Operational Reports</h2>}>
            <Head title="Operational Reports" />
            <div className="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div className="bg-white rounded shadow p-4 flex flex-wrap gap-3 items-end">
                    <div>
                        <label className="text-xs text-gray-500">Date From</label>
                        <input type="date" value={filters.date_from || ''} onChange={(e) => update('date_from', e.target.value)} className="block border rounded px-3 py-2" />
                    </div>
                    <div>
                        <label className="text-xs text-gray-500">Date To</label>
                        <input type="date" value={filters.date_to || ''} onChange={(e) => update('date_to', e.target.value)} className="block border rounded px-3 py-2" />
                    </div>
                    <div className="text-xs text-gray-500">Generated: {generated_at}</div>
                </div>

                <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <Card title="Sales Invoices" value={summary.sales_invoice_count} />
                    <Card title="Sales Total" value={money(summary.sales_invoice_total)} />
                    <Card title="Purchase Orders" value={summary.purchase_order_count} />
                    <Card title="PO Total" value={money(summary.purchase_order_total)} />
                    <Card title="Goods Receipts" value={summary.goods_receipt_count} />
                    <Card title="Delivery Orders" value={summary.delivery_order_count} />
                    <Card title="Stock Movements" value={summary.stock_movement_count} />
                    <Card title="Stock Net Qty" value={summary.stock_net_quantity} />
                </div>

                <Section title="Sales Invoices by Status" rows={sales} columns={[['status', 'Status'], ['count', 'Count'], ['total', 'Total']]} moneyColumns={['total']} />
                <Section title="Purchase Orders by Status" rows={purchase_orders} columns={[['status', 'Status'], ['count', 'Count'], ['total', 'Total']]} moneyColumns={['total']} />
                <Section title="Goods Receipts by Status" rows={goods_receipts} columns={[['status', 'Status'], ['count', 'Count']]} />
                <Section title="Delivery Orders by Status" rows={delivery_orders} columns={[['status', 'Status'], ['count', 'Count']]} />
                <Section title="Stock Movements by Type" rows={stock_movements} columns={[['movement_type', 'Type'], ['count', 'Count'], ['quantity_in', 'Qty In'], ['quantity_out', 'Qty Out'], ['net_quantity', 'Net']]} />
            </div>
        </AuthenticatedLayout>
    );
}

function Card({ title, value }) {
    return <div className="bg-white rounded shadow p-4"><div className="text-xs text-gray-500">{title}</div><div className="text-lg font-semibold">{value}</div></div>;
}

function Section({ title, rows, columns, moneyColumns = [] }) {
    return (
        <div className="bg-white rounded shadow overflow-hidden">
            <div className="px-4 py-3 border-b font-semibold">{title}</div>
            <table className="min-w-full text-sm">
                <thead className="bg-gray-50"><tr>{columns.map(([key, label]) => <th key={key} className="px-4 py-2 text-left">{label}</th>)}</tr></thead>
                <tbody>
                    {rows.map((row, idx) => (
                        <tr key={idx} className="border-t">
                            {columns.map(([key]) => <td key={key} className="px-4 py-2">{moneyColumns.includes(key) ? money(row[key]) : row[key]}</td>)}
                        </tr>
                    ))}
                    {rows.length === 0 && <tr><td className="px-4 py-4 text-gray-500" colSpan={columns.length}>Tidak ada data</td></tr>}
                </tbody>
            </table>
        </div>
    );
}
