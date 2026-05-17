import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import axios from 'axios';

export default function BalanceSheet({ fiscalPeriods }) {
    const [filters, setFilters] = useState({
        as_of_date: '',
        fiscal_period_id: '',
        detail_level: 'summary',
    });
    const [balanceSheet, setBalanceSheet] = useState(null);
    const [loading, setLoading] = useState(false);

    const handleGenerate = async () => {
        setLoading(true);
        try {
            const response = await axios.post(route('reports.balance-sheet.generate'), filters);
            setBalanceSheet(response.data);
        } catch (error) {
            console.error('Error generating balance sheet:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleExport = (format) => {
        const url = format === 'excel' 
            ? route('reports.balance-sheet.export-excel')
            : route('reports.balance-sheet.export-pdf');
        
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

    const formatCurrency = (amount) => {
        return amount.toLocaleString('id-ID');
    };

    return (
        <AuthenticatedLayout>
            <Head title="Balance Sheet" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <h2 className="text-2xl font-semibold mb-6">Balance Sheet</h2>

                            {/* Filter Form */}
                            <div className="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">
                                        As of Date *
                                    </label>
                                    <input
                                        type="date"
                                        value={filters.as_of_date}
                                        onChange={(e) => setFilters({...filters, as_of_date: e.target.value})}
                                        className="mt-1 block w-full rounded-md border-gray-300"
                                        required
                                    />
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
                                        Detail Level
                                    </label>
                                    <select
                                        value={filters.detail_level}
                                        onChange={(e) => setFilters({...filters, detail_level: e.target.value})}
                                        className="mt-1 block w-full rounded-md border-gray-300"
                                    >
                                        <option value="summary">Summary</option>
                                        <option value="detailed">Detailed</option>
                                    </select>
                                </div>
                            </div>

                            <div className="flex gap-2 mb-6">
                                <button
                                    onClick={handleGenerate}
                                    disabled={loading || !filters.as_of_date || !filters.fiscal_period_id}
                                    className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400"
                                >
                                    {loading ? 'Generating...' : 'Generate Report'}
                                </button>

                                {balanceSheet && (
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

                            {/* Balance Sheet Display */}
                            {balanceSheet && (
                                <div className="space-y-6">
                                    <div className="text-center mb-4">
                                        <p className="text-sm text-gray-600">As of: {balanceSheet.current_date.as_of_date}</p>
                                    </div>

                                    {/* Assets Section */}
                                    <div>
                                        <h3 className="text-lg font-bold mb-2">ASSETS</h3>
                                        
                                        <h4 className="text-md font-semibold mt-3 mb-1">CURRENT ASSETS</h4>
                                        {filters.detail_level === 'detailed' && balanceSheet.current_date.assets.current.map((item, idx) => (
                                            <div key={idx} className="flex justify-between py-1 pl-4">
                                                <span>{item.account_name}</span>
                                                <span>{formatCurrency(item.balance)}</span>
                                            </div>
                                        ))}
                                        <div className="flex justify-between py-2 font-semibold border-t">
                                            <span>TOTAL CURRENT ASSETS</span>
                                            <span>{formatCurrency(balanceSheet.current_date.assets.total_current)}</span>
                                        </div>

                                        <h4 className="text-md font-semibold mt-3 mb-1">NON-CURRENT ASSETS</h4>
                                        {filters.detail_level === 'detailed' && balanceSheet.current_date.assets.non_current.map((item, idx) => (
                                            <div key={idx} className="flex justify-between py-1 pl-4">
                                                <span>{item.account_name}</span>
                                                <span>{formatCurrency(item.balance)}</span>
                                            </div>
                                        ))}
                                        <div className="flex justify-between py-2 font-semibold border-t">
                                            <span>TOTAL NON-CURRENT ASSETS</span>
                                            <span>{formatCurrency(balanceSheet.current_date.assets.total_non_current)}</span>
                                        </div>

                                        <div className="flex justify-between py-2 font-bold border-t-2 border-gray-800">
                                            <span>TOTAL ASSETS</span>
                                            <span>{formatCurrency(balanceSheet.current_date.assets.total)}</span>
                                        </div>
                                    </div>

                                    {/* Liabilities Section */}
                                    <div>
                                        <h3 className="text-lg font-bold mb-2">LIABILITIES</h3>
                                        
                                        <h4 className="text-md font-semibold mt-3 mb-1">CURRENT LIABILITIES</h4>
                                        {filters.detail_level === 'detailed' && balanceSheet.current_date.liabilities.current.map((item, idx) => (
                                            <div key={idx} className="flex justify-between py-1 pl-4">
                                                <span>{item.account_name}</span>
                                                <span>{formatCurrency(item.balance)}</span>
                                            </div>
                                        ))}
                                        <div className="flex justify-between py-2 font-semibold border-t">
                                            <span>TOTAL CURRENT LIABILITIES</span>
                                            <span>{formatCurrency(balanceSheet.current_date.liabilities.total_current)}</span>
                                        </div>

                                        <h4 className="text-md font-semibold mt-3 mb-1">NON-CURRENT LIABILITIES</h4>
                                        {filters.detail_level === 'detailed' && balanceSheet.current_date.liabilities.non_current.map((item, idx) => (
                                            <div key={idx} className="flex justify-between py-1 pl-4">
                                                <span>{item.account_name}</span>
                                                <span>{formatCurrency(item.balance)}</span>
                                            </div>
                                        ))}
                                        <div className="flex justify-between py-2 font-semibold border-t">
                                            <span>TOTAL NON-CURRENT LIABILITIES</span>
                                            <span>{formatCurrency(balanceSheet.current_date.liabilities.total_non_current)}</span>
                                        </div>

                                        <div className="flex justify-between py-2 font-bold border-t-2 border-gray-800">
                                            <span>TOTAL LIABILITIES</span>
                                            <span>{formatCurrency(balanceSheet.current_date.liabilities.total)}</span>
                                        </div>
                                    </div>

                                    {/* Equity Section */}
                                    <div>
                                        <h3 className="text-lg font-bold mb-2">EQUITY</h3>
                                        {filters.detail_level === 'detailed' && balanceSheet.current_date.equity.accounts.map((item, idx) => (
                                            <div key={idx} className="flex justify-between py-1 pl-4">
                                                <span>{item.account_name}</span>
                                                <span>{formatCurrency(item.balance)}</span>
                                            </div>
                                        ))}
                                        <div className="flex justify-between py-1 pl-4">
                                            <span>Current Year Profit/Loss</span>
                                            <span className={balanceSheet.current_date.equity.current_year_profit_loss < 0 ? 'text-red-600' : ''}>
                                                {balanceSheet.current_date.equity.current_year_profit_loss < 0 
                                                    ? `(${formatCurrency(Math.abs(balanceSheet.current_date.equity.current_year_profit_loss))})` 
                                                    : formatCurrency(balanceSheet.current_date.equity.current_year_profit_loss)}
                                            </span>
                                        </div>
                                        <div className="flex justify-between py-2 font-bold border-t-2 border-gray-800">
                                            <span>TOTAL EQUITY</span>
                                            <span>{formatCurrency(balanceSheet.current_date.equity.total)}</span>
                                        </div>
                                    </div>

                                    {/* Total Liabilities + Equity */}
                                    <div className="flex justify-between py-3 font-bold text-lg border-t-4 border-gray-900">
                                        <span>TOTAL LIABILITIES + EQUITY</span>
                                        <span>{formatCurrency(balanceSheet.current_date.total_liabilities_equity)}</span>
                                    </div>

                                    {/* Balance Check & Ratios */}
                                    <div className="mt-6 p-4 bg-gray-50 rounded">
                                        <div className="text-center mb-4">
                                            <span className={`font-semibold text-lg ${balanceSheet.current_date.is_balanced ? 'text-green-600' : 'text-red-600'}`}>
                                                {balanceSheet.current_date.is_balanced ? '✓ BALANCED' : '✗ OUT OF BALANCE'}
                                                {!balanceSheet.current_date.is_balanced && ` (Difference: ${formatCurrency(balanceSheet.current_date.difference)})`}
                                            </span>
                                        </div>
                                        
                                        <div className="grid grid-cols-3 gap-4 mt-4">
                                            <div className="text-center">
                                                <p className="text-sm text-gray-600">Current Ratio</p>
                                                <p className="text-lg font-semibold">{balanceSheet.current_date.ratios.current_ratio.toFixed(2)}</p>
                                            </div>
                                            <div className="text-center">
                                                <p className="text-sm text-gray-600">Debt to Equity Ratio</p>
                                                <p className="text-lg font-semibold">{balanceSheet.current_date.ratios.debt_to_equity_ratio.toFixed(2)}</p>
                                            </div>
                                            <div className="text-center">
                                                <p className="text-sm text-gray-600">Working Capital</p>
                                                <p className="text-lg font-semibold">Rp {formatCurrency(balanceSheet.current_date.ratios.working_capital)}</p>
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
