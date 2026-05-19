import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Head, Link, router } from '@inertiajs/react'

export default function Index({ auth, assets, filters, statuses }) {
  const updateFilter = (key, value) => {
    router.get(route('fixed-assets.index'), { ...filters, [key]: value || undefined }, { preserveState: true, replace: true })
  }

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Fixed Assets</h2>}>
      <Head title="Fixed Assets" />
      <div className="py-6">
        <div className="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
          <div className="rounded-lg bg-white p-4 shadow sm:flex sm:items-center sm:justify-between">
            <div className="grid gap-2 sm:grid-cols-2 sm:gap-3">
              <input className="rounded border-gray-300" placeholder="Search code/name" defaultValue={filters.search || ''} onBlur={(e) => updateFilter('search', e.target.value)} />
              <select className="rounded border-gray-300" value={filters.status || ''} onChange={(e) => updateFilter('status', e.target.value)}>
                <option value="">All Status</option>
                {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
              </select>
            </div>
            <Link href={route('fixed-assets.create')} className="mt-3 inline-flex rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white sm:mt-0">Add Asset</Link>
          </div>

          <div className="overflow-hidden rounded-lg bg-white shadow">
            <table className="min-w-full divide-y divide-gray-200 text-sm">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-4 py-3 text-left">Code</th>
                  <th className="px-4 py-3 text-left">Name</th>
                  <th className="px-4 py-3 text-left">Date</th>
                  <th className="px-4 py-3 text-left">Cost</th>
                  <th className="px-4 py-3 text-left">Status</th>
                  <th className="px-4 py-3 text-left">Action</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100 bg-white">
                {assets.data.map((row) => (
                  <tr key={row.id}>
                    <td className="px-4 py-3">{row.asset_code}</td>
                    <td className="px-4 py-3">{row.name}</td>
                    <td className="px-4 py-3">{row.acquisition_date}</td>
                    <td className="px-4 py-3">{row.acquisition_cost}</td>
                    <td className="px-4 py-3">{row.status}</td>
                    <td className="px-4 py-3">
                      <Link href={route('fixed-assets.show', row.id)} className="text-indigo-600">Detail</Link>
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
