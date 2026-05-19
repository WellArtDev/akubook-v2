import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, workflows, filters }) {
  const updateFilter = (key, value) => {
    router.get(route('approval-workflows.index'), { ...filters, [key]: value }, { preserveState: true, replace: true });
  };

  const deactivate = (workflow) => {
    if (!confirm(`Deactivate ${workflow.workflow_key}?`)) return;
    router.delete(route('approval-workflows.destroy', workflow.id));
  };

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Approval Workflows</h2>}>
      <Head title="Approval Workflows" />
      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
          <div className="bg-white p-4 rounded-lg shadow-sm flex gap-3 items-center">
            <input className="border-gray-300 rounded-md" placeholder="Entity" value={filters.entity_type || ''} onChange={(e) => updateFilter('entity_type', e.target.value)} />
            <select className="border-gray-300 rounded-md" value={filters.is_active || ''} onChange={(e) => updateFilter('is_active', e.target.value)}>
              <option value="">Semua Status</option>
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
            <Link href={route('approval-workflows.create')} className="ml-auto inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md">Tambah Workflow</Link>
          </div>

          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200 text-sm">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-4 py-2 text-left">Key</th>
                    <th className="px-4 py-2 text-left">Entity</th>
                    <th className="px-4 py-2 text-left">Range</th>
                    <th className="px-4 py-2 text-left">Level</th>
                    <th className="px-4 py-2 text-left">Status</th>
                    <th className="px-4 py-2 text-left">Aksi</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {workflows.data.map((workflow) => (
                    <tr key={workflow.id}>
                      <td className="px-4 py-2">{workflow.workflow_key}</td>
                      <td className="px-4 py-2">{workflow.entity_type}</td>
                      <td className="px-4 py-2">{Number(workflow.min_amount).toLocaleString('id-ID')} - {workflow.max_amount ? Number(workflow.max_amount).toLocaleString('id-ID') : '∞'}</td>
                      <td className="px-4 py-2">{workflow.required_level}</td>
                      <td className="px-4 py-2">{workflow.is_active ? 'Active' : 'Inactive'}</td>
                      <td className="px-4 py-2 space-x-3">
                        <Link href={route('approval-workflows.show', workflow.id)} className="text-indigo-600">Detail</Link>
                        <Link href={route('approval-workflows.edit', workflow.id)} className="text-amber-600">Edit</Link>
                        <button type="button" className="text-red-600" onClick={() => deactivate(workflow)}>Deactivate</button>
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
