import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Show({ auth, payment }) {
    const postForm = useForm({});
    const voidForm = useForm({ reason: '' });

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Supplier Payment {payment.payment_number}</h2>}
        >
            <Head title={`Supplier Payment ${payment.payment_number}`} />

            <div className="py-6">
                <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-4">
                    <div className="rounded bg-white p-6 shadow">
                        <div className="grid grid-cols-2 gap-4 text-sm">
                            <div>Date: {payment.payment_date}</div>
                            <div>Supplier: {payment.supplier?.name}</div>
                            <div>Method: {payment.payment_method}</div>
                            <div>Status: {payment.status}</div>
                            <div>Total: {payment.total_amount}</div>
                            <div>Allocated: {payment.allocated_amount}</div>
                            <div>Unapplied: {payment.unapplied_amount}</div>
                            <div>Reference: {payment.reference_number ?? '-'}</div>
                        </div>
                    </div>

                    {payment.status === 'draft' && (
                        <div className="rounded bg-white p-6 shadow space-y-3">
                            <button className="rounded bg-emerald-600 px-4 py-2 text-white" onClick={() => postForm.post(route('supplier-payments.post', payment.id))}>Post Payment</button>
                            <div className="flex gap-2">
                                <input className="w-full rounded border-gray-300" placeholder="Void reason" value={voidForm.data.reason} onChange={(e) => voidForm.setData('reason', e.target.value)} />
                                <button className="rounded bg-red-600 px-4 py-2 text-white" onClick={() => voidForm.post(route('supplier-payments.void', payment.id))}>Void</button>
                            </div>
                        </div>
                    )}

                    <div className="rounded bg-white p-6 shadow">
                        <h3 className="mb-3 font-semibold">Allocations</h3>
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead><tr><th className="px-3 py-2 text-left text-xs uppercase">Invoice</th><th className="px-3 py-2 text-left text-xs uppercase">Amount</th></tr></thead>
                            <tbody>
                                {payment.allocations.map((allocation) => (
                                    <tr key={allocation.id}><td className="px-3 py-2">{allocation.purchase_invoice?.invoice_number}</td><td className="px-3 py-2">{allocation.allocated_amount}</td></tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <Link className="text-indigo-600" href={route('supplier-payments.index')}>Back to list</Link>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
