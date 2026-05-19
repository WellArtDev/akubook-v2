import React from 'react';
import { Head, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, taxConfigurations, history, filters, result, taxTypes }) {
    const submit = (e) => {
        e.preventDefault();
        router.get(route('tax-calculations.index'), Object.fromEntries(new FormData(e.target).entries()));
    };

    const configurations = taxConfigurations.filter((config) => config.tax_type === filters.tax_type);

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Tax Calculation" />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                        <div>
                            <h1 className="text-2xl font-semibold">Tax Calculation</h1>
                            <p className="text-sm text-gray-500">Kalkulasi pajak preview berdasarkan tax configuration aktif.</p>
                        </div>

                        <form onSubmit={submit} className="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                            <div>
                                <label className="block text-sm mb-1">Tax Type</label>
                                <select name="tax_type" defaultValue={filters.tax_type} className="w-full border rounded px-3 py-2">
                                    {Object.entries(taxTypes).map(([key, label]) => <option key={key} value={key}>{label}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm mb-1">Configuration</label>
                                <select name="tax_configuration_id" defaultValue={filters.tax_configuration_id || ''} className="w-full border rounded px-3 py-2">
                                    <option value="">Default Active</option>
                                    {configurations.map((config) => (
                                        <option key={config.id} value={config.id}>{config.code} - {config.name} ({Number(config.rate).toLocaleString()}%)</option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm mb-1">Taxable Amount</label>
                                <input name="taxable_amount" type="number" step="0.01" min="0" defaultValue={filters.taxable_amount || ''} className="w-full border rounded px-3 py-2" />
                            </div>
                            <label className="inline-flex items-center gap-2 md:mb-2">
                                <input name="is_inclusive" type="checkbox" value="1" defaultChecked={filters.is_inclusive} />
                                <span className="text-sm">Tax Inclusive</span>
                            </label>
                            <button className="bg-blue-600 text-white px-4 py-2 rounded">Calculate</button>
                        </form>

                        {result && (
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
                                <div className="border rounded p-3"><div className="text-gray-500">DPP</div><div className="font-semibold">{Number(result.dpp).toLocaleString()}</div></div>
                                <div className="border rounded p-3"><div className="text-gray-500">Tax Amount</div><div className="font-semibold">{Number(result.tax_amount).toLocaleString()}</div></div>
                                <div className="border rounded p-3"><div className="text-gray-500">Grand Total</div><div className="font-semibold">{Number(result.grand_total).toLocaleString()}</div></div>
                                <div className="border rounded p-3"><div className="text-gray-500">Rate</div><div className="font-semibold">{Number(result.rate).toLocaleString()}%</div></div>
                            </div>
                        )}

                        <div>
                            <h2 className="text-lg font-semibold mb-2">Calculation History</h2>
                            <table className="min-w-full text-sm">
                                <thead>
                                    <tr className="border-b">
                                        <th className="text-left py-2">Time</th>
                                        <th className="text-left py-2">Type</th>
                                        <th className="text-right py-2">Taxable</th>
                                        <th className="text-right py-2">Rate</th>
                                        <th className="text-right py-2">Tax</th>
                                        <th className="text-right py-2">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {history.map((row) => (
                                        <tr key={row.id} className="border-b">
                                            <td className="py-2">{new Date(row.created_at).toLocaleString()}</td>
                                            <td className="py-2">{taxTypes[row.tax_type]}</td>
                                            <td className="py-2 text-right">{Number(row.taxable_amount).toLocaleString()}</td>
                                            <td className="py-2 text-right">{Number(row.rate).toLocaleString()}%</td>
                                            <td className="py-2 text-right">{Number(row.tax_amount).toLocaleString()}</td>
                                            <td className="py-2 text-right">{Number(row.grand_total).toLocaleString()}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
