import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Head, router } from '@inertiajs/react'

export default function Index({ auth, period, rows, summary }) {
  const run = (e) => {
    e.preventDefault()
    const form = new FormData(e.currentTarget)
    router.get(route('asset-depreciations.index'), { period: form.get('period'), run: 1 }, { preserveState: true })
  }

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Asset Depreciation</h2>}>
      <Head title="Asset Depreciation" />
      <div className="py-6">
        <div className="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
          <form onSubmit={run} className="flex gap-3 rounded-lg bg-white p-4 shadow">
            <input name="period" type="month" className="rounded border-gray-300" defaultValue={period} />
            <button className="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Run</button>
            <button type="button" onClick={() => router.get(route('asset-depreciation-journals.index'), { period })} className="rounded bg-gray-700 px-4 py-2 text-sm font-semibold text-white">Jurnal</button>
          </form>

          <div className="grid gap-4 sm:grid-cols-3">
            <div className="rounded bg-white p-4 shadow"><p className="text-xs text-gray-500">Monthly Depreciation</p><p className="text-lg font-semibold">{summary.total_monthly_depreciation}</p></div>
            <div className="rounded bg-white p-4 shadow"><p className="text-xs text-gray-500">Accumulated</p><p className="text-lg font-semibold">{summary.total_accumulated_depreciation}</p></div>
            <div className="rounded bg-white p-4 shadow"><p className="text-xs text-gray-500">Book Value</p><p className="text-lg font-semibold">{summary.total_book_value_end}</p></div>
          </div>

          <div className="overflow-hidden rounded-lg bg-white shadow">
            <table className="min-w-full divide-y divide-gray-200 text-sm">
              <thead className="bg-gray-50"><tr><th className="px-4 py-3 text-left">Asset</th><th className="px-4 py-3 text-left">Monthly</th><th className="px-4 py-3 text-left">Accumulated</th><th className="px-4 py-3 text-left">Book Value</th></tr></thead>
              <tbody className="divide-y divide-gray-100 bg-white">
                {rows.map((row) => <tr key={row.id}><td className="px-4 py-3">{row.asset_code} - {row.asset_name}</td><td className="px-4 py-3">{row.monthly_depreciation}</td><td className="px-4 py-3">{row.accumulated_depreciation}</td><td className="px-4 py-3">{row.book_value_end}</td></tr>)}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  )
}
