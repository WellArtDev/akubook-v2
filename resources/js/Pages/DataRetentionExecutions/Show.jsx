import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function Show({ auth, execution }) {
  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Retention Execution Detail</h2>}>
      <Head title="Retention Execution Detail" />
      <div className="py-12">
        <div className="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
          <div className="bg-white shadow-sm rounded-lg p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div><span className="font-semibold">Execution ID:</span> {execution.id}</div>
            <div><span className="font-semibold">Policy:</span> {execution.policy?.policy_key || '-'}</div>
            <div><span className="font-semibold">Mode:</span> {execution.mode}</div>
            <div><span className="font-semibold">Entity:</span> {execution.entity_type}</div>
            <div><span className="font-semibold">Action:</span> {execution.action}</div>
            <div><span className="font-semibold">Status:</span> {execution.status}</div>
            <div><span className="font-semibold">Cutoff Date:</span> {execution.cutoff_date}</div>
            <div><span className="font-semibold">Executed By:</span> {execution.creator?.name || '-'}</div>
            <div><span className="font-semibold">Candidate Count:</span> {execution.candidate_count}</div>
            <div><span className="font-semibold">Processed Count:</span> {execution.processed_count}</div>
          </div>
          <div className="bg-white shadow-sm rounded-lg p-6 text-sm">
            <h3 className="font-semibold mb-2">Summary</h3>
            <pre className="bg-gray-50 p-3 rounded overflow-auto">{JSON.stringify(execution.summary || {}, null, 2)}</pre>
          </div>
          <div className="flex gap-3">
            <Link href={route('data-retention-executions.index')} className="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md">Kembali</Link>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
