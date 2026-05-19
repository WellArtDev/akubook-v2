import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Head, Link, router } from '@inertiajs/react'

export default function Index({ auth, disposals, filters }) {
  const updateFilter = (key, value) => {
    router.get(route('asset-disposals.index'), { ...filters, [key]: value }, { preserveState: true, replace: true })
  }

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Asset Disposal</h2>}>
      <Head title="Asset Disposal" />
      <div className="py-6">
        <div className="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
          <div className="flex justify-end">
            <Link href={route('asset-disposals.create')} className="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Add Disposal</Link>
          </div>

          <div className="grid gap-3 rounded-lg bg-white p-4 shadow sm:grid-cols-5">
            <input defaultValue={filters.search || ''} onBlur={(e) => updateFilter('search', e.target.value)} placeholder="Cari nomor/aset" className="rounded border-gray-300" />
            <select defaultValue={filters.status || ''} onChange={(e) => updateFilter('status', e.target.value)} className="rounded border-gray-300">
              <option value="">Semua Status</option>
              <option value="draft">Draft</option>
              <option value="posted">Posted</option>
            </select>
            <input type="date" defaultValue={filters.date_from || ''} onChange={(e) => updateFilter('date_from', e.target.value)} className="rounded border-gray-300" />
            <input type="date" defaultValue={filters.date_to || ''} onChange={(e) => updateFilter('date_to', e.target.value)} className="rounded border-gray-300" />
          </div>

          <div className="overflow-hidden rounded-lg bg-white shadow">
            <table className="min-w-full divide-y divide-gray-200 text-sm">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-4 py-3 text-left">No</th>
                  <th className="px-4 py-3 text-left">Tanggal</th>
                  <th className="px-4 py-3 text-left">Aset</th>
                  <th className="px-4 py-3 text-left">Book Value</th>
                  <th className="px-4 py-3 text-left">Proceeds</th>
                  <th className="px-4 py-3 text-left">Status</th>
                  <th className="px-4 py-3 text-left">Aksi</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100 bg-white">
                {disposals.data.map((d) => (
                  <tr key={d.id}>
                    <td className="px-4 py-3">{d.disposal_number}</td>
                    <td className="px-4 py-3">{d.disposal_date}</td>
                    <td className="px-4 py-3">{d.fixed_asset?.asset_code} - {d.fixed_asset?.name}</td>
                    <td className="px-4 py-3">{d.book_value}</td>
                    <td className="px-4 py-3">{d.proceeds_amount}</td>
                    <td className="px-4 py-3">{d.status}</td>
                    <td className="px-4 py-3"><Link href={route('asset-disposals.show', d.id)} className="text-indigo-600">Detail</Link></td>
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
