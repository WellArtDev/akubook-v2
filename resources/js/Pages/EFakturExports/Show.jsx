import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Head, Link } from '@inertiajs/react'

export default function Show({ auth, exportBatch }) {
  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold leading-tight text-gray-800">E-Faktur Export Detail</h2>}>
      <Head title={exportBatch.export_number} />
      <div className="py-6">
        <div className="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
          <div className="rounded-lg bg-white p-6 shadow">
            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
              <div><p className="text-xs text-gray-500">Number</p><p className="font-medium">{exportBatch.export_number}</p></div>
              <div><p className="text-xs text-gray-500">Period</p><p className="font-medium">{exportBatch.period_start} - {exportBatch.period_end}</p></div>
              <div><p className="text-xs text-gray-500">Rows</p><p className="font-medium">{exportBatch.row_count}</p></div>
              <div><p className="text-xs text-gray-500">Status</p><p className="font-medium">{exportBatch.status}</p></div>
            </div>
            <div className="mt-4 flex gap-2">
              <Link href={route('e-faktur-exports.download', exportBatch.id)} className="rounded bg-green-600 px-3 py-2 text-sm font-semibold text-white">Download CSV</Link>
              <Link href={route('e-faktur-exports.index')} className="rounded border px-3 py-2 text-sm">Back</Link>
            </div>
          </div>

          <div className="overflow-hidden rounded-lg bg-white shadow">
            <table className="min-w-full divide-y divide-gray-200 text-sm">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-4 py-3 text-left">No</th>
                  <th className="px-4 py-3 text-left">Faktur</th>
                  <th className="px-4 py-3 text-left">Date</th>
                  <th className="px-4 py-3 text-left">Customer</th>
                  <th className="px-4 py-3 text-left">DPP</th>
                  <th className="px-4 py-3 text-left">PPN</th>
                  <th className="px-4 py-3 text-left">Total</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100 bg-white">
                {exportBatch.lines.map((line) => (
                  <tr key={line.id}>
                    <td className="px-4 py-3">{line.line_number}</td>
                    <td className="px-4 py-3">{line.faktur_number}</td>
                    <td className="px-4 py-3">{line.faktur_date}</td>
                    <td className="px-4 py-3">{line.customer_name}</td>
                    <td className="px-4 py-3">{line.dpp}</td>
                    <td className="px-4 py-3">{line.ppn_amount}</td>
                    <td className="px-4 py-3">{line.grand_total}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  )
}
