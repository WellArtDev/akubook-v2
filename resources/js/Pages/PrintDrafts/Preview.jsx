import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Preview({ auth, draft, source, previewLines }) {
  const markReady = () => {
    router.post(route('print-drafts.mark-ready', draft.id));
  };

  const recordPrint = () => {
    router.post(route('print-drafts.record-print', draft.id));
  };

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Print Preview</h2>}>
      <Head title={`Preview ${draft.draft_number}`} />
      <div className="py-6">
        <div className="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
          <div className="bg-white shadow sm:rounded-lg p-6 grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
            <div><b>Draft:</b> {draft.draft_number}</div>
            <div><b>Type:</b> {draft.document_type}</div>
            <div><b>Status:</b> {draft.status}</div>
            <div><b>Source:</b> {source?.number || '-'}</div>
          </div>

          <div className="bg-white shadow sm:rounded-lg p-6 overflow-auto">
            <pre className="text-xs leading-5 font-mono whitespace-pre min-w-[700px]">
              {previewLines.join('\n')}
            </pre>
          </div>

          <div className="flex gap-2 justify-end">
            <Link href={route('print-histories.index')} className="px-4 py-2 border rounded">Histori</Link>
            <Link href={route('print-drafts.show', draft.id)} className="px-4 py-2 border rounded">Kembali</Link>
            <button className="px-4 py-2 bg-emerald-600 text-white rounded" onClick={recordPrint}>Catat Print</button>
            {draft.status !== 'ready' && (
              <button className="px-4 py-2 bg-indigo-600 text-white rounded" onClick={markReady}>Siap Print</button>
            )}
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
