import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Show({ auth, policy, preview, entities }) {
  const dryRunForm = useForm({ policy_id: policy.id, mode: 'dry-run' });
  const executeForm = useForm({ policy_id: policy.id, mode: 'execute' });

  const run = (form) => {
    form.post(route('data-retention-executions.store'));
  };

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Data Retention Policy Detail</h2>}>
      <Head title="Data Retention Policy Detail" />
      <div className="py-12">
        <div className="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
          <div className="bg-white shadow-sm rounded-lg p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div><span className="font-semibold">Policy Key:</span> {policy.policy_key}</div>
            <div><span className="font-semibold">Entity:</span> {entities[policy.entity_type]?.label || policy.entity_type}</div>
            <div><span className="font-semibold">Retention Days:</span> {policy.retention_days}</div>
            <div><span className="font-semibold">Action:</span> {policy.action}</div>
            <div><span className="font-semibold">Status:</span> {policy.is_active ? 'Active' : 'Inactive'}</div>
            <div><span className="font-semibold">Updated By:</span> {policy.updater?.name || '-'}</div>
            <div className="md:col-span-2"><span className="font-semibold">Description:</span> {policy.description || '-'}</div>
          </div>

          <div className="bg-white shadow-sm rounded-lg p-6">
            <h3 className="font-semibold mb-2">Preview Kandidat Retensi</h3>
            <div className="text-sm text-gray-700">Cutoff Date: <span className="font-medium">{preview.cutoff_date || '-'}</span></div>
            <div className="text-sm text-gray-700">Candidate Count: <span className="font-medium">{preview.candidate_count}</span></div>
            <p className="mt-2 text-xs text-gray-500">Dry-run tidak mengubah data. Execute akan menjalankan action policy.</p>
          </div>

          <div className="bg-white shadow-sm rounded-lg p-6">
            <h3 className="font-semibold mb-3">Run Retention Execution</h3>
            <div className="flex flex-wrap gap-3">
              <button type="button" className="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md" onClick={() => run(dryRunForm)} disabled={dryRunForm.processing}>Run Dry-Run</button>
              <button type="button" className="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md" onClick={() => run(executeForm)} disabled={executeForm.processing || policy.action !== 'delete'}>Run Execute</button>
              <Link href={route('data-retention-executions.index')} className="inline-flex items-center px-4 py-2 bg-slate-700 text-white rounded-md">Lihat Execution Batches</Link>
            </div>
            {policy.action !== 'delete' && <p className="mt-2 text-xs text-gray-500">MVP execute hanya mendukung action delete.</p>}
          </div>

          <div className="flex gap-3">
            <Link href={route('data-retention-policies.edit', policy.id)} className="inline-flex items-center px-4 py-2 bg-amber-500 text-white rounded-md">Edit</Link>
            <Link href={route('data-retention-policies.index')} className="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md">Kembali</Link>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
