import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';

const emptyPayload = { header: { title: '', document_number: '' }, lines: [] };

export default function Create({ auth, documentTypes, documentType, templates, sources }) {
  const { data, setData, post, processing, errors } = useForm({
    document_type: documentType,
    document_id: sources[0]?.id || '',
    dot_matrix_template_id: templates[0]?.id || '',
    override_payload: emptyPayload,
    status: 'draft',
  });

  const changeType = (type) => {
    router.get(route('print-drafts.create'), { document_type: type }, { preserveState: false });
  };

  const setHeader = (key, value) => {
    setData('override_payload', {
      ...data.override_payload,
      header: { ...(data.override_payload.header || {}), [key]: value },
    });
  };

  const submit = (e) => {
    e.preventDefault();
    post(route('print-drafts.store'));
  };

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Buat Print Draft</h2>}>
      <Head title="Buat Print Draft" />
      <div className="py-6">
        <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
          <form onSubmit={submit} className="bg-white shadow sm:rounded-lg p-6 space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm text-gray-600">Document Type</label>
                <select className="w-full border rounded px-3 py-2" value={data.document_type} onChange={(e) => changeType(e.target.value)}>
                  {documentTypes.map((type) => <option key={type} value={type}>{type}</option>)}
                </select>
                {errors.document_type && <div className="text-red-600 text-sm">{errors.document_type}</div>}
              </div>
              <div>
                <label className="block text-sm text-gray-600">Source Document</label>
                <select className="w-full border rounded px-3 py-2" value={data.document_id} onChange={(e) => setData('document_id', e.target.value)}>
                  {sources.map((source) => <option key={source.id} value={source.id}>{source.number}</option>)}
                </select>
                {errors.document_id && <div className="text-red-600 text-sm">{errors.document_id}</div>}
              </div>
              <div>
                <label className="block text-sm text-gray-600">Template</label>
                <select className="w-full border rounded px-3 py-2" value={data.dot_matrix_template_id} onChange={(e) => setData('dot_matrix_template_id', e.target.value)}>
                  {templates.map((template) => <option key={template.id} value={template.id}>{template.name}</option>)}
                </select>
                {errors.dot_matrix_template_id && <div className="text-red-600 text-sm">{errors.dot_matrix_template_id}</div>}
              </div>
              <div>
                <label className="block text-sm text-gray-600">Status</label>
                <select className="w-full border rounded px-3 py-2" value={data.status} onChange={(e) => setData('status', e.target.value)}>
                  <option value="draft">draft</option>
                  <option value="ready">ready</option>
                </select>
              </div>
            </div>

            <div className="border rounded p-4 space-y-3">
              <h3 className="font-medium">Override Header</h3>
              <input className="w-full border rounded px-3 py-2" placeholder="Judul" value={data.override_payload.header?.title || ''} onChange={(e) => setHeader('title', e.target.value)} />
              <input className="w-full border rounded px-3 py-2" placeholder="Nomor dokumen custom" value={data.override_payload.header?.document_number || ''} onChange={(e) => setHeader('document_number', e.target.value)} />
              {errors.override_payload && <div className="text-red-600 text-sm">{errors.override_payload}</div>}
            </div>

            <div className="flex justify-end gap-2">
              <Link href={route('print-drafts.index')} className="px-4 py-2 border rounded">Batal</Link>
              <button disabled={processing} className="px-4 py-2 bg-blue-600 text-white rounded">Simpan</button>
            </div>
          </form>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
