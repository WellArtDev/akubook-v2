import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export function PolicyForm({ auth, entities, actions, mode = 'create', policy = null }) {
  const form = useForm({
    policy_key: policy?.policy_key || '',
    entity_type: policy?.entity_type || Object.keys(entities)[0] || 'audit_log',
    retention_days: policy?.retention_days ?? 365,
    action: policy?.action || actions[0] || 'archive',
    is_active: policy?.is_active ?? true,
    description: policy?.description || '',
  });

  const submit = (e) => {
    e.preventDefault();
    if (mode === 'create') form.post(route('data-retention-policies.store'));
    else form.put(route('data-retention-policies.update', policy.id));
  };

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">{mode === 'create' ? 'Create' : 'Edit'} Data Retention Policy</h2>}>
      <Head title={mode === 'create' ? 'Create Data Retention Policy' : 'Edit Data Retention Policy'} />
      <div className="py-12">
        <div className="max-w-3xl mx-auto sm:px-6 lg:px-8">
          <form onSubmit={submit} className="bg-white shadow-sm rounded-lg p-6 space-y-4">
            <div>
              <label className="block text-sm font-medium">Policy Key</label>
              <input className="mt-1 w-full border-gray-300 rounded-md" value={form.data.policy_key} onChange={(e) => form.setData('policy_key', e.target.value)} />
              {form.errors.policy_key && <p className="mt-1 text-sm text-red-600">{form.errors.policy_key}</p>}
            </div>
            <div>
              <label className="block text-sm font-medium">Entity</label>
              <select className="mt-1 w-full border-gray-300 rounded-md" value={form.data.entity_type} onChange={(e) => form.setData('entity_type', e.target.value)}>
                {Object.entries(entities).map(([key, meta]) => <option key={key} value={key}>{meta.label}</option>)}
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium">Retention Days</label>
              <input type="number" min="1" className="mt-1 w-full border-gray-300 rounded-md" value={form.data.retention_days} onChange={(e) => form.setData('retention_days', e.target.value)} />
              {form.errors.retention_days && <p className="mt-1 text-sm text-red-600">{form.errors.retention_days}</p>}
            </div>
            <div>
              <label className="block text-sm font-medium">Action</label>
              <select className="mt-1 w-full border-gray-300 rounded-md" value={form.data.action} onChange={(e) => form.setData('action', e.target.value)}>
                {actions.map((a) => <option key={a} value={a}>{a}</option>)}
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium">Description</label>
              <textarea className="mt-1 w-full border-gray-300 rounded-md" value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} rows={3} />
            </div>
            <label className="inline-flex items-center gap-2">
              <input type="checkbox" checked={form.data.is_active} onChange={(e) => form.setData('is_active', e.target.checked)} />
              <span>Active</span>
            </label>
            <div>
              <button type="submit" className="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md" disabled={form.processing}>Simpan</button>
            </div>
          </form>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}

export default function Create(props) {
  return <PolicyForm {...props} mode="create" />;
}
