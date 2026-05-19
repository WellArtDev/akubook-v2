import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Head, Link, router } from '@inertiajs/react'

export default function Index({ auth, exports, filters }) {
  const updateFilter = (key, value) => {
    router.get(route('e-faktur-exports.index'), { ...filters, [key]: value || undefined }, { preserveState: true, replace: true })
  }

  return (
    <AuthenticatedLayout
      user={auth.user}
      header={<h2 className="text-xl font-semibold leading-tight text-gray-800">E-Faktur Exports</h2>}
    >
      <Head title="E-Faktur Exports" />

      <div className="py-6">
        <div className="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
          <div className="rounded-lg bg-white p-4 shadow sm:flex sm:items-center sm:justify-between">
            <div className="grid gap-2 sm:grid-cols-4 sm:gap-3">
              <input
                className="rounded border-gray-300"
                placeholder="Search number"
                defaultValue={filters.search || ''}
                onBlur={(e) => updateFilter('search', e.target.value)}
              />
              <select className="rounded border-gray-300" value={filters.status || ''} onChange={(e) => updateFilter('status', e.target.value)}>
                <option value="">All Status</option>
                <option value="draft">Draft</option>
                <option value="generated">Generated</option>
              </select>
              <input type="date" className="rounded border-gray-300" value={filters.date_from || ''} onChange={(e) => updateFilter('date_from', e.target.value)} />
              <input type="date" className="rounded border-gray-300" value={filters.date_to || ''} onChange={(e) => updateFilter('date_to', e.target.value)} />
            </div>
            <Link href={route('e-faktur-exports.create')} className="mt-3 inline-flex rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white sm:mt-0">
              Generate Export
            </Link>
          </div>

          <div className="overflow-hidden rounded-lg bg-white shadow">
            <table className="min-w-full divide-y divide-gray-200 text-sm">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-4 py-3 text-left">Number</th>
                  <th className="px-4 py-3 text-left">Period</th>
                  <th className="px-4 py-3 text-left">Rows</th>
                  <th className="px-4 py-3 text-left">Status</th>
                  <th className="px-4 py-3 text-left">Action</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100 bg-white">
                {exports.data.map((row) => (
                  <tr key={row.id}>
                    <td className="px-4 py-3 font-medium">{row.export_number}</td>
                    <td className="px-4 py-3">{row.period_start} - {row.period_end}</td>
                    <td className="px-4 py-3">{row.row_count}</td>
                    <td className="px-4 py-3">{row.status}</td>
                    <td className="px-4 py-3">
                      <Link href={route('e-faktur-exports.show', row.id)} className="text-indigo-600 hover:text-indigo-800">Detail</Link>
                    </td>
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
