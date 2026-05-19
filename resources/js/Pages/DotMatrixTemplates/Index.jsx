import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, templates, filters, documentTypes }) {
    const handleSearch = (event) => {
        event.preventDefault();
        const form = new FormData(event.target);
        router.get(route('dot-matrix-templates.index'), {
            search: form.get('search') || '',
            document_type: form.get('document_type') || '',
        }, { preserveState: true });
    };

    const handleDelete = (id) => {
        if (confirm('Hapus template ini?')) {
            router.delete(route('dot-matrix-templates.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Dot Matrix Templates" />
            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden rounded-lg bg-white shadow-sm">
                        <div className="p-6">
                            <div className="mb-6 flex items-center justify-between">
                                <h1 className="text-2xl font-semibold">Dot Matrix Templates</h1>
                                <Link href={route('dot-matrix-templates.create')} className="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                    Tambah Template
                                </Link>
                            </div>

                            <form onSubmit={handleSearch} className="mb-6 grid grid-cols-1 gap-3 md:grid-cols-4">
                                <input name="search" defaultValue={filters.search || ''} placeholder="Cari nama template" className="rounded border px-3 py-2 text-sm" />
                                <select name="document_type" defaultValue={filters.document_type || ''} className="rounded border px-3 py-2 text-sm">
                                    <option value="">Semua dokumen</option>
                                    {documentTypes.map((type) => (
                                        <option key={type} value={type}>{type}</option>
                                    ))}
                                </select>
                                <button type="submit" className="rounded bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                                    Filter
                                </button>
                            </form>

                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-slate-200">
                                    <thead className="bg-slate-50">
                                        <tr>
                                            <th className="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Nama</th>
                                            <th className="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Dokumen</th>
                                            <th className="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Paper</th>
                                            <th className="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Ukuran</th>
                                            <th className="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Status</th>
                                            <th className="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-100 bg-white">
                                        {templates.data.map((template) => (
                                            <tr key={template.id}>
                                                <td className="px-4 py-3 text-sm font-medium text-slate-900">{template.name}</td>
                                                <td className="px-4 py-3 text-sm text-slate-700">{template.document_type}</td>
                                                <td className="px-4 py-3 text-sm text-slate-700">{template.paper_size}</td>
                                                <td className="px-4 py-3 text-sm text-slate-700">{template.columns}x{template.rows}</td>
                                                <td className="px-4 py-3 text-sm text-slate-700">
                                                    <span className={`rounded px-2 py-1 text-xs ${template.is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600'}`}>
                                                        {template.is_active ? 'aktif' : 'nonaktif'}
                                                    </span>
                                                    {template.is_default && (
                                                        <span className="ml-2 rounded bg-blue-100 px-2 py-1 text-xs text-blue-700">default</span>
                                                    )}
                                                </td>
                                                <td className="px-4 py-3 text-sm text-slate-700 space-x-3">
                                                    <Link href={route('dot-matrix-templates.show', template.id)} className="text-blue-600 hover:text-blue-800">Lihat</Link>
                                                    <Link href={route('dot-matrix-templates.edit', template.id)} className="text-indigo-600 hover:text-indigo-800">Edit</Link>
                                                    <button onClick={() => handleDelete(template.id)} className="text-red-600 hover:text-red-800">Hapus</button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
