import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Show({ auth, reconciliation }) {
    const matchLine = (lineId) => {
        const reference = prompt('Reference Type (manual/payment/journal):', 'manual');
        if (!reference) return;
        router.post(route('bank-reconciliation-lines.match', lineId), {
            matched_reference_type: reference,
            matched_reference_id: null,
        });
    };

    const unmatchLine = (lineId) => router.post(route('bank-reconciliation-lines.unmatch', lineId));
    const markReconciled = () => {
        if (!confirm('Finalize reconciliation?')) return;
        router.post(route('bank-reconciliations.reconcile', reconciliation.id));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={reconciliation.reconciliation_number} />
            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded bg-white p-6 shadow">
                        <div className="mb-4 flex items-center justify-between">
                            <h1 className="text-2xl font-semibold">{reconciliation.reconciliation_number}</h1>
                            <div className="flex gap-3">
                                <Link href={route('bank-reconciliations.index')} className="text-indigo-600">Back</Link>
                                {reconciliation.status === 'draft' && (
                                    <button onClick={markReconciled} className="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Mark Reconciled</button>
                                )}
                            </div>
                        </div>
                        <div className="grid gap-3 md:grid-cols-3 text-sm">
                            <div><span className="font-medium">Bank:</span> {reconciliation.bank_account?.name}</div>
                            <div><span className="font-medium">Period:</span> {reconciliation.statement_start_date} - {reconciliation.statement_end_date}</div>
                            <div><span className="font-medium">Status:</span> {reconciliation.status}</div>
                            <div><span className="font-medium">Opening:</span> {Number(reconciliation.statement_opening_balance).toLocaleString()}</div>
                            <div><span className="font-medium">Closing:</span> {Number(reconciliation.statement_closing_balance).toLocaleString()}</div>
                            <div><span className="font-medium">System:</span> {Number(reconciliation.system_balance).toLocaleString()}</div>
                            <div><span className="font-medium">Matched Debit:</span> {Number(reconciliation.matched_debit_total).toLocaleString()}</div>
                            <div><span className="font-medium">Matched Credit:</span> {Number(reconciliation.matched_credit_total).toLocaleString()}</div>
                            <div><span className="font-medium">Difference:</span> {Number(reconciliation.difference).toLocaleString()}</div>
                        </div>
                    </div>

                    <div className="overflow-hidden rounded bg-white shadow">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Date</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Description</th>
                                    <th className="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Debit</th>
                                    <th className="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Credit</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Match</th>
                                    <th className="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Action</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 bg-white">
                                {reconciliation.lines.map((line) => (
                                    <tr key={line.id}>
                                        <td className="px-4 py-3 text-sm">{line.transaction_date}</td>
                                        <td className="px-4 py-3 text-sm">{line.description}</td>
                                        <td className="px-4 py-3 text-right text-sm">{Number(line.debit).toLocaleString()}</td>
                                        <td className="px-4 py-3 text-right text-sm">{Number(line.credit).toLocaleString()}</td>
                                        <td className="px-4 py-3 text-sm">{line.is_matched ? `${line.matched_reference_type || 'matched'}` : 'unmatched'}</td>
                                        <td className="px-4 py-3 text-right text-sm">
                                            {reconciliation.status === 'draft' && (
                                                line.is_matched
                                                    ? <button onClick={() => unmatchLine(line.id)} className="text-red-600">Unmatch</button>
                                                    : <button onClick={() => matchLine(line.id)} className="text-indigo-600">Match</button>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
