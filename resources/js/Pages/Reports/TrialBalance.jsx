import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import axios from 'axios';

export default function TrialBalance({ fiscalPeriods }) {
    const [filters, setFilters] = useState({
        fiscal_period_id: '',
        from_date: '',
        to_date: '',
        account_types: [],
        show_zero_balance: false,
    });
    const [trialBalance, setTrialBalance] = useState(null);
    const [loading, setLoading] = useState(false);

    const handleGenerate = async () => {
        setLoading(true);
        try {
            const response = await axios.post(route('reports.trial-balance.generate'), filters);
            setTrialBalance(response.data);
        } catch (error) {
            console.error('Error generating trial balance:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleExport = (format) => {
        const url = format === 'excel' 
            ? route('reports.trial-balance.export-excel')
            : route('reports.trial-balance.export-pdf');
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
        form.appendChild(csrfInput);
        
        // Add filters as hidden inputs
        Object.entries(filters).forEach(([key, value]) => {
            if (Array.isArray(value)) {
                value.forEach(v => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `${key}[]`;
                    input.value = v;
                    form.appendChild(input);
                });
            } else {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }
        });
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    };

    return (
        <AuthenticatedLayout>
            <Head title="Trial Balance" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <h2 className="text-2xl font-semibold mb-6">Trial Balance</h2>

                            {/* Filter Form */}
                            <div className="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">
                                        Fiscal Period
                                    </label>
                                    <select
                                        value={filters.fiscal_period_id}
                                        onChange={(e) => setFilters({...filters, fiscal_period_id: e.target.value})}
                                        className="mt-1 block w-full rounded-md border-gray-300"
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
                                        From Date
                                    </label>
                                    <input
                                        type="date"
                                        value={filters.from_date}
                                        onChange={(e) => setFilters({...filters, from_date: e.target.value})}
                                        className="mt-1 block w-full rounded-md border-gray-300"
                                    />
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700">
                                        To Date
                                    </label>
                                    <input
                                        type="date"
                                        value={filters.to_date}
                                        onChange={(e) => setFilters({...filters, to_date: e.target.value})}
                                        className="mt-1 block w-full rounded-md border-gray-300"
                                    />
                                </div>

                                <div>
                                    <label className="flex items-center">
                                        <input
                                            type="checkbox"
                                            checked={filters.show_zero_balance}
                                            onChange={(e) => setFilters({...filters, show_zero_balance: e.target.checked})}
                                            className="rounded border-gray-300"
                                        />
                                        <span className="ml-2 text-sm text-gray-700">Show Zero Balance</span>
                                    </label>
                                </div>
                            </div>

                            <div className="flex gap-2 mb-6">
                                <button
                                    onClick={handleGenerate}
                                    disabled={loading}
                                    className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400"
                                >
                                    {loading ? 'Generating...' : 'Generate Report'}
                                </button>

                                {trialBalance && (
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

                            {/* Trial Balance Display */}
                            {trialBalance && (
                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                    Account Code
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                    Account Name
                                                </th>
                                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                                    Debit
                                                </th>
                                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                                    Credit
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {trialBalance.groups.map((group, idx) => (
                                                <React.Fragment key={idx}>
                                                    <tr className="bg-gray-100">
                                                        <td colSpan="4" className="px-6 py-2 font-semibold">
                                                            {group.type.toUpperCase()}
                                                        </td>
                                                    </tr>
                                                    {group.accounts.map((account, aidx) => (
                                                        <tr key={aidx}>
                                                            <td className="px-6 py-2">{account.account_code}</td>
                                                            <td className="px-6 py-2">{account.account_name}</td>
                                                            <td className="px-6 py-2 text-right">
                                                                {account.debit > 0 ? account.debit.toLocaleString('id-ID') : '-'}
                                                            </td>
                                                            <td className="px-6 py-2 text-right">
                                                                {account.credit > 0 ? account.credit.toLocaleString('id-ID') : '-'}
                                                            </td>
                                                        </tr>
                                                    ))}
                                                    <tr className="bg-gray-50 font-semibold">
                                                        <td colSpan="2" className="px-6 py-2">Total {group.type}</td>
                                                        <td className="px-6 py-2 text-right">{group.total_debit.toLocaleString('id-ID')}</td>
                                                        <td className="px-6 py-2 text-right">{group.total_credit.toLocaleString('id-ID')}</td>
                                                    </tr>
                                                </React.Fragment>
                                            ))}
                                            <tr className="bg-gray-200 font-bold">
                                                <td colSpan="2" className="px-6 py-3">GRAND TOTAL</td>
                                                <td className="px-6 py-3 text-right">{trialBalance.grand_total_debit.toLocaleString('id-ID')}</td>
                                                <td className="px-6 py-3 text-right">{trialBalance.grand_total_credit.toLocaleString('id-ID')}</td>
                                            </tr>
                                            <tr>
                                                <td colSpan="4" className="px-6 py-3 text-center">
                                                    <span className={`font-semibold ${trialBalance.is_balanced ? 'text-green-600' : 'text-red-600'}`}>
                                                        {trialBalance.is_balanced ? '✓ BALANCED' : '✗ OUT OF BALANCE'}
                                                        {!trialBalance.is_balanced && ` (Difference: ${trialBalance.difference.toLocaleString('id-ID')})`}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
