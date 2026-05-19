import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, drafts, filters, documentTypes }) {
  const updateFilter = (key, value) => {
    router.get(route('print-drafts.index'), { ...filters, [key]: value || undefined }, { preserveState: true, replace: true });
  };

  const destroyDraft = (id) => {
    if (confirm('Hapus draft print?')) {
      router.delete(route('print-drafts.destroy', id));
    }
  };

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Print Drafts</h2>}>
      <Head title="Print Drafts" />
      <div className="py-6">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
          <div className="bg-white p-4 shadow sm:rounded-lg flex flex-wrap gap-3 items-end">
            <div>
              <label className="block text-sm text-gray-600">Search</label>
              <input
                className="border rounded px-3 py-2"
                value={filters.search || ''}
                onChange={(e) => updateFilter('search', e.target.value)}
                placeholder="Nomor draft"
              />
            </div>
            <div>
              <label className="block text-sm text-gray-600">Document Type</label>
              <select
                className="border rounded px-3 py-2"
                value={filters.document_type || ''}
                onChange={(e) => updateFilter('document_type', e.target.value)}
              >
                <option value="">All</option>
                {documentTypes.map((type) => (
                  <option key={type} value={type}>{type}</option>
                ))}
              </select>
            </div>
            <Link href={route('print-drafts.create')} className="ml-auto bg-blue-600 text-white px-4 py-2 rounded">Tambah Draft</Link>
          </div>

          <div className="bg-white shadow sm:rounded-lg overflow-hidden">
            <table className="min-w-full divide-y divide-gray-200 text-sm">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-4 py-2 text-left">Draft</th>
                  <th className="px-4 py-2 text-left">Type</th>
                  <th className="px-4 py-2 text-left">Template</th>
                  <th className="px-4 py-2 text-left">Status</th>
                  <th className="px-4 py-2 text-left">Aksi</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {drafts.data.map((draft) => (
                  <tr key={draft.id}>
                    <td className="px-4 py-2">{draft.draft_number}</td>
                    <td className="px-4 py-2">{draft.document_type}</td>
                    <td className="px-4 py-2">{draft.template?.name}</td>
                    <td className="px-4 py-2">{draft.status}</td>
                    <td className="px-4 py-2 space-x-2">
                      <Link href={route('print-drafts.show', draft.id)} className="text-blue-600">Lihat</Link>
                      <Link href={route('print-drafts.edit', draft.id)} className="text-amber-600">Edit</Link>
                      <button className="text-red-600" onClick={() => destroyDraft(draft.id)}>Hapus</button>
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
