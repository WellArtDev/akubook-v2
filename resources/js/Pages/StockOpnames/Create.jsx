import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Create({ auth, lines }) {
    const { data, setData, post, processing } = useForm({
        opname_date: new Date().toISOString().slice(0, 10),
        notes: '',
        lines,
    });

    const updateLine = (index, field, value) => {
        const next = [...data.lines];
        next[index] = { ...next[index], [field]: value };
        setData('lines', next);
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('stock-opnames.store'));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Create Stock Opname" />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white shadow-sm sm:rounded-lg p-6">
                        <div className="flex justify-between items-center mb-6">
                            <h1 className="text-2xl font-semibold">Create Stock Opname</h1>
                            <Link href={route('stock-opnames.index')} className="bg-gray-600 text-white px-4 py-2 rounded">Back</Link>
                        </div>
                        <form onSubmit={submit} className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <input type="date" value={data.opname_date} onChange={(e) => setData('opname_date', e.target.value)} className="border rounded px-3 py-2" />
                                <input value={data.notes} onChange={(e) => setData('notes', e.target.value)} className="border rounded px-3 py-2" placeholder="Notes" />
                            </div>
                            <div className="overflow-x-auto">
                                <table className="min-w-full text-sm">
                                    <thead><tr className="border-b"><th className="text-left py-2">Item</th><th>System</th><th>Physical</th><th>Variance</th><th>Notes</th></tr></thead>
                                    <tbody>
                                        {data.lines.map((line, index) => {
                                            const variance = Number(line.physical_quantity || 0) - Number(line.system_quantity || 0);
                                            return (
                                                <tr key={line.item_id} className="border-b">
                                                    <td className="py-2">{line.item_code} - {line.item_name}</td>
                                                    <td><input type="number" step="0.001" value={line.system_quantity} onChange={(e) => updateLine(index, 'system_quantity', e.target.value)} className="border rounded px-2 py-1 w-28" /></td>
                                                    <td><input type="number" step="0.001" value={line.physical_quantity} onChange={(e) => updateLine(index, 'physical_quantity', e.target.value)} className="border rounded px-2 py-1 w-28" /></td>
                                                    <td>{variance.toLocaleString()}</td>
                                                    <td><input value={line.notes || ''} onChange={(e) => updateLine(index, 'notes', e.target.value)} className="border rounded px-2 py-1" /></td>
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                </table>
                            </div>
                            <div className="flex justify-end"><button disabled={processing} className="bg-blue-600 text-white px-4 py-2 rounded">Save</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
