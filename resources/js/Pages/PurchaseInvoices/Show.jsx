import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Head, router } from '@inertiajs/react'

export default function Show({ auth, purchaseInvoice }) {
  const postInvoice = () => {
    if (confirm('Post purchase invoice?')) {
      router.post(route('purchase-invoices.post', purchaseInvoice.id))
    }
  }

  const cancelInvoice = () => {
    if (confirm('Cancel purchase invoice?')) {
      router.post(route('purchase-invoices.cancel', purchaseInvoice.id))
    }
  }

  return (
    <AuthenticatedLayout user={auth.user}>
      <Head title={purchaseInvoice.invoice_number} />

      <div className="py-6">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
          <div className="bg-white shadow-sm sm:rounded-lg p-6 flex items-center justify-between">
            <div>
              <h1 className="text-xl font-semibold text-gray-900">{purchaseInvoice.invoice_number}</h1>
              <p className="text-sm text-gray-500">Supplier: {purchaseInvoice.supplier?.name}</p>
            </div>
            <div className="flex gap-2">
              {purchaseInvoice.status === 'draft' && (
                <button onClick={postInvoice} className="px-3 py-2 bg-green-600 text-white rounded-md text-sm">Post</button>
              )}
              {['draft', 'posted', 'partially_paid'].includes(purchaseInvoice.status) && (
                <button onClick={cancelInvoice} className="px-3 py-2 bg-red-600 text-white rounded-md text-sm">Cancel</button>
              )}
            </div>
          </div>

          <div className="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-4 py-3 text-left text-xs uppercase text-gray-500">Product</th>
                  <th className="px-4 py-3 text-right text-xs uppercase text-gray-500">Qty</th>
                  <th className="px-4 py-3 text-right text-xs uppercase text-gray-500">Price</th>
                  <th className="px-4 py-3 text-right text-xs uppercase text-gray-500">Tax</th>
                  <th className="px-4 py-3 text-right text-xs uppercase text-gray-500">Line Total</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200 bg-white">
                {purchaseInvoice.lines.map((line) => (
                  <tr key={line.id}>
                    <td className="px-4 py-3 text-sm">{line.product_name}</td>
                    <td className="px-4 py-3 text-sm text-right">{Number(line.invoice_quantity).toLocaleString('id-ID')}</td>
                    <td className="px-4 py-3 text-sm text-right">{Number(line.unit_price).toLocaleString('id-ID')}</td>
                    <td className="px-4 py-3 text-sm text-right">{Number(line.tax_amount).toLocaleString('id-ID')}</td>
                    <td className="px-4 py-3 text-sm text-right">{Number(line.line_total).toLocaleString('id-ID')}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          <div className="bg-white shadow-sm sm:rounded-lg p-6 flex justify-end">
            <div className="w-full md:w-80 space-y-1 text-sm">
              <div className="flex justify-between"><span>Subtotal</span><span>{Number(purchaseInvoice.subtotal || 0).toLocaleString('id-ID')}</span></div>
              <div className="flex justify-between"><span>Tax</span><span>{Number(purchaseInvoice.tax_amount || 0).toLocaleString('id-ID')}</span></div>
              <div className="flex justify-between font-semibold border-t pt-2"><span>Total</span><span>{Number(purchaseInvoice.total_amount || 0).toLocaleString('id-ID')}</span></div>
              <div className="flex justify-between"><span>Outstanding</span><span>{Number(purchaseInvoice.outstanding_amount || 0).toLocaleString('id-ID')}</span></div>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  )
}
