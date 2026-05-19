import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function Show({ auth, transfer }) {
    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Payroll Bank Transfer Detail</h2>}>
            <Head title="Payroll Bank Transfer Detail" />
            <div className="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div className="flex gap-2">
                    <Link href={route('payroll-bank-transfers.index')} className="px-3 py-2 bg-gray-200 rounded">Kembali</Link>
                    <a href={route('payroll-bank-transfers.download', transfer.id)} className="px-3 py-2 bg-indigo-600 text-white rounded">Download CSV</a>
                </div>
                <div className="bg-white rounded shadow p-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div><div className="text-gray-500">Number</div><div>{transfer.transfer_number}</div></div>
                    <div><div className="text-gray-500">Period</div><div>{transfer.period}</div></div>
                    <div><div className="text-gray-500">Rows</div><div>{transfer.row_count}</div></div>
                    <div><div className="text-gray-500">Total</div><div>{Number(transfer.total_amount || 0).toLocaleString('id-ID')}</div></div>
                </div>
                <div className="bg-white shadow rounded overflow-hidden">
                    <table className="min-w-full divide-y">
                        <thead className="bg-gray-50"><tr><th className="px-4 py-2 text-left">Employee</th><th className="px-4 py-2 text-left">Bank</th><th className="px-4 py-2 text-left">Account</th><th className="px-4 py-2 text-left">Amount</th><th className="px-4 py-2 text-left">Status</th><th className="px-4 py-2 text-left">Reason</th></tr></thead>
                        <tbody className="divide-y">
                            {transfer.lines.map((line) => (
                                <tr key={line.id}>
                                    <td className="px-4 py-2">{line.employee_code} - {line.employee_name}</td>
                                    <td className="px-4 py-2">{line.bank_name || '-'}</td>
                                    <td className="px-4 py-2">{line.bank_account_number || '-'}</td>
                                    <td className="px-4 py-2">{Number(line.amount || 0).toLocaleString('id-ID')}</td>
                                    <td className="px-4 py-2">{line.status}</td>
                                    <td className="px-4 py-2">{line.failure_reason || '-'}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
