import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Show({ auth, transfer }) {
    const confirmTransfer = () => {
        if (confirm('Konfirmasi transfer ini?')) {
            router.post(route('stock-transfers.confirm', transfer.id));
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Detail Stock Transfer</h2>}
        >
            <Head title={`Stock Transfer ${transfer.transfer_number}`} />

            <div className="py-6">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
                    <div className="bg-white shadow sm:rounded-lg p-6">
                        <div className="flex justify-between items-start">
                            <div>
                                <div className="text-lg font-semibold">{transfer.transfer_number}</div>
                                <div className="text-sm text-gray-600">Tanggal: {transfer.transfer_date}</div>
                                <div className="text-sm text-gray-600">Dari: {transfer.from_branch?.name}</div>
                                <div className="text-sm text-gray-600">Ke: {transfer.to_branch?.name}</div>
                                <div className="text-sm text-gray-600">Status: {transfer.status}</div>
                            </div>
                            <div className="flex gap-2">
                                <Link href={route('stock-transfers.index')} className="px-3 py-2 border rounded text-sm">Kembali</Link>
                                {transfer.status === 'draft' && (
                                    <button onClick={confirmTransfer} className="px-3 py-2 bg-indigo-600 text-white rounded text-sm">Konfirmasi</button>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="bg-white shadow sm:rounded-lg overflow-x-auto">
                        <table className="min-w-full text-sm">
                            <thead className="bg-gray-50 text-left text-gray-600 uppercase text-xs">
                                <tr>
                                    <th className="px-4 py-3">No</th>
                                    <th className="px-4 py-3">Item</th>
                                    <th className="px-4 py-3">Qty</th>
                                    <th className="px-4 py-3">Unit</th>
                                    <th className="px-4 py-3">Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                {transfer.lines.map((line) => (
                                    <tr key={line.id} className="border-t">
                                        <td className="px-4 py-3">{line.line_number}</td>
                                        <td className="px-4 py-3">{line.item?.code} - {line.item?.name}</td>
                                        <td className="px-4 py-3">{line.quantity}</td>
                                        <td className="px-4 py-3">{line.unit}</td>
                                        <td className="px-4 py-3">{line.notes || '-'}</td>
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
