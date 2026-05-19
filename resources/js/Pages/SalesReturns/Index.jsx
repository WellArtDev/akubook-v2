import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, returns, filters = {} }) {
  const updateFilter = (key, value) => {
    router.get(route('sales-returns.index'), { ...filters, [key]: value || undefined }, { preserveState: true });
  };

  return (
    <AuthenticatedLayout user={auth.user}>
      <Head title="Sales Returns" />
      <div className="py-6">
        <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <div className="mb-6 flex items-center justify-between">
            <h1 className="text-2xl font-semibold text-gray-900">Sales Returns</h1>
            <Link href={route('sales-returns.create')} className="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Create Return</Link>
          </div>

          <div className="mb-4 grid gap-4 rounded bg-white p-4 shadow md:grid-cols-3">
            <input className="rounded border-gray-300" placeholder="Search RMA" defaultValue={filters.search || ''} onBlur={(e) => updateFilter('search', e.target.value)} />
            <select className="rounded border-gray-300" value={filters.status || ''} onChange={(e) => updateFilter('status', e.target.value)}>
              <option value="">All Status</option>
              {['pending', 'approved', 'received', 'completed', 'rejected'].map((status) => <option key={status} value={status}>{status}</option>)}
            </select>
          </div>

          <div className="overflow-hidden bg-white shadow sm:rounded-lg">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">RMA</th>
                  <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Date</th>
                  <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Customer</th>
                  <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                  <th className="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Total</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200 bg-white">
                {returns.data.map((item) => (
                  <tr key={item.id}>
                    <td className="px-6 py-4"><Link href={route('sales-returns.show', item.id)} className="text-blue-600 hover:underline">{item.rma_number}</Link></td>
                    <td className="px-6 py-4">{item.return_date}</td>
                    <td className="px-6 py-4">{item.customer?.name}</td>
                    <td className="px-6 py-4">{item.status}</td>
                    <td className="px-6 py-4 text-right">{Number(item.total_amount).toLocaleString()}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
