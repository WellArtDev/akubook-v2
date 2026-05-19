import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Head, useForm } from '@inertiajs/react'

export default function Create({ auth, availableCount }) {
  const { data, setData, post, processing, errors } = useForm({
    period_start: new Date().toISOString().slice(0, 10),
    period_end: new Date().toISOString().slice(0, 10),
  })

  const submit = (e) => {
    e.preventDefault()
    post(route('e-faktur-exports.store'))
  }

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Generate E-Faktur Export</h2>}>
      <Head title="Generate E-Faktur Export" />
      <div className="py-6">
        <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
          <form onSubmit={submit} className="space-y-4 rounded-lg bg-white p-6 shadow">
            <p className="text-sm text-gray-600">Issued faktur available: {availableCount}</p>
            <div className="grid gap-4 sm:grid-cols-2">
              <div>
                <label className="mb-1 block text-sm font-medium">Period Start</label>
                <input type="date" className="w-full rounded border-gray-300" value={data.period_start} onChange={(e) => setData('period_start', e.target.value)} />
                {errors.period_start && <p className="mt-1 text-xs text-red-600">{errors.period_start}</p>}
              </div>
              <div>
                <label className="mb-1 block text-sm font-medium">Period End</label>
                <input type="date" className="w-full rounded border-gray-300" value={data.period_end} onChange={(e) => setData('period_end', e.target.value)} />
                {errors.period_end && <p className="mt-1 text-xs text-red-600">{errors.period_end}</p>}
              </div>
            </div>
            <button disabled={processing} className="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Generate</button>
          </form>
        </div>
      </div>
    </AuthenticatedLayout>
  )
}
