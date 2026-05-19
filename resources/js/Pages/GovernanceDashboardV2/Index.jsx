import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Head, useForm } from '@inertiajs/react'

export default function Index({ auth, filters, kpis, trend, latest, generated_at }) {
  const { data, setData, get, processing } = useForm({
    date_from: filters.date_from || '',
    date_to: filters.date_to || '',
  })

  const submit = (e) => {
    e.preventDefault()
    get(route('governance-dashboard-v2.index'))
  }

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Governance Dashboard v2</h2>}>
      <Head title="Governance Dashboard v2" />
      <div className="py-6">
        <div className="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
          <form onSubmit={submit} className="rounded-lg bg-white p-4 shadow">
            <div className="grid gap-3 sm:grid-cols-3">
              <div>
                <label className="text-sm font-medium text-gray-700">From</label>
                <input type="date" className="mt-1 w-full rounded border-gray-300" value={data.date_from} onChange={(e) => setData('date_from', e.target.value)} />
              </div>
              <div>
                <label className="text-sm font-medium text-gray-700">To</label>
                <input type="date" className="mt-1 w-full rounded border-gray-300" value={data.date_to} onChange={(e) => setData('date_to', e.target.value)} />
              </div>
              <div className="flex items-end">
                <button type="submit" disabled={processing} className="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">Apply</button>
              </div>
            </div>
          </form>

          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card title="Retention Runs" value={kpis.retention_runs} />
            <Card title="Enforcement Count" value={kpis.enforcement_count} />
            <Card title="Sensitive Alerts" value={kpis.sensitive_alerts} />
            <Card title="Export Packs" value={kpis.export_packs} />
          </div>

          <div className="grid gap-4 lg:grid-cols-2">
            <div className="rounded-lg bg-white p-4 shadow">
              <h3 className="text-sm font-semibold text-gray-700">Recent Status</h3>
              <dl className="mt-3 space-y-2 text-sm">
                <div className="flex justify-between"><dt className="text-gray-500">Last Retention Run</dt><dd>{latest.retention_run ? latest.retention_run.created_at : '-'}</dd></div>
                <div className="flex justify-between"><dt className="text-gray-500">Last Export Pack</dt><dd>{latest.export_pack ? latest.export_pack.generated_at : '-'}</dd></div>
              </dl>
            </div>

            <div className="rounded-lg bg-white p-4 shadow">
              <h3 className="text-sm font-semibold text-gray-700">Daily Trend</h3>
              <table className="mt-3 min-w-full text-sm">
                <thead>
                  <tr className="text-left text-gray-500">
                    <th className="pb-2">Metric</th>
                    <th className="pb-2">Entries</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  <TrendRow label="Retention Runs" data={trend.retention_runs} />
                  <TrendRow label="Workflow Enforcements" data={trend.workflow_enforcements} />
                  <TrendRow label="Sensitive Alerts" data={trend.sensitive_alerts} />
                  <TrendRow label="Export Packs" data={trend.export_packs} />
                </tbody>
              </table>
            </div>
          </div>

          <p className="text-xs text-gray-500">Generated at: {generated_at}</p>
        </div>
      </div>
    </AuthenticatedLayout>
  )
}

function Card({ title, value }) {
  return (
    <div className="rounded-lg bg-white p-4 shadow">
      <p className="text-sm text-gray-500">{title}</p>
      <p className="mt-1 text-2xl font-semibold text-gray-900">{value}</p>
    </div>
  )
}

function TrendRow({ label, data }) {
  return (
    <tr>
      <td className="py-2 pr-3">{label}</td>
      <td className="py-2 text-gray-700">{data.length}</td>
    </tr>
  )
}
