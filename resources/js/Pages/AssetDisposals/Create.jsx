import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Head, Link, useForm } from '@inertiajs/react'

export default function Create({ auth, assets, accounts }) {
  const { data, setData, post, processing, errors } = useForm({
    fixed_asset_id: assets[0]?.id || '',
    disposal_date: new Date().toISOString().slice(0, 10),
    proceeds_amount: 0,
    proceeds_account_id: '',
    gain_loss_account_id: accounts[0]?.id || '',
    notes: '',
  })

  const submit = (e) => {
    e.preventDefault()
    post(route('asset-disposals.store'))
  }

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Create Asset Disposal</h2>}>
      <Head title="Create Asset Disposal" />
      <div className="py-6">
        <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
          <form onSubmit={submit} className="space-y-4 rounded-lg bg-white p-6 shadow">
            <div>
              <label className="text-sm font-medium">Asset</label>
              <select value={data.fixed_asset_id} onChange={(e) => setData('fixed_asset_id', e.target.value)} className="mt-1 w-full rounded border-gray-300">
                {assets.map((asset) => <option key={asset.id} value={asset.id}>{asset.asset_code} - {asset.name}</option>)}
              </select>
              {errors.fixed_asset_id && <p className="text-sm text-red-600">{errors.fixed_asset_id}</p>}
            </div>
            <div>
              <label className="text-sm font-medium">Tanggal Disposal</label>
              <input type="date" value={data.disposal_date} onChange={(e) => setData('disposal_date', e.target.value)} className="mt-1 w-full rounded border-gray-300" />
            </div>
            <div>
              <label className="text-sm font-medium">Nilai Jual</label>
              <input type="number" step="0.01" value={data.proceeds_amount} onChange={(e) => setData('proceeds_amount', e.target.value)} className="mt-1 w-full rounded border-gray-300" />
            </div>
            <div>
              <label className="text-sm font-medium">Akun Proceeds</label>
              <select value={data.proceeds_account_id} onChange={(e) => setData('proceeds_account_id', e.target.value)} className="mt-1 w-full rounded border-gray-300">
                <option value="">Tidak ada</option>
                {accounts.map((account) => <option key={account.id} value={account.id}>{account.code} - {account.name}</option>)}
              </select>
              {errors.proceeds_account_id && <p className="text-sm text-red-600">{errors.proceeds_account_id}</p>}
            </div>
            <div>
              <label className="text-sm font-medium">Akun Gain/Loss</label>
              <select value={data.gain_loss_account_id} onChange={(e) => setData('gain_loss_account_id', e.target.value)} className="mt-1 w-full rounded border-gray-300">
                {accounts.map((account) => <option key={account.id} value={account.id}>{account.code} - {account.name}</option>)}
              </select>
            </div>
            <div>
              <label className="text-sm font-medium">Catatan</label>
              <textarea value={data.notes} onChange={(e) => setData('notes', e.target.value)} className="mt-1 w-full rounded border-gray-300" />
            </div>
            <div className="flex justify-end gap-2">
              <Link href={route('asset-disposals.index')} className="rounded border px-4 py-2 text-sm">Batal</Link>
              <button disabled={processing} className="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Simpan</button>
            </div>
          </form>
        </div>
      </div>
    </AuthenticatedLayout>
  )
}
