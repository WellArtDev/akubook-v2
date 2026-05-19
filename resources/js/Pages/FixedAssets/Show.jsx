import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Head, Link, router } from '@inertiajs/react'

export default function Show({ auth, asset }) {
  const destroy = () => {
    if (confirm('Delete fixed asset?')) {
      router.delete(route('fixed-assets.destroy', asset.id))
    }
  }

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Fixed Asset Detail</h2>}>
      <Head title={asset.asset_code} />
      <div className="py-6"><div className="mx-auto max-w-4xl space-y-4 sm:px-6 lg:px-8">
        <div className="rounded-lg bg-white p-6 shadow">
          <div className="grid gap-3 sm:grid-cols-2">
            <div><p className="text-xs text-gray-500">Code</p><p className="font-medium">{asset.asset_code}</p></div>
            <div><p className="text-xs text-gray-500">Name</p><p className="font-medium">{asset.name}</p></div>
            <div><p className="text-xs text-gray-500">Category</p><p className="font-medium">{asset.category || '-'}</p></div>
            <div><p className="text-xs text-gray-500">Status</p><p className="font-medium">{asset.status}</p></div>
            <div><p className="text-xs text-gray-500">Acquisition Date</p><p className="font-medium">{asset.acquisition_date}</p></div>
            <div><p className="text-xs text-gray-500">Acquisition Cost</p><p className="font-medium">{asset.acquisition_cost}</p></div>
            <div><p className="text-xs text-gray-500">Useful Life (Months)</p><p className="font-medium">{asset.useful_life_months}</p></div>
            <div><p className="text-xs text-gray-500">Residual Value</p><p className="font-medium">{asset.residual_value}</p></div>
            <div><p className="text-xs text-gray-500">Asset Account</p><p className="font-medium">{asset.asset_account?.code} - {asset.asset_account?.name}</p></div>
            <div><p className="text-xs text-gray-500">Accumulated Depreciation</p><p className="font-medium">{asset.accumulated_depreciation_account?.code} - {asset.accumulated_depreciation_account?.name}</p></div>
            <div><p className="text-xs text-gray-500">Depreciation Expense</p><p className="font-medium">{asset.depreciation_expense_account?.code} - {asset.depreciation_expense_account?.name}</p></div>
          </div>
          <div className="mt-4 flex gap-2">
            <Link href={route('fixed-assets.edit', asset.id)} className="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Edit</Link>
            <button onClick={destroy} className="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white">Delete</button>
            <Link href={route('fixed-assets.index')} className="rounded border px-4 py-2 text-sm">Back</Link>
          </div>
        </div>
      </div></div>
    </AuthenticatedLayout>
  )
}
