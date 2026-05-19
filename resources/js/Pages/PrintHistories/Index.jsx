import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, histories, filters, documentTypes }) {
  const applyFilter = (key, value) => {
    router.get(route('print-histories.index'), { ...filters, [key]: value }, { preserveState: true, replace: true });
  };

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Print History</h2>}>
      <Head title="Print History" />
      <div className="py-6">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
          <div className="bg-white shadow sm:rounded-lg p-4 grid grid-cols-1 md:grid-cols-4 gap-3">
            <select className="border rounded px-3 py-2" value={filters.document_type || ''} onChange={(e) => applyFilter('document_type', e.target.value)}>
              <option value="">Semua Dokumen</option>
              {documentTypes.map((type) => (
                <option key={type} value={type}>{type}</option>
              ))}
            </select>
            <input type="date" className="border rounded px-3 py-2" value={filters.date_from || ''} onChange={(e) => applyFilter('date_from', e.target.value)} />
            <input type="date" className="border rounded px-3 py-2" value={filters.date_to || ''} onChange={(e) => applyFilter('date_to', e.target.value)} />
            <Link href={route('print-drafts.index')} className="px-4 py-2 border rounded text-center">Ke Draft</Link>
          </div>

          <div className="bg-white shadow sm:rounded-lg overflow-hidden">
            <table className="min-w-full text-sm">
              <thead className="bg-gray-100">
                <tr>
                  <th className="text-left px-4 py-2">Waktu</th>
                  <th className="text-left px-4 py-2">Draft</th>
                  <th className="text-left px-4 py-2">Dokumen</th>
                  <th className="text-left px-4 py-2">Template</th>
                  <th className="text-left px-4 py-2">User</th>
                  <th className="text-left px-4 py-2">Aksi</th>
                </tr>
              </thead>
              <tbody>
                {histories.data.map((row) => (
                  <tr key={row.id} className="border-t">
                    <td className="px-4 py-2">{row.printed_at}</td>
                    <td className="px-4 py-2">{row.draft?.draft_number}</td>
                    <td className="px-4 py-2">{row.document_type} #{row.document_id}</td>
                    <td className="px-4 py-2">{row.template?.name}</td>
                    <td className="px-4 py-2">{row.printer?.name}</td>
                    <td className="px-4 py-2">
                      <Link href={route('print-histories.show', row.id)} className="text-indigo-600">Detail</Link>
                    </td>
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
