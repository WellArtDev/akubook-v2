import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Create({ auth }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        category: '',
        tax_id: '',
        tax_type: 'non_pkp',
        payment_terms: '',
        phone: '',
        email: '',
        website: '',
        notes: '',
        contacts: [],
        addresses: [],
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('suppliers.store'));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Create Supplier" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex justify-between items-center mb-6">
                                <h2 className="text-2xl font-semibold">Create Supplier</h2>
                                <Link
                                    href={route('suppliers.index')}
                                    className="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    Back
                                </Link>
                            </div>

                            <form onSubmit={handleSubmit}>
                                <div className="grid grid-cols-2 gap-6">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Name *</label>
                                        <input
                                            type="text"
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                            required
                                        />
                                        {errors.name && <p className="text-red-500 text-xs mt-1">{errors.name}</p>}
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Category</label>
                                        <select
                                            value={data.category}
                                            onChange={(e) => setData('category', e.target.value)}
                                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        >
                                            <option value="">Select Category</option>
                                            <option value="Raw Material">Raw Material</option>
                                            <option value="Packaging">Packaging</option>
                                            <option value="Service">Service</option>
                                            <option value="Equipment">Equipment</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Tax ID (NPWP)</label>
                                        <input
                                            type="text"
                                            value={data.tax_id}
                                            onChange={(e) => setData('tax_id', e.target.value)}
                                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Tax Type *</label>
                                        <select
                                            value={data.tax_type}
                                            onChange={(e) => setData('tax_type', e.target.value)}
                                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                            required
                                        >
                                            <option value="pkp">PKP</option>
                                            <option value="non_pkp">Non-PKP</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Payment Terms</label>
                                        <select
                                            value={data.payment_terms}
                                            onChange={(e) => setData('payment_terms', e.target.value)}
                                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        >
                                            <option value="">Select Terms</option>
                                            <option value="Net 0">Net 0</option>
                                            <option value="Net 7">Net 7</option>
                                            <option value="Net 14">Net 14</option>
                                            <option value="Net 30">Net 30</option>
                                            <option value="Net 45">Net 45</option>
                                            <option value="Net 60">Net 60</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Phone</label>
                                        <input
                                            type="text"
                                            value={data.phone}
                                            onChange={(e) => setData('phone', e.target.value)}
                                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Email</label>
                                        <input
                                            type="email"
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Website</label>
                                        <input
                                            type="url"
                                            value={data.website}
                                            onChange={(e) => setData('website', e.target.value)}
                                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        />
                                    </div>

                                    <div className="col-span-2">
                                        <label className="block text-sm font-medium text-gray-700">Notes</label>
                                        <textarea
                                            value={data.notes}
                                            onChange={(e) => setData('notes', e.target.value)}
                                            rows="3"
                                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        />
                                    </div>
                                </div>

                                <div className="mt-6 flex justify-end space-x-3">
                                    <Link
                                        href={route('suppliers.index')}
                                        className="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
                                    >
                                        Cancel
                                    </Link>
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                                    >
                                        Create Supplier
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
