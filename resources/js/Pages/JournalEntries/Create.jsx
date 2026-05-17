import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function Create({ auth, accounts }) {
    const { data, setData, post, processing, errors } = useForm({
        journal_date: new Date().toISOString().split('T')[0],
        reference_number: '',
        description: '',
        entry_type: 'manual',
        lines: [
            { account_id: '', description: '', debit_amount: 0, credit_amount: 0 },
            { account_id: '', description: '', debit_amount: 0, credit_amount: 0 },
        ],
    });

    const [action, setAction] = useState('draft');

    const addLine = () => {
        setData('lines', [...data.lines, { account_id: '', description: '', debit_amount: 0, credit_amount: 0 }]);
    };

    const removeLine = (index) => {
        if (data.lines.length > 2) {
            setData('lines', data.lines.filter((_, i) => i !== index));
        }
    };

    const updateLine = (index, field, value) => {
        const newLines = [...data.lines];
        newLines[index][field] = value;
        setData('lines', newLines);
    };

    const totalDebit = data.lines.reduce((sum, line) => sum + parseFloat(line.debit_amount || 0), 0);
    const totalCredit = data.lines.reduce((sum, line) => sum + parseFloat(line.credit_amount || 0), 0);
    const isBalanced = totalDebit === totalCredit && totalDebit > 0;

    const handleSubmit = (e, submitAction) => {
        e.preventDefault();
        setAction(submitAction);
        post(route('journal-entries.store'), {
            data: { ...data, action: submitAction },
        });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Tambah Journal Entry</h2>}
        >
            <Head title="Tambah Journal Entry" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <form className="p-6">
                            {/* Header Fields */}
                            <div className="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">
                                        Tanggal <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        value={data.journal_date}
                                        onChange={(e) => setData('journal_date', e.target.value)}
                                        className="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                    {errors.journal_date && <p className="mt-1 text-sm text-red-600">{errors.journal_date}</p>}
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Reference Number</label>
                                    <input
                                        type="text"
                                        value={data.reference_number}
                                        onChange={(e) => setData('reference_number', e.target.value)}
                                        className="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="Optional"
                                    />
                                </div>
                            </div>

                            <div className="mb-6">
                                <label className="block text-sm font-medium text-gray-700">
                                    Deskripsi <span className="text-red-500">*</span>
                                </label>
                                <textarea
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    rows="3"
                                    className="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Deskripsi journal entry"
                                />
                                {errors.description && <p className="mt-1 text-sm text-red-600">{errors.description}</p>}
                            </div>

                            {/* Lines Table */}
                            <div className="mb-6">
                                <div className="flex items-center justify-between mb-3">
                                    <h3 className="text-lg font-medium text-gray-900">Journal Lines</h3>
                                    <button
                                        type="button"
                                        onClick={addLine}
                                        className="px-3 py-1 text-sm text-white bg-green-600 rounded-md hover:bg-green-700"
                                    >
                                        + Add Line
                                    </button>
                                </div>

                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th className="px-3 py-2 text-xs font-medium text-left text-gray-500 uppercase">Account</th>
                                                <th className="px-3 py-2 text-xs font-medium text-left text-gray-500 uppercase">Description</th>
                                                <th className="px-3 py-2 text-xs font-medium text-right text-gray-500 uppercase">Debit</th>
                                                <th className="px-3 py-2 text-xs font-medium text-right text-gray-500 uppercase">Credit</th>
                                                <th className="px-3 py-2 text-xs font-medium text-center text-gray-500 uppercase">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {data.lines.map((line, index) => (
                                                <tr key={index}>
                                                    <td className="px-3 py-2">
                                                        <select
                                                            value={line.account_id}
                                                            onChange={(e) => updateLine(index, 'account_id', e.target.value)}
                                                            className="block w-full text-sm border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500"
                                                        >
                                                            <option value="">Pilih Akun</option>
                                                            {accounts.map((account) => (
                                                                <option key={account.id} value={account.id}>
                                                                    {account.code} - {account.name}
                                                                </option>
                                                            ))}
                                                        </select>
                                                    </td>
                                                    <td className="px-3 py-2">
                                                        <input
                                                            type="text"
                                                            value={line.description}
                                                            onChange={(e) => updateLine(index, 'description', e.target.value)}
                                                            className="block w-full text-sm border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500"
                                                            placeholder="Optional"
                                                        />
                                                    </td>
                                                    <td className="px-3 py-2">
                                                        <input
                                                            type="number"
                                                            value={line.debit_amount}
                                                            onChange={(e) => updateLine(index, 'debit_amount', e.target.value)}
                                                            className="block w-full text-sm text-right border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500"
                                                            min="0"
                                                            step="0.01"
                                                        />
                                                    </td>
                                                    <td className="px-3 py-2">
                                                        <input
                                                            type="number"
                                                            value={line.credit_amount}
                                                            onChange={(e) => updateLine(index, 'credit_amount', e.target.value)}
                                                            className="block w-full text-sm text-right border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500"
                                                            min="0"
                                                            step="0.01"
                                                        />
                                                    </td>
                                                    <td className="px-3 py-2 text-center">
                                                        <button
                                                            type="button"
                                                            onClick={() => removeLine(index)}
                                                            disabled={data.lines.length <= 2}
                                                            className="text-red-600 hover:text-red-900 disabled:opacity-50 disabled:cursor-not-allowed"
                                                        >
                                                            Remove
                                                        </button>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                        <tfoot className="bg-gray-50">
                                            <tr>
                                                <td colSpan="2" className="px-3 py-2 text-right font-medium">Total:</td>
                                                <td className="px-3 py-2 text-right font-medium">
                                                    {new Intl.NumberFormat('id-ID').format(totalDebit)}
                                                </td>
                                                <td className="px-3 py-2 text-right font-medium">
                                                    {new Intl.NumberFormat('id-ID').format(totalCredit)}
                                                </td>
                                                <td className="px-3 py-2 text-center">
                                                    {isBalanced ? (
                                                        <span className="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Balance</span>
                                                    ) : (
                                                        <span className="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Out of Balance</span>
                                                    )}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                {errors.lines && <p className="mt-2 text-sm text-red-600">{errors.lines}</p>}
                            </div>

                            {/* Actions */}
                            <div className="flex items-center justify-end gap-3">
                                <Link
                                    href={route('journal-entries.index')}
                                    className="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
                                >
                                    Batal
                                </Link>
                                <button
                                    type="button"
                                    onClick={(e) => handleSubmit(e, 'draft')}
                                    disabled={processing}
                                    className="px-4 py-2 text-white bg-gray-600 rounded-md hover:bg-gray-700 disabled:opacity-50"
                                >
                                    Save as Draft
                                </button>
                                <button
                                    type="button"
                                    onClick={(e) => handleSubmit(e, 'post')}
                                    disabled={processing || !isBalanced}
                                    className="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50"
                                >
                                    Save & Post
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
