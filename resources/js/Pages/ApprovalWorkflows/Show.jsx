import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';

export default function Show({ auth, workflow }) {
  const evalForm = useForm({ entity_type: workflow.entity_type, amount: workflow.min_amount });

  const evaluate = (e) => {
    e.preventDefault();
    evalForm.post(route('approval-workflows.evaluate'), {
      preserveState: true,
      onSuccess: () => {},
    });
  };

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Approval Workflow Detail</h2>}>
      <Head title="Approval Workflow Detail" />
      <div className="py-12">
        <div className="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
          <div className="bg-white shadow-sm rounded-lg p-6 text-sm grid grid-cols-1 md:grid-cols-2 gap-3">
            <div><span className="font-semibold">Workflow Key:</span> {workflow.workflow_key}</div>
            <div><span className="font-semibold">Entity Type:</span> {workflow.entity_type}</div>
            <div><span className="font-semibold">Min Amount:</span> {Number(workflow.min_amount).toLocaleString('id-ID')}</div>
            <div><span className="font-semibold">Max Amount:</span> {workflow.max_amount ? Number(workflow.max_amount).toLocaleString('id-ID') : '∞'}</div>
            <div><span className="font-semibold">Required Level:</span> {workflow.required_level}</div>
            <div><span className="font-semibold">Status:</span> {workflow.is_active ? 'Active' : 'Inactive'}</div>
            <div className="md:col-span-2"><span className="font-semibold">Description:</span> {workflow.description || '-'}</div>
          </div>

          <div className="bg-white shadow-sm rounded-lg p-6">
            <h3 className="font-semibold mb-3">Quick Evaluate</h3>
            <form onSubmit={evaluate} className="flex flex-wrap gap-3 items-end">
              <div>
                <label className="block text-sm">Entity Type</label>
                <input className="border-gray-300 rounded-md" value={evalForm.data.entity_type} onChange={(e) => evalForm.setData('entity_type', e.target.value)} />
              </div>
              <div>
                <label className="block text-sm">Amount</label>
                <input type="number" step="0.01" className="border-gray-300 rounded-md" value={evalForm.data.amount} onChange={(e) => evalForm.setData('amount', e.target.value)} />
              </div>
              <button type="submit" className="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md">Evaluate (JSON)</button>
            </form>
            <p className="text-xs text-gray-500 mt-2">Gunakan endpoint evaluator via API call/inspector untuk hasil JSON.</p>
          </div>

          <div className="flex gap-3">
            <Link href={route('approval-workflows.edit', workflow.id)} className="inline-flex items-center px-4 py-2 bg-amber-500 text-white rounded-md">Edit</Link>
            <Link href={route('approval-workflows.index')} className="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md">Kembali</Link>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
