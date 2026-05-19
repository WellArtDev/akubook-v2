import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';

export default function Create({ auth, invoices = [], invoice = null, lines = [] }) {
    const form = useForm({
        return_date: new Date().toISOString().slice(0, 10),
        purchase_invoice_id: invoice?.id ?? '',
        return_reason: '',
        lines: lines.map((line) => ({
            purchase_invoice_line_id: line.purchase_invoice_line_id,
            return_quantity: line.remaining_quantity,
            tax_percentage: 11,
            inspection_notes: '',
        })),
    });

    const selectedInvoice = invoices.find((x) => String(x.id) === String(form.data.purchase_invoice_id)) || invoice;

    const reloadByInvoice = (id) => {
        router.get(route('purchase-returns.create'), { purchase_invoice_id: id || undefined }, { preserveState: false });
    };

    const updateLine = (index, key, value) => {
        const next = [...form.data.lines];
        next[index] = { ...next[index], [key]: value };
        form.setData('lines', next);
    };

    const submit = (e) => {
        e.preventDefault();
        form.post(route('purchase-returns.store'));
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">New Purchase Return</h2>}>
            <Head title="New Purchase Return" />

            <div className="py-6">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <form onSubmit={submit} className="bg-white p-6 shadow-sm sm:rounded-lg space-y-4">
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label className="block text-sm mb-1">Return Date</label>
                                <input type="date" className="w-full border rounded px-3 py-2" value={form.data.return_date} onChange={(e) => form.setData('return_date', e.target.value)} />
                            </div>
                            <div>
                                <label className="block text-sm mb-1">Purchase Invoice</label>
                                <select
                                    className="w-full border rounded px-3 py-2"
                                    value={form.data.purchase_invoice_id}
                                    onChange={(e) => {
                                        form.setData('purchase_invoice_id', e.target.value);
                                        reloadByInvoice(e.target.value);
                                    }}
                                >
                                    <option value="">Select Invoice</option>
                                    {invoices.map((inv) => (
                                        <option key={inv.id} value={inv.id}>
                                            {inv.invoice_number} - {inv.supplier?.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm mb-1">Return Reason</label>
                                <input className="w-full border rounded px-3 py-2" value={form.data.return_reason} onChange={(e) => form.setData('return_reason', e.target.value)} />
                            </div>
                        </div>

                        {selectedInvoice && (
                            <div className="text-sm text-gray-600">Supplier: {selectedInvoice.supplier?.name} | Invoice: {selectedInvoice.invoice_number}</div>
                        )}

                        <div className="overflow-x-auto">
                            <table className="min-w-full text-sm border">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-3 py-2 text-left">Product</th>
                                        <th className="px-3 py-2 text-right">Invoice Qty</th>
                                        <th className="px-3 py-2 text-right">Remaining</th>
                                        <th className="px-3 py-2 text-right">Return Qty</th>
                                        <th className="px-3 py-2 text-right">Tax %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {lines.map((line, idx) => (
                                        <tr key={line.purchase_invoice_line_id} className="border-t">
                                            <td className="px-3 py-2">{line.product_name}</td>
                                            <td className="px-3 py-2 text-right">{line.invoice_quantity}</td>
                                            <td className="px-3 py-2 text-right">{line.remaining_quantity}</td>
                                            <td className="px-3 py-2 text-right">
                                                <input
                                                    type="number"
                                                    step="0.001"
                                                    min="0"
                                                    max={line.remaining_quantity}
                                                    className="w-28 border rounded px-2 py-1 text-right"
                                                    value={form.data.lines[idx]?.return_quantity ?? ''}
                                                    onChange={(e) => updateLine(idx, 'return_quantity', e.target.value)}
                                                />
                                            </td>
                                            <td className="px-3 py-2 text-right">
                                                <input
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    className="w-24 border rounded px-2 py-1 text-right"
                                                    value={form.data.lines[idx]?.tax_percentage ?? 11}
                                                    onChange={(e) => updateLine(idx, 'tax_percentage', e.target.value)}
                                                />
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        <div className="flex justify-end">
                            <button type="submit" className="px-4 py-2 bg-indigo-600 text-white rounded" disabled={form.processing}>
                                Save Purchase Return
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
