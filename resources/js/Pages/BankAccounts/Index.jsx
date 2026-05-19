import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, bankAccounts, filters }) {
    const submit = (e) => {
        e.preventDefault();
        const form = new FormData(e.target);
        router.get(route('bank-accounts.index'), {
            search: form.get('search') || '',
            is_active: form.get('is_active') || '',
        });
    };

    const destroy = (id) => {
        if (confirm('Hapus bank account?')) {
            router.delete(route('bank-accounts.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Bank Accounts" />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white shadow-sm sm:rounded-lg p-6">
                        <div className="flex justify-between items-center mb-6">
                            <h1 className="text-2xl font-semibold">Bank Accounts</h1>
                            <Link href={route('bank-accounts.create')} className="bg-blue-600 text-white px-4 py-2 rounded">Add Bank Account</Link>
                        </div>

                        <form onSubmit={submit} className="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
                            <input name="search" defaultValue={filters.search || ''} placeholder="Search" className="border rounded px-3 py-2" />
                            <select name="is_active" defaultValue={filters.is_active || ''} className="border rounded px-3 py-2">
                                <option value="">All Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <button className="bg-gray-700 text-white px-4 py-2 rounded">Filter</button>
                        </form>

                        <table className="min-w-full text-sm">
                            <thead>
                                <tr className="border-b">
                                    <th className="text-left py-2">Code</th>
                                    <th className="text-left py-2">Name</th>
                                    <th className="text-left py-2">Bank</th>
                                    <th className="text-left py-2">Account Number</th>
                                    <th className="text-left py-2">COA</th>
                                    <th className="text-right py-2">Opening Balance</th>
                                    <th className="text-left py-2">Status</th>
                                    <th className="text-right py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {bankAccounts.data.map((bankAccount) => (
                                    <tr key={bankAccount.id} className="border-b">
                                        <td className="py-2">{bankAccount.code}</td>
                                        <td className="py-2">{bankAccount.name}</td>
                                        <td className="py-2">{bankAccount.bank_name}</td>
                                        <td className="py-2">{bankAccount.account_number}</td>
                                        <td className="py-2">{bankAccount.account?.code} - {bankAccount.account?.name}</td>
                                        <td className="py-2 text-right">{Number(bankAccount.opening_balance).toLocaleString()}</td>
                                        <td className="py-2">{bankAccount.is_active ? 'Active' : 'Inactive'}</td>
                                        <td className="py-2 text-right space-x-2">
                                            <Link href={route('bank-accounts.show', bankAccount.id)} className="text-blue-600">View</Link>
                                            <Link href={route('bank-accounts.edit', bankAccount.id)} className="text-indigo-600">Edit</Link>
                                            <button onClick={() => destroy(bankAccount.id)} className="text-red-600">Delete</button>
                                        </td>
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
