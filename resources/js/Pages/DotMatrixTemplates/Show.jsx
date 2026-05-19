import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Show({ auth, template, sampleData }) {
    const handleDelete = () => {
        if (confirm('Hapus template ini?')) {
            router.delete(route('dot-matrix-templates.destroy', template.id));
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`Template ${template.name}`} />
            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden rounded-lg bg-white shadow-sm">
                        <div className="p-6">
                            <div className="mb-6 flex items-center justify-between">
                                <h1 className="text-2xl font-semibold">Template Detail</h1>
                                <div className="space-x-2">
                                    <Link href={route('dot-matrix-templates.edit', template.id)} className="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Edit</Link>
                                    <Link href={route('dot-matrix-templates.index')} className="rounded bg-slate-600 px-4 py-2 text-sm font-semibold text-white">Kembali</Link>
                                </div>
                            </div>

                            <div className="mb-6 grid grid-cols-1 gap-3 md:grid-cols-2">
                                <div><span className="text-sm text-slate-500">Nama</span><p className="text-sm font-medium text-slate-900">{template.name}</p></div>
                                <div><span className="text-sm text-slate-500">Dokumen</span><p className="text-sm font-medium text-slate-900">{template.document_type}</p></div>
                                <div><span className="text-sm text-slate-500">Paper</span><p className="text-sm font-medium text-slate-900">{template.paper_size}</p></div>
                                <div><span className="text-sm text-slate-500">Grid</span><p className="text-sm font-medium text-slate-900">{template.columns}x{template.rows}</p></div>
                            </div>

                            <div className="mb-6">
                                <h2 className="mb-2 text-sm font-semibold text-slate-700">Margins</h2>
                                <pre className="rounded bg-slate-50 p-3 text-xs text-slate-700">{JSON.stringify(template.margins, null, 2)}</pre>
                            </div>

                            <div className="mb-6">
                                <h2 className="mb-2 text-sm font-semibold text-slate-700">Field Map</h2>
                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-slate-200">
                                        <thead className="bg-slate-50">
                                            <tr>
                                                <th className="px-3 py-2 text-left text-xs uppercase text-slate-500">Field</th>
                                                <th className="px-3 py-2 text-left text-xs uppercase text-slate-500">X</th>
                                                <th className="px-3 py-2 text-left text-xs uppercase text-slate-500">Y</th>
                                                <th className="px-3 py-2 text-left text-xs uppercase text-slate-500">Width</th>
                                                <th className="px-3 py-2 text-left text-xs uppercase text-slate-500">Sample Value</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-slate-100">
                                            {(template.field_map || []).map((field, index) => (
                                                <tr key={`${field.field}-${index}`}>
                                                    <td className="px-3 py-2 text-sm">{field.field}</td>
                                                    <td className="px-3 py-2 text-sm">{field.x}</td>
                                                    <td className="px-3 py-2 text-sm">{field.y}</td>
                                                    <td className="px-3 py-2 text-sm">{field.width || '-'}</td>
                                                    <td className="px-3 py-2 text-sm text-slate-600">{sampleData[field.field] || '-'}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <button onClick={handleDelete} className="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                                Hapus Template
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
