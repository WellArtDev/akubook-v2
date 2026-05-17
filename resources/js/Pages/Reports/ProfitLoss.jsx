import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import axios from 'axios';

export default function ProfitLoss({ fiscalPeriods }) {
    const [filters, setFilters] = useState({
        fiscal_period_id: '',
        from_date: '',
        to_date: '',
        detail_level: 'summary',
    });
    const [profitLoss, setProfitLoss] = useState(null);
    const [loading, setLoading] = useState(false);

    const handleGenerate = async () => {
        setLoading(true);
        try {
            const response = await axios.post(route('reports.profit-loss.generate'), filters);
            setProfitLoss(response.data);
        } catch (error) {
            console.error('Error generating profit & loss:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleExport = (format) => {
        const url = format === 'excel' 
            ? route('reports.profit-loss.export-excel')
            : route('reports.profit-loss.export-pdf');
        
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
            <Head title="Profit & Loss Statement" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <h2 className="text-2xl font-semibold mb-6">Profit & Loss Statement</h2>

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
                                    disabled={loading}
                                    className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400"
                                >
                                    {loading ? 'Generating...' : 'Generate Report'}
                                </button>

                                {profitLoss && (
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

                            {/* P&L Display */}
                            {profitLoss && (
                                <div className="space-y-6">
                                    {/* Revenue Section */}
                                    <div>
                                        <h3 className="text-lg font-semibold mb-2">REVENUE</h3>
                                        {filters.detail_level === 'detailed' && profitLoss.current_period.revenue.map((item, idx) => (
                                            <div key={idx} className="flex justify-between py-1 pl-4">
                                                <span>{item.account_name}</span>
                                                <span>{formatCurrency(item.amount)}</span>
                                            </div>
                                        ))}
                                        <div className="flex justify-between py-2 font-semibold border-t">
                                            <span>TOTAL REVENUE</span>
                                            <span>{formatCurrency(profitLoss.current_period.total_revenue)}</span>
                                        </div>
                                    </div>

                                    {/* COGS Section */}
                                    <div>
                                        <h3 className="text-lg font-semibold mb-2">COST OF GOODS SOLD</h3>
                                        {filters.detail_level === 'detailed' && profitLoss.current_period.cogs.map((item, idx) => (
                                            <div key={idx} className="flex justify-between py-1 pl-4">
                                                <span>{item.account_name}</span>
                                                <span>{formatCurrency(item.amount)}</span>
                                            </div>
                                        ))}
                                        <div className="flex justify-between py-2 font-semibold border-t">
                                            <span>GROSS PROFIT</span>
                                            <span>{formatCurrency(profitLoss.current_period.gross_profit)}</span>
                                        </div>
                                        <div className="flex justify-between py-1 text-sm text-gray-600">
                                            <span>Gross Profit Margin:</span>
                                            <span>{profitLoss.current_period.gross_profit_margin.toFixed(2)}%</span>
                                        </div>
                                    </div>

                                    {/* Operating Expenses Section */}
                                    <div>
                                        <h3 className="text-lg font-semibold mb-2">OPERATING EXPENSES</h3>
                                        {filters.detail_level === 'detailed' && profitLoss.current_period.operating_expenses.map((item, idx) => (
                                            <div key={idx} className="flex justify-between py-1 pl-4">
                                                <span>{item.account_name}</span>
                                                <span>{formatCurrency(item.amount)}</span>
                                            </div>
                                        ))}
                                        <div className="flex justify-between py-2 font-semibold border-t">
                                            <span>OPERATING PROFIT</span>
                                            <span>{formatCurrency(profitLoss.current_period.operating_profit)}</span>
                                        </div>
                                        <div className="flex justify-between py-1 text-sm text-gray-600">
                                            <span>Operating Profit Margin:</span>
                                            <span>{profitLoss.current_period.operating_profit_margin.toFixed(2)}%</span>
                                        </div>
                                    </div>

                                    {/* Other Income/Expenses Section */}
                                    <div>
                                        <h3 className="text-lg font-semibold mb-2">OTHER INCOME/(EXPENSES)</h3>
                                        {filters.detail_level === 'detailed' && profitLoss.current_period.other_income_expenses.map((item, idx) => (
                                            <div key={idx} className="flex justify-between py-1 pl-4">
                                                <span>{item.account_name}</span>
                                                <span className={item.amount < 0 ? 'text-red-600' : ''}>
                                                    {item.amount < 0 ? `(${formatCurrency(Math.abs(item.amount))})` : formatCurrency(item.amount)}
                                                </span>
                                            </div>
                                        ))}
                                        <div className="flex justify-between py-2 font-semibold border-t">
                                            <span>NET PROFIT BEFORE TAX</span>
                                            <span>{formatCurrency(profitLoss.current_period.net_profit_before_tax)}</span>
                                        </div>
                                    </div>

                                    {/* Tax Section */}
                                    <div>
                                        <h3 className="text-lg font-semibold mb-2">TAX EXPENSE</h3>
                                        {filters.detail_level === 'detailed' && profitLoss.current_period.tax_expense.map((item, idx) => (
                                            <div key={idx} className="flex justify-between py-1 pl-4">
                                                <span>{item.account_name}</span>
                                                <span>{formatCurrency(item.amount)}</span>
                                            </div>
                                        ))}
                                        <div className="flex justify-between py-2 font-bold border-t-2 border-gray-800">
                                            <span>NET PROFIT AFTER TAX</span>
                                            <span className={profitLoss.current_period.net_profit_after_tax < 0 ? 'text-red-600' : 'text-green-600'}>
                                                {profitLoss.current_period.net_profit_after_tax < 0 
                                                    ? `(${formatCurrency(Math.abs(profitLoss.current_period.net_profit_after_tax))})` 
                                                    : formatCurrency(profitLoss.current_period.net_profit_after_tax)}
                                            </span>
                                        </div>
                                        <div className="flex justify-between py-1 text-sm text-gray-600">
                                            <span>Net Profit Margin:</span>
                                            <span>{profitLoss.current_period.net_profit_margin.toFixed(2)}%</span>
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
