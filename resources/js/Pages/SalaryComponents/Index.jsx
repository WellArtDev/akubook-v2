import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, components, filters, componentTypes }) {
    const updateFilter = (key, value) => {
        router.get(route('salary-components.index'), { ...filters, [key]: value }, { preserveState: true, replace: true });
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Salary Components</h2>}>
            <Head title="Salary Components" />
            <div className="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div className="flex justify-between items-center">
                    <div className="flex gap-2">
                        <input className="border rounded px-3 py-2" placeholder="Search code/name" value={filters.search || ''} onChange={(e) => updateFilter('search', e.target.value)} />
                        <select className="border rounded px-3 py-2" value={filters.component_type || ''} onChange={(e) => updateFilter('component_type', e.target.value)}>
                            <option value="">All Type</option>
                            {componentTypes.map((type) => <option key={type} value={type}>{type}</option>)}
                        </select>
                        <select className="border rounded px-3 py-2" value={filters.is_active || ''} onChange={(e) => updateFilter('is_active', e.target.value)}>
                            <option value="">All Status</option>
                            <option value="1">active</option>
                            <option value="0">inactive</option>
                        </select>
                    </div>
                    <Link href={route('salary-components.create')} className="px-4 py-2 bg-indigo-600 text-white rounded">Tambah</Link>
                </div>
                <div className="bg-white shadow rounded overflow-hidden">
                    <table className="min-w-full divide-y">
                        <thead className="bg-gray-50"><tr><th className="px-4 py-2 text-left">Code</th><th className="px-4 py-2 text-left">Name</th><th className="px-4 py-2 text-left">Type</th><th className="px-4 py-2 text-left">Method</th><th className="px-4 py-2 text-left">Taxable</th><th className="px-4 py-2 text-left">Status</th><th className="px-4 py-2 text-left">Aksi</th></tr></thead>
                        <tbody className="divide-y">
                            {components.data.map((component) => (
                                <tr key={component.id}>
                                    <td className="px-4 py-2">{component.code}</td>
                                    <td className="px-4 py-2">{component.name}</td>
                                    <td className="px-4 py-2">{component.component_type}</td>
                                    <td className="px-4 py-2">{component.calculation_method}</td>
                                    <td className="px-4 py-2">{component.is_taxable ? 'yes' : 'no'}</td>
                                    <td className="px-4 py-2">{component.is_active ? 'active' : 'inactive'}</td>
                                    <td className="px-4 py-2 space-x-2">
                                        <Link href={route('salary-components.show', component.id)} className="text-indigo-600">Detail</Link>
                                        <Link href={route('salary-components.edit', component.id)} className="text-blue-600">Edit</Link>
                                        <button onClick={() => router.delete(route('salary-components.destroy', component.id))} className="text-red-600">Hapus</button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
