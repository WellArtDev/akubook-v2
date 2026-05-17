import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import axios from 'axios';

export default function GeneralLedger({ accounts, fiscalPeriods }) {
    const [filters, setFilters] = useState({
        account_id: '',
        fiscal_period_id: '',
        from_date: '',
        to_date: '',
    });
    const [generalLedger, setGeneralLedger] = useState(null);
    const [loading, setLoading] = useState(false);

    const handleGenerate = async () => {
        setLoading(true);
        try {
            const response = await axios.post(route('reports.general-ledger.generate'), filters);
            setGeneralLedger(response.data);
        } catch (error) {
            console.error('Error generating general ledger:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleExport = (format) => {
        const url = format === 'excel' 
            ? route('reports.general-ledger.export-excel')
            : route('reports.general-ledger.export-pdf');
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
        form.appendChild(csrfInput);
        
        Object.entries(filters).forEach(([key, value]) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    };

    const handleDrillDown = (journalEntryId) => {
        router.visit(route('journal-entries.show', journalEntryId));
    };

    return (
        <AuthenticatedLayout>
            <Head title="General Ledger" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <h2 className="text-2xl font-semibold mb-6">General Ledger</h2>

                            {/* Filter Form */}
                            <div className="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">
                                        Account *
                                    </label>
                                    <select
                                        value={filters.account_id}
                                        onChange={(e) => setFilters({...filters, account_id: e.target.value})}
                                        className="mt-1 block w-full rounded-md border-gray-300"
                                        required
                                    >
                                        <option value="">Select Account</option>
                                        {accounts.map(account => (
                                            <option key={account.id} value={account.id}>
                                                {account.code} - {account.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700">
                                        Fiscal Period *
                                    </label>
                                    <select
                                        value={filters.fiscal_period_id}
                                        onChange={(e) => setFilters({...filters, fiscal_period_id: e.target.value})}
                                        className="mt-1 block w-full rounded-md border-gray-300"
                                        required
                                    >
                                        <option value="">Select Period</option>
                                        {fiscalPeriods.map(period => (
                                            <option key={period.id} value={period.id}>
                                                {period.period_name}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700">
                                        From Date *
                                    </label>
                                    <input
                                        type="date"
                                        value={filters.from_date}
                                        onChange={(e) => setFilters({...filters, from_date: e.target.value})}
                                        className="mt-1 block w-full rounded-md border-gray-300"
                                        required
                                    />
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700">
                                        To Date *
                                    </label>
                                    <input
                                        type="date"
                                        value={filters.to_date}
                                        onChange={(e) => setFilters({...filters, to_date: e.target.value})}
                                        className="mt-1 block w-full rounded-md border-gray-300"
                                        required
                                    />
                                </div>
                            </div>

                            <div className="flex gap-2 mb-6">
                                <button
                                    onClick={handleGenerate}
                                    disabled={loading || !filters.account_id || !filters.fiscal_period_id}
                                    className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400"
                                >
                                    {loading ? 'Generating...' : 'Generate Report'}
                                </button>

                                {generalLedger && (
                                    <>
                                        <button
                                            onClick={() => handleExport('excel')}
                                            className="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                                        >
                                            Export Excel
                                        </button>
                                        <button
                                            onClick={() => handleExport('pdf')}
                                            className="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                                        >
                                            Export PDF
                                        </button>
                                    </>
                                )}
                            </div>

                            {/* General Ledger Display */}
                            {generalLedger && (
                                <div>
                                    {/* Header */}
                                    <div className="mb-4 p-4 bg-gray-50 rounded">
                                        <h3 className="text-lg font-semibold">
                                            Account: [{generalLedger.account.code}] {generalLedger.account.name}
                                        </h3>
                                        <p className="text-sm text-gray-600">
                                            Period: {generalLedger.fiscal_period.name}
                                        </p>
                                        <p className="text-sm text-gray-600">
                                            Date Range: {generalLedger.date_range.from} to {generalLedger.date_range.to}
                                        </p>
                                        <p className="text-sm font-semibold mt-2">
                                            Opening Balance: Rp {generalLedger.opening_balance.toLocaleString('id-ID')}
                                        </p>
                                    </div>

                                    {/* Transaction Table */}
                                    <div className="overflow-x-auto">
                                        <table className="min-w-full divide-y divide-gray-200">
                                            <thead className="bg-gray-50">
                                                <tr>
                                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ref No</th>
                                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                                    <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Debit</th>
                                                    <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Credit</th>
                                                    <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                                                </tr>
                                            </thead>
                                            <tbody className="bg-white divide-y divide-gray-200">
                                                {generalLedger.lines.map((line, idx) => (
                                                    <tr 
                                                        key={idx}
                                                        onClick={() => handleDrillDown(line.journal_entry_id)}
                                                        className="hover:bg-gray-50 cursor-pointer"
                                                    >
                                                        <td className="px-4 py-2 text-sm">{line.date}</td>
                                                        <td className="px-4 py-2 text-sm">{line.reference}</td>
                                                        <td className="px-4 py-2 text-sm">{line.description}</td>
                                                        <td className="px-4 py-2 text-sm text-right">
                                                            {line.debit > 0 ? line.debit.toLocaleString('id-ID') : '-'}
                                                        </td>
                                                        <td className="px-4 py-2 text-sm text-right">
                                                            {line.credit > 0 ? line.credit.toLocaleString('id-ID') : '-'}
                                                        </td>
                                                        <td className="px-4 py-2 text-sm text-right font-semibold">
                                                            {line.balance.toLocaleString('id-ID')}
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>

                                    {/* Summary */}
                                    <div className="mt-4 p-4 bg-gray-50 rounded">
                                        <div className="grid grid-cols-2 gap-4">
                                            <div>
                                                <p className="text-sm text-gray-600">Total Debit:</p>
                                                <p className="text-lg font-semibold">Rp {generalLedger.total_debit.toLocaleString('id-ID')}</p>
                                            </div>
                                            <div>
                                                <p className="text-sm text-gray-600">Total Credit:</p>
                                                <p className="text-lg font-semibold">Rp {generalLedger.total_credit.toLocaleString('id-ID')}</p>
                                            </div>
                                            <div>
                                                <p className="text-sm text-gray-600">Net Movement:</p>
                                                <p className="text-lg font-semibold">Rp {generalLedger.net_movement.toLocaleString('id-ID')}</p>
                                            </div>
                                            <div>
                                                <p className="text-sm text-gray-600">Ending Balance:</p>
                                                <p className="text-lg font-semibold">Rp {generalLedger.ending_balance.toLocaleString('id-ID')}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
