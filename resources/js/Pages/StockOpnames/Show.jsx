import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Show({ auth, opname }) {
    const confirmOpname = () => {
        if (confirm('Confirm stock opname?')) {
            router.post(route('stock-opnames.confirm', opname.id));
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={opname.opname_number} />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white shadow-sm sm:rounded-lg p-6">
                        <div className="flex justify-between items-center mb-6">
                            <div><h1 className="text-2xl font-semibold">{opname.opname_number}</h1><p>{opname.status}</p></div>
                            <div className="space-x-2">
                                {opname.status === 'draft' && <button onClick={confirmOpname} className="bg-green-600 text-white px-4 py-2 rounded">Confirm</button>}
                                <Link href={route('stock-opnames.index')} className="bg-gray-600 text-white px-4 py-2 rounded">Back</Link>
                            </div>
                        </div>
                        <table className="min-w-full text-sm">
                            <thead><tr className="border-b"><th className="text-left py-2">Item</th><th>System</th><th>Physical</th><th>Variance</th><th>Notes</th></tr></thead>
                            <tbody>
                                {opname.lines.map((line) => (
                                    <tr key={line.id} className="border-b">
                                        <td className="py-2">{line.item?.code} - {line.item?.name}</td>
                                        <td>{Number(line.system_quantity).toLocaleString()}</td>
                                        <td>{Number(line.physical_quantity).toLocaleString()}</td>
                                        <td>{Number(line.variance_quantity).toLocaleString()}</td>
                                        <td>{line.notes || '-'}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
