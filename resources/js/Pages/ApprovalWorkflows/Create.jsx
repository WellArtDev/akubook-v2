import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export function WorkflowForm({ auth, mode = 'create', workflow = null }) {
  const form = useForm({
    workflow_key: workflow?.workflow_key || '',
    entity_type: workflow?.entity_type || 'purchase_order',
    min_amount: workflow?.min_amount ?? 0,
    max_amount: workflow?.max_amount ?? '',
    required_level: workflow?.required_level ?? 1,
    is_active: workflow?.is_active ?? true,
    description: workflow?.description || '',
  });

  const submit = (e) => {
    e.preventDefault();
    if (mode === 'create') form.post(route('approval-workflows.store'));
    else form.put(route('approval-workflows.update', workflow.id));
  };

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">{mode === 'create' ? 'Create' : 'Edit'} Approval Workflow</h2>}>
      <Head title={mode === 'create' ? 'Create Approval Workflow' : 'Edit Approval Workflow'} />
      <div className="py-12">
        <div className="max-w-3xl mx-auto sm:px-6 lg:px-8">
          <form onSubmit={submit} className="bg-white shadow-sm rounded-lg p-6 space-y-4">
            <input className="w-full border-gray-300 rounded-md" placeholder="Workflow Key" value={form.data.workflow_key} onChange={(e) => form.setData('workflow_key', e.target.value)} />
            <input className="w-full border-gray-300 rounded-md" placeholder="Entity Type" value={form.data.entity_type} onChange={(e) => form.setData('entity_type', e.target.value)} />
            <div className="grid grid-cols-2 gap-3">
              <input type="number" step="0.01" className="w-full border-gray-300 rounded-md" placeholder="Min Amount" value={form.data.min_amount} onChange={(e) => form.setData('min_amount', e.target.value)} />
              <input type="number" step="0.01" className="w-full border-gray-300 rounded-md" placeholder="Max Amount (opsional)" value={form.data.max_amount} onChange={(e) => form.setData('max_amount', e.target.value)} />
            </div>
            <input type="number" min="1" className="w-full border-gray-300 rounded-md" placeholder="Required Level" value={form.data.required_level} onChange={(e) => form.setData('required_level', e.target.value)} />
            <textarea className="w-full border-gray-300 rounded-md" rows={3} placeholder="Description" value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} />
            <label className="inline-flex items-center gap-2">
              <input type="checkbox" checked={form.data.is_active} onChange={(e) => form.setData('is_active', e.target.checked)} />
              <span>Active</span>
            </label>
            <button type="submit" className="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md" disabled={form.processing}>Simpan</button>
            {(form.errors.workflow_key || form.errors.entity_type || form.errors.min_amount || form.errors.max_amount || form.errors.required_level) && (
              <div className="text-sm text-red-600 space-y-1">
                {Object.values(form.errors).map((error) => <p key={error}>{error}</p>)}
              </div>
            )}
          </form>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}

export default function Create(props) {
  return <WorkflowForm {...props} mode="create" />;
}
