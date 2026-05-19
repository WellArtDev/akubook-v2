import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Head, router } from '@inertiajs/react'

export default function Index({ auth, period, rows, summary }) {
  const run = (e) => {
    e.preventDefault()
    const form = new FormData(e.currentTarget)
    router.post(route('asset-depreciation-journals.run'), { period: form.get('period') })
  }

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Depreciation Journal</h2>}>
      <Head title="Depreciation Journal" />
      <div className="py-6">
        <div className="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
          <form onSubmit={run} className="flex gap-3 rounded-lg bg-white p-4 shadow">
            <input name="period" type="month" className="rounded border-gray-300" defaultValue={period} />
            <button className="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Post Journal</button>
          </form>

          <div className="grid gap-4 sm:grid-cols-3">
            <div className="rounded bg-white p-4 shadow"><p className="text-xs text-gray-500">Asset Count</p><p className="text-lg font-semibold">{summary.asset_count}</p></div>
            <div className="rounded bg-white p-4 shadow"><p className="text-xs text-gray-500">Total Depreciation</p><p className="text-lg font-semibold">{summary.total_depreciation}</p></div>
            <div className="rounded bg-white p-4 shadow"><p className="text-xs text-gray-500">Posted</p><p className="text-lg font-semibold">{summary.posted_count}</p></div>
          </div>

          <div className="overflow-hidden rounded-lg bg-white shadow">
            <table className="min-w-full divide-y divide-gray-200 text-sm">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-4 py-3 text-left">Asset</th>
                  <th className="px-4 py-3 text-left">Monthly</th>
                  <th className="px-4 py-3 text-left">Journal</th>
                  <th className="px-4 py-3 text-left">Posted At</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100 bg-white">
                {rows.map((row) => (
                  <tr key={row.id}>
                    <td className="px-4 py-3">{row.asset_code} - {row.asset_name}</td>
                    <td className="px-4 py-3">{row.monthly_depreciation}</td>
                    <td className="px-4 py-3">{row.journal_number || '-'}</td>
                    <td className="px-4 py-3">{row.journal_posted_at || '-'}</td>
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
