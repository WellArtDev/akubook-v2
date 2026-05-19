import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, policies, filters, entities, actions }) {
  const updateFilter = (key, value) => {
    router.get(route('data-retention-policies.index'), { ...filters, [key]: value }, { preserveState: true, replace: true });
  };

  const deactivate = (policy) => {
    if (!confirm(`Deactivate policy ${policy.policy_key}?`)) return;
    router.delete(route('data-retention-policies.destroy', policy.id));
  };

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Data Retention Policies</h2>}>
      <Head title="Data Retention Policies" />
      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 flex flex-wrap gap-3 items-center">
            <select className="border-gray-300 rounded-md" value={filters.entity_type || ''} onChange={(e) => updateFilter('entity_type', e.target.value)}>
              <option value="">Semua Entity</option>
              {Object.entries(entities).map(([key, meta]) => <option key={key} value={key}>{meta.label}</option>)}
            </select>
            <select className="border-gray-300 rounded-md" value={filters.action || ''} onChange={(e) => updateFilter('action', e.target.value)}>
              <option value="">Semua Action</option>
              {actions.map((a) => <option key={a} value={a}>{a}</option>)}
            </select>
            <select className="border-gray-300 rounded-md" value={filters.is_active || ''} onChange={(e) => updateFilter('is_active', e.target.value)}>
              <option value="">Semua Status</option>
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
            <Link href={route('data-retention-policies.create')} className="ml-auto inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md">Tambah Policy</Link>
          </div>

          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200 text-sm">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-4 py-2 text-left">Policy Key</th>
                    <th className="px-4 py-2 text-left">Entity</th>
                    <th className="px-4 py-2 text-left">Retention (days)</th>
                    <th className="px-4 py-2 text-left">Action</th>
                    <th className="px-4 py-2 text-left">Status</th>
                    <th className="px-4 py-2 text-left">Aksi</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {policies.data.map((policy) => (
                    <tr key={policy.id}>
                      <td className="px-4 py-2">{policy.policy_key}</td>
                      <td className="px-4 py-2">{entities[policy.entity_type]?.label || policy.entity_type}</td>
                      <td className="px-4 py-2">{policy.retention_days}</td>
                      <td className="px-4 py-2">{policy.action}</td>
                      <td className="px-4 py-2">{policy.is_active ? 'Active' : 'Inactive'}</td>
                      <td className="px-4 py-2 space-x-3">
                        <Link href={route('data-retention-policies.show', policy.id)} className="text-indigo-600">Detail</Link>
                        <Link href={route('data-retention-policies.edit', policy.id)} className="text-amber-600">Edit</Link>
                        <button type="button" className="text-red-600" onClick={() => deactivate(policy)}>Deactivate</button>
                      </td>
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
