import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Head, Link, router } from '@inertiajs/react'

export default function Index({ auth, purchaseInvoices, filters = {} }) {
  const updateFilter = (key, value) => {
    router.get(route('purchase-invoices.index'), { ...filters, [key]: value || undefined }, { preserveState: true, replace: true })
  }

  return (
    <AuthenticatedLayout user={auth.user}>
      <Head title="Purchase Invoices" />

      <div className="py-6">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
          <div className="bg-white shadow-sm sm:rounded-lg p-6 flex items-center justify-between">
            <h1 className="text-xl font-semibold text-gray-900">Purchase Invoices</h1>
            <Link href={route('purchase-invoices.create')} className="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">Create Invoice</Link>
          </div>

          <div className="bg-white shadow-sm sm:rounded-lg p-4 grid grid-cols-1 md:grid-cols-3 gap-3">
            <input
              value={filters.search || ''}
              onChange={(e) => updateFilter('search', e.target.value)}
              placeholder="Search number/supplier"
              className="border-gray-300 rounded-md"
            />
            <select
              value={filters.status || ''}
              onChange={(e) => updateFilter('status', e.target.value)}
              className="border-gray-300 rounded-md"
            >
              <option value="">All status</option>
              <option value="draft">Draft</option>
              <option value="posted">Posted</option>
              <option value="partially_paid">Partially Paid</option>
              <option value="paid">Paid</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>

          <div className="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Number</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200 bg-white">
                {purchaseInvoices.data.map((invoice) => (
                  <tr key={invoice.id} className="hover:bg-gray-50 cursor-pointer" onClick={() => router.visit(route('purchase-invoices.show', invoice.id))}>
                    <td className="px-4 py-3 text-sm text-gray-900">{invoice.invoice_number}</td>
                    <td className="px-4 py-3 text-sm text-gray-900">{invoice.invoice_date}</td>
                    <td className="px-4 py-3 text-sm text-gray-900">{invoice.supplier?.name}</td>
                    <td className="px-4 py-3 text-sm text-gray-900">{invoice.status}</td>
                    <td className="px-4 py-3 text-sm text-gray-900 text-right">{Number(invoice.total_amount || 0).toLocaleString('id-ID')}</td>
                  </tr>
                ))}
                {purchaseInvoices.data.length === 0 && (
                  <tr><td className="px-4 py-6 text-sm text-gray-500 text-center" colSpan={5}>No purchase invoices</td></tr>
                )}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  )
}
