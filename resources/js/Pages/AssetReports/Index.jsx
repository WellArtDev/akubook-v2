import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, assets, disposals, summary, filters }) {
  const updateFilter = (key, value) => {
    router.get(route('asset-reports.index'), { ...filters, [key]: value }, { preserveState: true, replace: true });
  };

  return (
    <AuthenticatedLayout user={auth.user}>
      <Head title="Asset Reports" />

      <div className="py-12">
        <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
          <div className="rounded-lg bg-white p-6 shadow-sm">
            <div className="mb-4 flex items-center justify-between">
              <h1 className="text-xl font-semibold">Asset Reports</h1>
              <Link href={route('fixed-assets.index')} className="text-sm text-indigo-600 hover:text-indigo-800">Fixed Assets</Link>
            </div>

            <div className="mb-4 grid grid-cols-1 gap-3 md:grid-cols-4">
              <input value={filters.search || ''} onChange={(e) => updateFilter('search', e.target.value)} placeholder="Cari aset/disposal" className="rounded border-gray-300" />
              <select value={filters.status || ''} onChange={(e) => updateFilter('status', e.target.value)} className="rounded border-gray-300">
                <option value="">Semua Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="disposed">Disposed</option>
              </select>
              <input type="date" value={filters.date_from || ''} onChange={(e) => updateFilter('date_from', e.target.value)} className="rounded border-gray-300" />
              <input type="date" value={filters.date_to || ''} onChange={(e) => updateFilter('date_to', e.target.value)} className="rounded border-gray-300" />
            </div>

            <div className="mb-6 grid grid-cols-1 gap-3 md:grid-cols-4">
              <SummaryCard title="Total Acquisition" value={summary.total_acquisition_cost} />
              <SummaryCard title="Accumulated Depr." value={summary.total_accumulated_depreciation} />
              <SummaryCard title="Total Book Value" value={summary.total_book_value} />
              <SummaryCard title="Disposal Proceeds" value={summary.total_disposal_proceeds} />
            </div>

            <h2 className="mb-2 text-sm font-semibold text-gray-700">Asset Register</h2>
            <div className="mb-6 overflow-x-auto rounded border">
              <table className="min-w-full divide-y divide-gray-200 text-sm">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-3 py-2 text-left">Code</th>
                    <th className="px-3 py-2 text-left">Name</th>
                    <th className="px-3 py-2 text-left">Category</th>
                    <th className="px-3 py-2 text-right">Acq. Cost</th>
                    <th className="px-3 py-2 text-right">Accum. Depr.</th>
                    <th className="px-3 py-2 text-right">Book Value</th>
                    <th className="px-3 py-2 text-left">Status</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100 bg-white">
                  {assets.map((row) => (
                    <tr key={row.id}>
                      <td className="px-3 py-2">{row.asset_code}</td>
                      <td className="px-3 py-2">{row.name}</td>
                      <td className="px-3 py-2">{row.category}</td>
                      <td className="px-3 py-2 text-right">{formatMoney(row.acquisition_cost)}</td>
                      <td className="px-3 py-2 text-right">{formatMoney(row.accumulated_depreciation)}</td>
                      <td className="px-3 py-2 text-right">{formatMoney(row.book_value)}</td>
                      <td className="px-3 py-2">{row.status}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            <h2 className="mb-2 text-sm font-semibold text-gray-700">Disposal History</h2>
            <div className="overflow-x-auto rounded border">
              <table className="min-w-full divide-y divide-gray-200 text-sm">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-3 py-2 text-left">Number</th>
                    <th className="px-3 py-2 text-left">Date</th>
                    <th className="px-3 py-2 text-left">Asset</th>
                    <th className="px-3 py-2 text-right">Book Value</th>
                    <th className="px-3 py-2 text-right">Proceeds</th>
                    <th className="px-3 py-2 text-left">Status</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100 bg-white">
                  {disposals.map((row) => (
                    <tr key={row.id}>
                      <td className="px-3 py-2">{row.disposal_number}</td>
                      <td className="px-3 py-2">{row.disposal_date}</td>
                      <td className="px-3 py-2">{row.asset_code} - {row.asset_name}</td>
                      <td className="px-3 py-2 text-right">{formatMoney(row.book_value)}</td>
                      <td className="px-3 py-2 text-right">{formatMoney(row.proceeds_amount)}</td>
                      <td className="px-3 py-2">{row.status}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}

function SummaryCard({ title, value }) {
  return (
    <div className="rounded border bg-gray-50 p-3">
      <p className="text-xs text-gray-500">{title}</p>
      <p className="text-sm font-semibold text-gray-900">{formatMoney(value)}</p>
    </div>
  );
}

function formatMoney(value) {
  return Number(value || 0).toLocaleString('id-ID', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}
