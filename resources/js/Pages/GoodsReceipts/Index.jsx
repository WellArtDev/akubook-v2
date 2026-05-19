import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, goodsReceipts, filters = {} }) {
  const updateFilter = (key, value) => {
    router.get(route('goods-receipts.index'), { ...filters, [key]: value || undefined }, { preserveState: true, replace: true });
  };

  return (
    <AuthenticatedLayout user={auth.user}>
      <Head title="Goods Receipts" />
      <div className="py-6">
        <div className="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-4">
          <div className="bg-white shadow-sm sm:rounded-lg p-6 flex items-center justify-between">
            <h1 className="text-xl font-semibold">Goods Receipts</h1>
            <Link href={route('goods-receipts.create')} className="px-4 py-2 bg-indigo-600 text-white rounded-md">Buat GR</Link>
          </div>

          <div className="bg-white shadow-sm sm:rounded-lg p-4 grid grid-cols-1 md:grid-cols-4 gap-3">
            <input className="rounded border-gray-300" placeholder="Cari GR/PO" defaultValue={filters.search || ''} onBlur={(e) => updateFilter('search', e.target.value)} />
            <select className="rounded border-gray-300" value={filters.status || ''} onChange={(e) => updateFilter('status', e.target.value)}>
              <option value="">Semua Status</option>
              <option value="draft">Draft</option>
              <option value="received">Received</option>
              <option value="cancelled">Cancelled</option>
            </select>
            <input type="date" className="rounded border-gray-300" value={filters.date_from || ''} onChange={(e) => updateFilter('date_from', e.target.value)} />
            <input type="date" className="rounded border-gray-300" value={filters.date_to || ''} onChange={(e) => updateFilter('date_to', e.target.value)} />
          </div>

          <div className="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
            <table className="min-w-full text-sm">
              <thead className="bg-gray-50 text-left text-gray-600">
                <tr>
                  <th className="px-4 py-3">GR Number</th>
                  <th className="px-4 py-3">Date</th>
                  <th className="px-4 py-3">PO</th>
                  <th className="px-4 py-3">Supplier</th>
                  <th className="px-4 py-3">Status</th>
                </tr>
              </thead>
              <tbody>
                {goodsReceipts.data.map((gr) => (
                  <tr key={gr.id} className="border-t">
                    <td className="px-4 py-3"><Link className="text-indigo-600" href={route('goods-receipts.show', gr.id)}>{gr.gr_number}</Link></td>
                    <td className="px-4 py-3">{gr.gr_date}</td>
                    <td className="px-4 py-3">{gr.purchase_order?.po_number}</td>
                    <td className="px-4 py-3">{gr.supplier?.name}</td>
                    <td className="px-4 py-3">{gr.status}</td>
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
