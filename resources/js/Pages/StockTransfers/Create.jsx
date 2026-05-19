import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Create({ auth, branches, items }) {
    const { data, setData, post, processing, errors } = useForm({
        transfer_date: new Date().toISOString().slice(0, 10),
        from_branch_id: branches[0]?.id || '',
        to_branch_id: branches[1]?.id || branches[0]?.id || '',
        notes: '',
        lines: [
            {
                item_id: items[0]?.id || '',
                quantity: 1,
                notes: '',
            },
        ],
    });

    const addLine = () => {
        setData('lines', [
            ...data.lines,
            {
                item_id: items[0]?.id || '',
                quantity: 1,
                notes: '',
            },
        ]);
    };

    const updateLine = (index, key, value) => {
        const lines = [...data.lines];
        lines[index][key] = value;
        setData('lines', lines);
    };

    const removeLine = (index) => {
        setData('lines', data.lines.filter((_, i) => i !== index));
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('stock-transfers.store'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Buat Stock Transfer</h2>}
        >
            <Head title="Buat Stock Transfer" />

            <div className="py-6">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white shadow sm:rounded-lg p-6">
                        <form onSubmit={submit} className="space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium">Tanggal Transfer</label>
                                    <input
                                        type="date"
                                        value={data.transfer_date}
                                        onChange={(e) => setData('transfer_date', e.target.value)}
                                        className="mt-1 w-full border rounded px-3 py-2 text-sm"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium">Lokasi Asal</label>
                                    <select
                                        value={data.from_branch_id}
                                        onChange={(e) => setData('from_branch_id', e.target.value)}
                                        className="mt-1 w-full border rounded px-3 py-2 text-sm"
                                    >
                                        {branches.map((branch) => (
                                            <option key={branch.id} value={branch.id}>{branch.name}</option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium">Lokasi Tujuan</label>
                                    <select
                                        value={data.to_branch_id}
                                        onChange={(e) => setData('to_branch_id', e.target.value)}
                                        className="mt-1 w-full border rounded px-3 py-2 text-sm"
                                    >
                                        {branches.map((branch) => (
                                            <option key={branch.id} value={branch.id}>{branch.name}</option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium">Catatan</label>
                                    <input
                                        type="text"
                                        value={data.notes}
                                        onChange={(e) => setData('notes', e.target.value)}
                                        className="mt-1 w-full border rounded px-3 py-2 text-sm"
                                    />
                                </div>
                            </div>

                            <div className="space-y-3">
                                <div className="flex justify-between items-center">
                                    <h3 className="font-medium">Item Transfer</h3>
                                    <button
                                        type="button"
                                        onClick={addLine}
                                        className="px-3 py-2 bg-gray-100 rounded text-sm"
                                    >
                                        Tambah Baris
                                    </button>
                                </div>
                                <div className="space-y-2">
                                    {data.lines.map((line, index) => (
                                        <div key={index} className="grid grid-cols-1 md:grid-cols-12 gap-2 items-center border rounded p-3">
                                            <div className="md:col-span-5">
                                                <select
                                                    value={line.item_id}
                                                    onChange={(e) => updateLine(index, 'item_id', e.target.value)}
                                                    className="w-full border rounded px-3 py-2 text-sm"
                                                >
                                                    {items.map((item) => (
                                                        <option key={item.id} value={item.id}>{item.code} - {item.name}</option>
                                                    ))}
                                                </select>
                                            </div>
                                            <div className="md:col-span-3">
                                                <input
                                                    type="number"
                                                    min="0.001"
                                                    step="0.001"
                                                    value={line.quantity}
                                                    onChange={(e) => updateLine(index, 'quantity', e.target.value)}
                                                    className="w-full border rounded px-3 py-2 text-sm"
                                                />
                                            </div>
                                            <div className="md:col-span-3">
                                                <input
                                                    type="text"
                                                    value={line.notes || ''}
                                                    onChange={(e) => updateLine(index, 'notes', e.target.value)}
                                                    placeholder="Catatan"
                                                    className="w-full border rounded px-3 py-2 text-sm"
                                                />
                                            </div>
                                            <div className="md:col-span-1 text-right">
                                                <button
                                                    type="button"
                                                    onClick={() => removeLine(index)}
                                                    className="text-red-600 text-sm"
                                                >
                                                    Hapus
                                                </button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            {errors.error && <div className="text-red-600 text-sm">{errors.error}</div>}

                            <div className="flex justify-end gap-2">
                                <Link href={route('stock-transfers.index')} className="px-4 py-2 border rounded text-sm">Batal</Link>
                                <button type="submit" disabled={processing} className="px-4 py-2 bg-indigo-600 text-white rounded text-sm">
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
