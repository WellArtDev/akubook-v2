import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function Index({ auth, executions }) {
  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Retention Executions</h2>}>
      <Head title="Retention Executions" />
      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200 text-sm">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-4 py-2 text-left">ID</th>
                    <th className="px-4 py-2 text-left">Policy</th>
                    <th className="px-4 py-2 text-left">Mode</th>
                    <th className="px-4 py-2 text-left">Entity</th>
                    <th className="px-4 py-2 text-left">Action</th>
                    <th className="px-4 py-2 text-left">Candidate</th>
                    <th className="px-4 py-2 text-left">Processed</th>
                    <th className="px-4 py-2 text-left">Status</th>
                    <th className="px-4 py-2 text-left">Aksi</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {executions.data.map((execution) => (
                    <tr key={execution.id}>
                      <td className="px-4 py-2">{execution.id}</td>
                      <td className="px-4 py-2">{execution.policy?.policy_key || '-'}</td>
                      <td className="px-4 py-2">{execution.mode}</td>
                      <td className="px-4 py-2">{execution.entity_type}</td>
                      <td className="px-4 py-2">{execution.action}</td>
                      <td className="px-4 py-2">{execution.candidate_count}</td>
                      <td className="px-4 py-2">{execution.processed_count}</td>
                      <td className="px-4 py-2">{execution.status}</td>
                      <td className="px-4 py-2">
                        <Link href={route('data-retention-executions.show', execution.id)} className="text-indigo-600">Detail</Link>
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
