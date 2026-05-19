import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Head, Link, useForm } from '@inertiajs/react'

export default function Create({ auth, accounts, statuses, formOverride = null }) {
  const createForm = useForm({
    asset_code: '',
    name: '',
    category: '',
    acquisition_date: new Date().toISOString().slice(0, 10),
    acquisition_cost: '0',
    useful_life_months: '60',
    residual_value: '0',
    status: statuses[0] || 'active',
    asset_account_id: '',
    accumulated_depreciation_account_id: '',
    depreciation_expense_account_id: '',
    notes: '',
  })

  const { data, setData, processing, errors } = formOverride || createForm

  const submit = (e) => {
    e.preventDefault()
    if (formOverride) {
      formOverride.submit(e)
      return
    }
    createForm.post(route('fixed-assets.store'))
  }

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Create Fixed Asset</h2>}>
      <Head title="Create Fixed Asset" />
      <div className="py-6"><div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
        <form onSubmit={submit} className="space-y-4 rounded-lg bg-white p-6 shadow">
          <div className="grid gap-4 sm:grid-cols-2">
            <input className="rounded border-gray-300" placeholder="Asset code (auto if empty)" value={data.asset_code} onChange={(e) => setData('asset_code', e.target.value)} />
            <input className="rounded border-gray-300" placeholder="Name" value={data.name} onChange={(e) => setData('name', e.target.value)} />
            <input className="rounded border-gray-300" placeholder="Category" value={data.category} onChange={(e) => setData('category', e.target.value)} />
            <input type="date" className="rounded border-gray-300" value={data.acquisition_date} onChange={(e) => setData('acquisition_date', e.target.value)} />
            <input type="number" step="0.01" className="rounded border-gray-300" placeholder="Acquisition cost" value={data.acquisition_cost} onChange={(e) => setData('acquisition_cost', e.target.value)} />
            <input type="number" className="rounded border-gray-300" placeholder="Useful life months" value={data.useful_life_months} onChange={(e) => setData('useful_life_months', e.target.value)} />
            <input type="number" step="0.01" className="rounded border-gray-300" placeholder="Residual value" value={data.residual_value} onChange={(e) => setData('residual_value', e.target.value)} />
            <select className="rounded border-gray-300" value={data.status} onChange={(e) => setData('status', e.target.value)}>{statuses.map((s) => <option key={s} value={s}>{s}</option>)}</select>
            <select className="rounded border-gray-300" value={data.asset_account_id} onChange={(e) => setData('asset_account_id', e.target.value)}><option value="">Asset Account</option>{accounts.map((a) => <option key={a.id} value={a.id}>{a.code} - {a.name}</option>)}</select>
            <select className="rounded border-gray-300" value={data.accumulated_depreciation_account_id} onChange={(e) => setData('accumulated_depreciation_account_id', e.target.value)}><option value="">Accumulated Depreciation Account</option>{accounts.map((a) => <option key={a.id} value={a.id}>{a.code} - {a.name}</option>)}</select>
            <select className="rounded border-gray-300 sm:col-span-2" value={data.depreciation_expense_account_id} onChange={(e) => setData('depreciation_expense_account_id', e.target.value)}><option value="">Depreciation Expense Account</option>{accounts.map((a) => <option key={a.id} value={a.id}>{a.code} - {a.name}</option>)}</select>
            <textarea className="rounded border-gray-300 sm:col-span-2" placeholder="Notes" value={data.notes} onChange={(e) => setData('notes', e.target.value)} />
          </div>
          {Object.keys(errors).length > 0 && <div className="text-sm text-red-600">{Object.values(errors)[0]}</div>}
          <div className="flex gap-2"><button disabled={processing} className="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Save</button><Link href={route('fixed-assets.index')} className="rounded border px-4 py-2 text-sm">Back</Link></div>
        </form>
      </div></div>
    </AuthenticatedLayout>
  )
}
