import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Show({ auth, draft, source }) {
  const destroyDraft = () => {
    if (confirm('Hapus draft print?')) {
      router.delete(route('print-drafts.destroy', draft.id));
    }
  };

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Detail Print Draft</h2>}>
      <Head title={draft.draft_number} />
      <div className="py-6">
        <div className="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
          <div className="bg-white shadow sm:rounded-lg p-6 space-y-2">
            <div><b>Draft:</b> {draft.draft_number}</div>
            <div><b>Type:</b> {draft.document_type}</div>
            <div><b>Status:</b> {draft.status}</div>
            <div><b>Template:</b> {draft.template?.name}</div>
            <div><b>Source:</b> {source?.number || '-'}</div>
          </div>

          <div className="bg-white shadow sm:rounded-lg p-6">
            <h3 className="font-medium mb-2">Override Payload</h3>
            <pre className="bg-gray-50 p-3 rounded text-xs overflow-auto">{JSON.stringify(draft.override_payload, null, 2)}</pre>
          </div>

          <div className="flex gap-2 justify-end">
            <Link href={route('print-drafts.index')} className="px-4 py-2 border rounded">Kembali</Link>
            <Link href={route('print-drafts.preview', draft.id)} className="px-4 py-2 bg-indigo-600 text-white rounded">Preview</Link>
            <Link href={route('print-drafts.edit', draft.id)} className="px-4 py-2 bg-amber-500 text-white rounded">Edit</Link>
            <button className="px-4 py-2 bg-red-600 text-white rounded" onClick={destroyDraft}>Hapus</button>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
