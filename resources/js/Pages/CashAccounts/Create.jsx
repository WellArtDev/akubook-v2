import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Create({ auth, accounts }) {
    const { data, setData, post, processing, errors } = useForm({
        code: '',
        name: '',
        account_id: accounts[0]?.id || '',
        opening_balance: 0,
        is_active: true,
        description: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('cash-accounts.store'));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Create Cash Account" />
            <div className="py-12">
                <div className="max-w-3xl mx-auto sm:px-6 lg:px-8">
                    <form onSubmit={submit} className="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                        <div className="flex justify-between items-center">
                            <h1 className="text-2xl font-semibold">Create Cash Account</h1>
                            <Link href={route('cash-accounts.index')} className="text-gray-600">Back</Link>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <Input label="Code" value={data.code} onChange={(v) => setData('code', v)} error={errors.code} />
                            <Input label="Name" value={data.name} onChange={(v) => setData('name', v)} error={errors.name} />
                            <div>
                                <label className="block text-sm font-medium">COA Account</label>
                                <select value={data.account_id} onChange={(e) => setData('account_id', e.target.value)} className="border rounded px-3 py-2 w-full">
                                    {accounts.map((account) => <option key={account.id} value={account.id}>{account.code} - {account.name}</option>)}
                                </select>
                                {errors.account_id && <div className="text-red-600 text-sm">{errors.account_id}</div>}
                            </div>
                            <Input label="Opening Balance" type="number" value={data.opening_balance} onChange={(v) => setData('opening_balance', v)} error={errors.opening_balance} />
                        </div>
                        <label className="flex items-center gap-2">
                            <input type="checkbox" checked={data.is_active} onChange={(e) => setData('is_active', e.target.checked)} /> Active
                        </label>
                        <textarea value={data.description} onChange={(e) => setData('description', e.target.value)} className="border rounded px-3 py-2 w-full" placeholder="Description" />
                        <button disabled={processing} className="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function Input({ label, value, onChange, error, type = 'text' }) {
    return <div><label className="block text-sm font-medium">{label}</label><input type={type} value={value} onChange={(e) => onChange(e.target.value)} className="border rounded px-3 py-2 w-full" />{error && <div className="text-red-600 text-sm">{error}</div>}</div>;
}
