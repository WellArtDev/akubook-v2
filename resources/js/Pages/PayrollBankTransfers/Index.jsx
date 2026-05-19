import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, transfers, filters }) {
    const updateFilter = (key, value) => {
        router.get(route('payroll-bank-transfers.index'), { ...filters, [key]: value }, { preserveState: true, replace: true });
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Payroll Bank Transfers</h2>}>
            <Head title="Payroll Bank Transfers" />
            <div className="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div className="flex justify-between items-center">
                    <div className="flex gap-2">
                        <input type="month" className="border rounded px-3 py-2" value={filters.period || ''} onChange={(e) => updateFilter('period', e.target.value)} />
                        <input className="border rounded px-3 py-2" placeholder="Search transfer number" value={filters.search || ''} onChange={(e) => updateFilter('search', e.target.value)} />
                    </div>
                    <Link href={route('payroll-bank-transfers.create')} className="px-4 py-2 bg-indigo-600 text-white rounded">Generate</Link>
                </div>
                <div className="bg-white shadow rounded overflow-hidden">
                    <table className="min-w-full divide-y">
                        <thead className="bg-gray-50"><tr><th className="px-4 py-2 text-left">Number</th><th className="px-4 py-2 text-left">Period</th><th className="px-4 py-2 text-left">Rows</th><th className="px-4 py-2 text-left">Success</th><th className="px-4 py-2 text-left">Failed</th><th className="px-4 py-2 text-left">Total Amount</th><th className="px-4 py-2 text-left">Aksi</th></tr></thead>
                        <tbody className="divide-y">
                            {transfers.data.map((transfer) => (
                                <tr key={transfer.id}>
                                    <td className="px-4 py-2">{transfer.transfer_number}</td>
                                    <td className="px-4 py-2">{transfer.period}</td>
                                    <td className="px-4 py-2">{transfer.row_count}</td>
                                    <td className="px-4 py-2">{transfer.success_count}</td>
                                    <td className="px-4 py-2">{transfer.failed_count}</td>
                                    <td className="px-4 py-2">{Number(transfer.total_amount || 0).toLocaleString('id-ID')}</td>
                                    <td className="px-4 py-2"><Link className="text-indigo-600" href={route('payroll-bank-transfers.show', transfer.id)}>Detail</Link></td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
