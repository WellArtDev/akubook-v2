import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function Show({ auth, history }) {
  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Detail Print History</h2>}>
      <Head title={`Print ${history.id}`} />
      <div className="py-6">
        <div className="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
          <div className="bg-white shadow sm:rounded-lg p-6 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <div><b>Printed At:</b> {history.printed_at}</div>
            <div><b>Draft:</b> {history.draft?.draft_number}</div>
            <div><b>Document:</b> {history.document_type} #{history.document_id}</div>
            <div><b>Template:</b> {history.template?.name}</div>
            <div><b>User:</b> {history.printer?.name}</div>
            <div><b>Draft Status:</b> {history.output_metadata?.draft_status || '-'}</div>
          </div>

          <div className="bg-white shadow sm:rounded-lg p-6">
            <h3 className="font-medium mb-2">Output Metadata</h3>
            <pre className="text-xs bg-gray-50 p-3 rounded overflow-auto">{JSON.stringify(history.output_metadata, null, 2)}</pre>
          </div>

          <div className="flex justify-end">
            <Link href={route('print-histories.index')} className="px-4 py-2 border rounded">Kembali</Link>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
