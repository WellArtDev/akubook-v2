import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Show({ auth, cashAccount }) {
    const destroy = () => {
        if (confirm('Hapus cash account?')) {
            router.delete(route('cash-accounts.destroy', cashAccount.id));
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`Cash Account ${cashAccount.code}`} />
            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                        <div className="flex justify-between items-center">
                            <h1 className="text-2xl font-semibold">Cash Account {cashAccount.code}</h1>
                            <div className="space-x-2">
                                <Link href={route('cash-accounts.index')} className="text-gray-600">Back</Link>
                                <Link href={route('cash-accounts.edit', cashAccount.id)} className="text-indigo-600">Edit</Link>
                                <button onClick={destroy} className="text-red-600">Delete</button>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <Info label="Code" value={cashAccount.code} />
                            <Info label="Name" value={cashAccount.name} />
                            <Info label="COA" value={`${cashAccount.account?.code || '-'} - ${cashAccount.account?.name || '-'}`} />
                            <Info label="Opening Balance" value={Number(cashAccount.opening_balance).toLocaleString()} />
                            <Info label="Status" value={cashAccount.is_active ? 'Active' : 'Inactive'} />
                            <Info label="Created By" value={cashAccount.creator?.name || '-'} />
                        </div>

                        <div>
                            <div className="text-sm font-medium text-gray-700">Description</div>
                            <div className="mt-1 text-sm text-gray-600">{cashAccount.description || '-'}</div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function Info({ label, value }) {
    return <div><div className="text-xs text-gray-500">{label}</div><div className="font-medium">{value}</div></div>;
}
