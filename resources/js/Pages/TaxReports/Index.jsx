import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Head, router } from '@inertiajs/react'

export default function Index({ auth, rows, summary, filters, taxTypes }) {
  const updateFilter = (key, value) => {
    router.get(route('tax-reports.index'), { ...filters, [key]: value || undefined }, { preserveState: true, replace: true })
  }

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Tax Reporting</h2>}>
      <Head title="Tax Reporting" />
      <div className="py-6">
        <div className="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
          <div className="rounded-lg bg-white p-4 shadow sm:grid sm:grid-cols-3 sm:gap-3">
            <input type="date" className="rounded border-gray-300" value={filters.date_from || ''} onChange={(e) => updateFilter('date_from', e.target.value)} />
            <input type="date" className="rounded border-gray-300" value={filters.date_to || ''} onChange={(e) => updateFilter('date_to', e.target.value)} />
            <select className="rounded border-gray-300" value={filters.tax_type || ''} onChange={(e) => updateFilter('tax_type', e.target.value)}>
              <option value="">All Tax Types</option>
              {Object.entries(taxTypes).map(([key, label]) => <option key={key} value={key}>{label}</option>)}
            </select>
          </div>

          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div className="rounded bg-white p-4 shadow"><p className="text-xs text-gray-500">PPN Output</p><p className="text-lg font-semibold">{summary.ppn_out}</p></div>
            <div className="rounded bg-white p-4 shadow"><p className="text-xs text-gray-500">PPN Input</p><p className="text-lg font-semibold">{summary.ppn_in}</p></div>
            <div className="rounded bg-white p-4 shadow"><p className="text-xs text-gray-500">Withholding</p><p className="text-lg font-semibold">{summary.withholding}</p></div>
            <div className="rounded bg-white p-4 shadow"><p className="text-xs text-gray-500">Net VAT</p><p className="text-lg font-semibold">{summary.net_vat}</p></div>
          </div>

          <div className="overflow-hidden rounded-lg bg-white shadow">
            <table className="min-w-full divide-y divide-gray-200 text-sm">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-4 py-3 text-left">Source</th>
                  <th className="px-4 py-3 text-left">Number</th>
                  <th className="px-4 py-3 text-left">Date</th>
                  <th className="px-4 py-3 text-left">Party</th>
                  <th className="px-4 py-3 text-left">Type</th>
                  <th className="px-4 py-3 text-left">DPP</th>
                  <th className="px-4 py-3 text-left">Tax</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100 bg-white">
                {rows.map((row, idx) => (
                  <tr key={idx}>
                    <td className="px-4 py-3">{row.source}</td>
                    <td className="px-4 py-3">{row.number}</td>
                    <td className="px-4 py-3">{row.date}</td>
                    <td className="px-4 py-3">{row.party}</td>
                    <td className="px-4 py-3">{row.tax_type}</td>
                    <td className="px-4 py-3">{row.dpp}</td>
                    <td className="px-4 py-3">{row.tax_amount}</td>
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
