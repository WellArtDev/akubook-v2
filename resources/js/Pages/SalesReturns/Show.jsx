import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

export default function Show({ auth, salesReturn }) {
  const approve = () => router.post(route('sales-returns.approve', salesReturn.id));
  const reject = () => {
    const reason = window.prompt('Reject reason');
    if (reason) router.post(route('sales-returns.reject', salesReturn.id), { reason });
  };
  const receive = () => {
    router.post(route('sales-returns.receive', salesReturn.id), {
      lines: salesReturn.lines.map((line) => ({
        id: line.id,
        accepted_quantity: line.return_quantity,
        rejected_quantity: 0,
        inspection_notes: line.inspection_notes || '',
      })),
    });
  };
  const complete = () => router.post(route('sales-returns.complete', salesReturn.id));

  return (
    <AuthenticatedLayout user={auth.user}>
      <Head title={salesReturn.rma_number} />
      <div className="py-6">
        <div className="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
          <div className="rounded bg-white p-6 shadow">
            <div className="flex items-start justify-between">
              <div>
                <h1 className="text-2xl font-semibold">{salesReturn.rma_number}</h1>
                <p className="text-gray-600">{salesReturn.customer?.name} · {salesReturn.status}</p>
              </div>
              <div className="flex gap-2">
                {salesReturn.status === 'pending' && <button onClick={approve} className="rounded bg-green-600 px-3 py-2 text-white">Approve</button>}
                {['pending', 'approved'].includes(salesReturn.status) && <button onClick={reject} className="rounded bg-red-600 px-3 py-2 text-white">Reject</button>}
                {salesReturn.status === 'approved' && <button onClick={receive} className="rounded bg-blue-600 px-3 py-2 text-white">Receive</button>}
                {salesReturn.status === 'received' && <button onClick={complete} className="rounded bg-purple-600 px-3 py-2 text-white">Complete</button>}
              </div>
            </div>
            <div className="mt-4 grid gap-4 md:grid-cols-3">
              <div><div className="text-sm text-gray-500">Invoice</div><div>{salesReturn.sales_invoice?.invoice_number}</div></div>
              <div><div className="text-sm text-gray-500">Return Date</div><div>{salesReturn.return_date}</div></div>
              <div><div className="text-sm text-gray-500">Total</div><div>{Number(salesReturn.total_amount).toLocaleString()}</div></div>
            </div>
            <div className="mt-4"><div className="text-sm text-gray-500">Reason</div><div>{salesReturn.return_reason}</div></div>
          </div>

          <div className="overflow-hidden rounded bg-white shadow">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Item</th>
                  <th className="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Return</th>
                  <th className="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Accepted</th>
                  <th className="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Rejected</th>
                  <th className="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Total</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200">
                {salesReturn.lines.map((line) => (
                  <tr key={line.id}>
                    <td className="px-6 py-4">{line.product_name}</td>
                    <td className="px-6 py-4 text-right">{line.return_quantity}</td>
                    <td className="px-6 py-4 text-right">{line.accepted_quantity}</td>
                    <td className="px-6 py-4 text-right">{line.rejected_quantity}</td>
                    <td className="px-6 py-4 text-right">{Number(line.line_total).toLocaleString()}</td>
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
