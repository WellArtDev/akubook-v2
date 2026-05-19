import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { useMemo } from 'react';

export default function Create({ auth, invoices }) {
  const { data, setData, post, processing, errors } = useForm({
    return_date: new Date().toISOString().slice(0, 10),
    sales_invoice_id: '',
    return_reason: '',
    lines: [],
  });

  const selectedInvoice = useMemo(() => invoices.find((inv) => String(inv.id) === String(data.sales_invoice_id)), [invoices, data.sales_invoice_id]);

  const loadInvoiceLines = (invoiceId) => {
    const invoice = invoices.find((inv) => String(inv.id) === String(invoiceId));
    setData((prev) => ({
      ...prev,
      sales_invoice_id: invoiceId,
      lines: invoice
        ? invoice.lines.map((line) => ({
            sales_invoice_line_id: line.id,
            product_name: line.product_name,
            max_quantity: line.quantity,
            return_quantity: line.quantity,
            inspection_notes: '',
          }))
        : [],
    }));
  };

  const submit = (e) => {
    e.preventDefault();
    post(route('sales-returns.store'));
  };

  return (
    <AuthenticatedLayout user={auth.user}>
      <Head title="Create Sales Return" />
      <div className="py-6">
        <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
          <form onSubmit={submit} className="space-y-6 rounded bg-white p-6 shadow">
            <h1 className="text-xl font-semibold">Create Sales Return</h1>

            <div className="grid gap-4 md:grid-cols-2">
              <div>
                <label className="mb-1 block text-sm">Return Date</label>
                <input type="date" className="w-full rounded border-gray-300" value={data.return_date} onChange={(e) => setData('return_date', e.target.value)} />
                {errors.return_date && <div className="text-sm text-red-600">{errors.return_date}</div>}
              </div>
              <div>
                <label className="mb-1 block text-sm">Invoice</label>
                <select className="w-full rounded border-gray-300" value={data.sales_invoice_id} onChange={(e) => loadInvoiceLines(e.target.value)}>
                  <option value="">Select invoice</option>
                  {invoices.map((invoice) => (
                    <option key={invoice.id} value={invoice.id}>{invoice.invoice_number} - {invoice.customer?.name}</option>
                  ))}
                </select>
                {errors.sales_invoice_id && <div className="text-sm text-red-600">{errors.sales_invoice_id}</div>}
              </div>
            </div>

            <div>
              <label className="mb-1 block text-sm">Return Reason</label>
              <textarea className="w-full rounded border-gray-300" rows="3" value={data.return_reason} onChange={(e) => setData('return_reason', e.target.value)} />
              {errors.return_reason && <div className="text-sm text-red-600">{errors.return_reason}</div>}
            </div>

            {selectedInvoice && (
              <div>
                <h2 className="mb-2 font-medium">Return Lines</h2>
                <div className="space-y-3">
                  {data.lines.map((line, index) => (
                    <div key={line.sales_invoice_line_id} className="grid gap-3 rounded border p-3 md:grid-cols-3">
                      <div>
                        <div className="text-sm text-gray-600">Item</div>
                        <div className="font-medium">{line.product_name}</div>
                        <div className="text-xs text-gray-500">Max: {line.max_quantity}</div>
                      </div>
                      <div>
                        <label className="mb-1 block text-sm">Return Qty</label>
                        <input
                          type="number"
                          step="0.001"
                          min="0"
                          max={line.max_quantity}
                          className="w-full rounded border-gray-300"
                          value={line.return_quantity}
                          onChange={(e) => {
                            const lines = [...data.lines];
                            lines[index].return_quantity = e.target.value;
                            setData('lines', lines);
                          }}
                        />
                      </div>
                      <div>
                        <label className="mb-1 block text-sm">Inspection Notes</label>
                        <input
                          className="w-full rounded border-gray-300"
                          value={line.inspection_notes}
                          onChange={(e) => {
                            const lines = [...data.lines];
                            lines[index].inspection_notes = e.target.value;
                            setData('lines', lines);
                          }}
                        />
                      </div>
                    </div>
                  ))}
                </div>
                {errors.lines && <div className="mt-2 text-sm text-red-600">{errors.lines}</div>}
              </div>
            )}

            <button type="submit" disabled={processing} className="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50">Save Return</button>
          </form>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
