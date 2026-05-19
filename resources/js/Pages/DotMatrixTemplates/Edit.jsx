import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Edit({ auth, template, documentTypes }) {
    const { data, setData, put, processing, errors } = useForm({
        name: template.name,
        document_type: template.document_type,
        paper_size: template.paper_size,
        columns: template.columns,
        rows: template.rows,
        margins: template.margins || { top: 1, left: 2, right: 2, bottom: 1 },
        field_map: template.field_map || [],
        is_default: template.is_default,
        is_active: template.is_active,
    });

    const submit = (event) => {
        event.preventDefault();
        put(route('dot-matrix-templates.update', template.id));
    };

    const updateMap = (index, key, value) => {
        const next = [...data.field_map];
        next[index] = { ...next[index], [key]: key === 'x' || key === 'y' || key === 'width' ? Number(value) : value };
        setData('field_map', next);
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`Edit ${template.name}`} />
            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden rounded-lg bg-white shadow-sm">
                        <div className="p-6">
                            <div className="mb-6 flex items-center justify-between">
                                <h1 className="text-2xl font-semibold">Edit Dot Matrix Template</h1>
                                <Link href={route('dot-matrix-templates.show', template.id)} className="rounded bg-slate-600 px-4 py-2 text-sm font-semibold text-white">Kembali</Link>
                            </div>

                            <form onSubmit={submit} className="space-y-6">
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <label className="mb-1 block text-sm font-medium text-slate-700">Nama</label>
                                        <input value={data.name} onChange={(e) => setData('name', e.target.value)} className="w-full rounded border px-3 py-2 text-sm" />
                                        {errors.name && <p className="mt-1 text-xs text-red-600">{errors.name}</p>}
                                    </div>
                                    <div>
                                        <label className="mb-1 block text-sm font-medium text-slate-700">Jenis Dokumen</label>
                                        <select value={data.document_type} onChange={(e) => setData('document_type', e.target.value)} className="w-full rounded border px-3 py-2 text-sm">
                                            {documentTypes.map((type) => <option key={type} value={type}>{type}</option>)}
                                        </select>
                                    </div>
                                    <div>
                                        <label className="mb-1 block text-sm font-medium text-slate-700">Paper Size</label>
                                        <input value={data.paper_size} onChange={(e) => setData('paper_size', e.target.value)} className="w-full rounded border px-3 py-2 text-sm" />
                                    </div>
                                    <div className="grid grid-cols-2 gap-2">
                                        <div>
                                            <label className="mb-1 block text-sm font-medium text-slate-700">Columns</label>
                                            <input type="number" value={data.columns} onChange={(e) => setData('columns', Number(e.target.value))} className="w-full rounded border px-3 py-2 text-sm" />
                                        </div>
                                        <div>
                                            <label className="mb-1 block text-sm font-medium text-slate-700">Rows</label>
                                            <input type="number" value={data.rows} onChange={(e) => setData('rows', Number(e.target.value))} className="w-full rounded border px-3 py-2 text-sm" />
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h2 className="mb-2 text-sm font-semibold text-slate-700">Field Map</h2>
                                    <div className="space-y-2">
                                        {data.field_map.map((field, index) => (
                                            <div key={`${field.field}-${index}`} className="grid grid-cols-1 gap-2 rounded border p-3 md:grid-cols-5">
                                                <input value={field.field || ''} onChange={(e) => updateMap(index, 'field', e.target.value)} placeholder="field" className="rounded border px-2 py-1 text-sm" />
                                                <input type="number" value={field.x ?? 0} onChange={(e) => updateMap(index, 'x', e.target.value)} placeholder="x" className="rounded border px-2 py-1 text-sm" />
                                                <input type="number" value={field.y ?? 0} onChange={(e) => updateMap(index, 'y', e.target.value)} placeholder="y" className="rounded border px-2 py-1 text-sm" />
                                                <input type="number" value={field.width ?? ''} onChange={(e) => updateMap(index, 'width', e.target.value)} placeholder="width" className="rounded border px-2 py-1 text-sm" />
                                                <input value={field.label || ''} onChange={(e) => updateMap(index, 'label', e.target.value)} placeholder="label" className="rounded border px-2 py-1 text-sm" />
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                <div className="flex items-center gap-4">
                                    <label className="inline-flex items-center gap-2 text-sm">
                                        <input type="checkbox" checked={data.is_default} onChange={(e) => setData('is_default', e.target.checked)} />
                                        Default template
                                    </label>
                                    <label className="inline-flex items-center gap-2 text-sm">
                                        <input type="checkbox" checked={data.is_active} onChange={(e) => setData('is_active', e.target.checked)} />
                                        Aktif
                                    </label>
                                </div>

                                <div>
                                    <button disabled={processing} className="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
                                        Update Template
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
