import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Show({ auth, component }) {
    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Salary Component Detail</h2>}>
            <Head title="Salary Component Detail" />
            <div className="py-6 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div className="flex gap-2">
                    <Link href={route('salary-components.index')} className="px-3 py-2 bg-gray-200 rounded">Kembali</Link>
                    <Link href={route('salary-components.edit', component.id)} className="px-3 py-2 bg-indigo-600 text-white rounded">Edit</Link>
                    <button onClick={() => router.delete(route('salary-components.destroy', component.id))} className="px-3 py-2 bg-red-600 text-white rounded">Hapus</button>
                </div>
                <div className="bg-white rounded shadow p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><div className="text-sm text-gray-500">Code</div><div>{component.code}</div></div>
                    <div><div className="text-sm text-gray-500">Name</div><div>{component.name}</div></div>
                    <div><div className="text-sm text-gray-500">Type</div><div>{component.component_type}</div></div>
                    <div><div className="text-sm text-gray-500">Method</div><div>{component.calculation_method}</div></div>
                    <div><div className="text-sm text-gray-500">Default Amount</div><div>{component.default_amount}</div></div>
                    <div><div className="text-sm text-gray-500">Default Percentage</div><div>{component.default_percentage}</div></div>
                    <div><div className="text-sm text-gray-500">Taxable</div><div>{component.is_taxable ? 'yes' : 'no'}</div></div>
                    <div><div className="text-sm text-gray-500">Status</div><div>{component.is_active ? 'active' : 'inactive'}</div></div>
                    <div className="md:col-span-2"><div className="text-sm text-gray-500">Account</div><div>{component.account ? `${component.account.code} - ${component.account.name}` : '-'}</div></div>
                    <div className="md:col-span-2"><div className="text-sm text-gray-500">Description</div><div>{component.description || '-'}</div></div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
