import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, quotations, filters, customers }) {
    const statusOptions = ['draft', 'sent', 'approved', 'rejected', 'expired', 'converted', 'revised'];
    const selectedStatuses = Array.isArray(filters.status) ? filters.status : (filters.status ? String(filters.status).split(',') : []);

    const updateFilter = (key, value) => router.get(route('sales-quotations.index'), { ...filters, [key]: value }, { preserveState: true, replace: true });

    const toggleStatus = (status) => {
        const next = selectedStatuses.includes(status) ? selectedStatuses.filter((s) => s !== status) : [...selectedStatuses, status];
        updateFilter('status', next);
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Sales Quotations</h2>}>
            <Head title="Sales Quotations" />
            <div className="py-12"><div className="mx-auto max-w-7xl sm:px-6 lg:px-8"><div className="bg-white shadow-sm sm:rounded-lg p-6">
                <div className="flex justify-between mb-4"><Link href={route('sales-quotations.create')} className="px-4 py-2 bg-blue-600 text-white rounded">Tambah Quotation</Link></div>
                <div className="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
                    <input value={filters.search || ''} onChange={(e) => updateFilter('search', e.target.value)} placeholder="Search number/customer" className="border rounded px-3 py-2" />
                    <select value={filters.customer_id || ''} onChange={(e) => updateFilter('customer_id', e.target.value)} className="border rounded px-3 py-2"><option value="">All customer</option>{customers.map((c) => <option key={c.id} value={c.id}>{c.code} - {c.name}</option>)}</select>
                    <input type="date" value={filters.date_from || ''} onChange={(e) => updateFilter('date_from', e.target.value)} className="border rounded px-3 py-2" />
                    <input type="date" value={filters.date_to || ''} onChange={(e) => updateFilter('date_to', e.target.value)} className="border rounded px-3 py-2" />
                </div>
                <div className="flex gap-2 flex-wrap mb-4">{statusOptions.map((s) => <button key={s} type="button" onClick={() => toggleStatus(s)} className={`px-3 py-1 rounded text-sm ${selectedStatuses.includes(s) ? 'bg-blue-600 text-white' : 'bg-gray-200'}`}>{s}</button>)}</div>
                <table className="min-w-full divide-y divide-gray-200"><thead><tr><th className="px-3 py-2 text-left">Number</th><th className="px-3 py-2 text-left">Date</th><th className="px-3 py-2 text-left">Customer</th><th className="px-3 py-2 text-left">Valid Until</th><th className="px-3 py-2 text-left">Status</th><th className="px-3 py-2 text-right">Grand Total</th></tr></thead><tbody>
                    {quotations.data.map((q) => <tr key={q.id} className="border-t"><td className="px-3 py-2"><Link className="text-blue-600" href={route('sales-quotations.show', q.id)}>{q.quotation_number}</Link></td><td className="px-3 py-2">{q.quotation_date}</td><td className="px-3 py-2">{q.customer?.name}</td><td className="px-3 py-2">{q.valid_until}</td><td className="px-3 py-2">{q.status}</td><td className="px-3 py-2 text-right">Rp {new Intl.NumberFormat('id-ID').format(q.grand_total)}</td></tr>)}
                </tbody></table>
            </div></div></div>
        </AuthenticatedLayout>
    );
}
