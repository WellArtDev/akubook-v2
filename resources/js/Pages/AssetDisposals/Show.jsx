import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Head, Link, router } from '@inertiajs/react'

export default function Show({ auth, disposal, gainLoss }) {
  const postDisposal = () => {
    if (confirm('Posting disposal aset ini?')) {
      router.post(route('asset-disposals.post', disposal.id))
    }
  }

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Asset Disposal Detail</h2>}>
      <Head title={disposal.disposal_number} />
      <div className="py-6">
        <div className="mx-auto max-w-5xl space-y-4 sm:px-6 lg:px-8">
          <div className="flex justify-between rounded-lg bg-white p-4 shadow">
            <div>
              <h3 className="text-lg font-semibold">{disposal.disposal_number}</h3>
              <p className="text-sm text-gray-500">{disposal.fixed_asset?.asset_code} - {disposal.fixed_asset?.name}</p>
            </div>
            <div className="flex gap-2">
              <Link href={route('asset-disposals.index')} className="rounded border px-4 py-2 text-sm">Back</Link>
              {disposal.status === 'draft' && <button onClick={postDisposal} className="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Post</button>}
            </div>
          </div>

          <div className="grid gap-4 sm:grid-cols-3">
            <div className="rounded bg-white p-4 shadow"><p className="text-xs text-gray-500">Acquisition Cost</p><p className="text-lg font-semibold">{disposal.acquisition_cost}</p></div>
            <div className="rounded bg-white p-4 shadow"><p className="text-xs text-gray-500">Accumulated Depreciation</p><p className="text-lg font-semibold">{disposal.accumulated_depreciation}</p></div>
            <div className="rounded bg-white p-4 shadow"><p className="text-xs text-gray-500">Book Value</p><p className="text-lg font-semibold">{disposal.book_value}</p></div>
            <div className="rounded bg-white p-4 shadow"><p className="text-xs text-gray-500">Proceeds</p><p className="text-lg font-semibold">{disposal.proceeds_amount}</p></div>
            <div className="rounded bg-white p-4 shadow"><p className="text-xs text-gray-500">Gain/Loss</p><p className="text-lg font-semibold">{gainLoss}</p></div>
            <div className="rounded bg-white p-4 shadow"><p className="text-xs text-gray-500">Status</p><p className="text-lg font-semibold">{disposal.status}</p></div>
          </div>

          <div className="rounded bg-white p-4 shadow">
            <h4 className="mb-2 font-semibold">Journal</h4>
            {disposal.journal_entry ? (
              <table className="min-w-full text-sm">
                <tbody>
                  {disposal.journal_entry.lines.map((line) => (
                    <tr key={line.id} className="border-t">
                      <td className="py-2">{line.account?.code} - {line.account?.name}</td>
                      <td className="py-2 text-right">Dr {line.debit}</td>
                      <td className="py-2 text-right">Cr {line.credit}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            ) : <p className="text-sm text-gray-500">Belum diposting.</p>}
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  )
}
