import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

export default function Show({ auth, goodsReceipt }) {
  const receive = () => {
    const receivedAt = new Date().toISOString();
    router.post(route('goods-receipts.receive', goodsReceipt.id), {
      received_at: receivedAt,
      lines: goodsReceipt.lines.map((line) => ({
        id: line.id,
        accepted_quantity: line.receipt_quantity,
        rejected_quantity: 0,
        inspection_notes: line.inspection_notes || '',
      })),
    });
  };

  const cancel = () => {
    const reason = prompt('Alasan cancel GR');
    if (!reason) return;
    router.post(route('goods-receipts.cancel', goodsReceipt.id), { reason });
  };

  return (
    <AuthenticatedLayout user={auth.user}>
      <Head title={`Goods Receipt ${goodsReceipt.gr_number}`} />
      <div className="py-6">
        <div className="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-4">
          <div className="bg-white shadow-sm sm:rounded-lg p-6">
            <h1 className="text-xl font-semibold">{goodsReceipt.gr_number}</h1>
            <div className="text-sm text-gray-600 mt-2">PO: {goodsReceipt.purchase_order?.po_number} | Status: {goodsReceipt.status}</div>
            <div className="mt-4 flex gap-2">
              {goodsReceipt.can_receive && <button onClick={receive} className="px-4 py-2 bg-green-600 text-white rounded-md">Receive</button>}
              {goodsReceipt.can_cancel && goodsReceipt.status !== 'cancelled' && <button onClick={cancel} className="px-4 py-2 bg-red-600 text-white rounded-md">Cancel</button>}
            </div>
          </div>

          <div className="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
            <table className="min-w-full text-sm">
              <thead className="bg-gray-50 text-left">
                <tr>
                  <th className="px-4 py-3">Product</th>
                  <th className="px-4 py-3">PO Qty</th>
                  <th className="px-4 py-3">Receipt Qty</th>
                  <th className="px-4 py-3">Accepted</th>
                  <th className="px-4 py-3">Rejected</th>
                </tr>
              </thead>
              <tbody>
                {goodsReceipt.lines.map((line) => (
                  <tr key={line.id} className="border-t">
                    <td className="px-4 py-3">{line.product_name}</td>
                    <td className="px-4 py-3">{line.po_quantity}</td>
                    <td className="px-4 py-3">{line.receipt_quantity}</td>
                    <td className="px-4 py-3">{line.accepted_quantity}</td>
                    <td className="px-4 py-3">{line.rejected_quantity}</td>
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
