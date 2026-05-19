import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';

export default function Create({ auth, purchaseOrders, purchaseOrder, lines = [] }) {
  const selected = purchaseOrder || null;

  const { data, setData, post, processing, errors } = useForm({
    gr_date: new Date().toISOString().slice(0, 10),
    purchase_order_id: selected?.id || '',
    reference_number: '',
    notes: '',
    lines: lines.map((line) => ({
      purchase_order_line_id: line.id,
      receipt_quantity: line.remaining_quantity,
      inspection_notes: '',
    })),
  });

  const onSelectPO = (id) => {
    setData('purchase_order_id', id);
    router.get(route('goods-receipts.create'), { purchase_order_id: id }, { preserveState: false, replace: true });
  };

  const updateLine = (idx, key, value) => {
    const next = [...data.lines];
    next[idx] = { ...next[idx], [key]: value };
    setData('lines', next);
  };

  const submit = (e) => {
    e.preventDefault();
    post(route('goods-receipts.store'));
  };

  return (
    <AuthenticatedLayout user={auth.user}>
      <Head title="Create Goods Receipt" />
      <div className="py-6">
        <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <form onSubmit={submit} className="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
            <h1 className="text-xl font-semibold">Create Goods Receipt</h1>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
              <input type="date" className="rounded border-gray-300" value={data.gr_date} onChange={(e) => setData('gr_date', e.target.value)} />
              <select className="rounded border-gray-300" value={data.purchase_order_id} onChange={(e) => onSelectPO(e.target.value)}>
                <option value="">Pilih PO</option>
                {purchaseOrders.map((po) => <option key={po.id} value={po.id}>{po.po_number}</option>)}
              </select>
              <input className="rounded border-gray-300" placeholder="Reference" value={data.reference_number} onChange={(e) => setData('reference_number', e.target.value)} />
            </div>
            {errors.purchase_order_id && <div className="text-sm text-red-600">{errors.purchase_order_id}</div>}

            <div className="overflow-x-auto border rounded">
              <table className="min-w-full text-sm">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-3 py-2 text-left">Product</th>
                    <th className="px-3 py-2 text-left">Qty PO</th>
                    <th className="px-3 py-2 text-left">Received</th>
                    <th className="px-3 py-2 text-left">Remaining</th>
                    <th className="px-3 py-2 text-left">Receipt Qty</th>
                  </tr>
                </thead>
                <tbody>
                  {lines.map((line, idx) => (
                    <tr key={line.id} className="border-t">
                      <td className="px-3 py-2">{line.product_name}</td>
                      <td className="px-3 py-2">{line.quantity}</td>
                      <td className="px-3 py-2">{line.received_quantity}</td>
                      <td className="px-3 py-2">{line.remaining_quantity}</td>
                      <td className="px-3 py-2">
                        <input type="number" step="0.001" min="0" max={line.remaining_quantity} className="rounded border-gray-300 w-28" value={data.lines[idx]?.receipt_quantity || ''} onChange={(e) => updateLine(idx, 'receipt_quantity', e.target.value)} />
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            <textarea className="w-full rounded border-gray-300" rows="3" placeholder="Notes" value={data.notes} onChange={(e) => setData('notes', e.target.value)} />

            <button disabled={processing} className="px-4 py-2 bg-indigo-600 text-white rounded-md">Simpan</button>
          </form>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
