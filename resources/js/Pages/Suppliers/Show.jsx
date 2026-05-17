import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Show({ auth, supplier }) {
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`Supplier: ${supplier.name}`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex justify-between items-center mb-6">
                                <h2 className="text-2xl font-semibold">Supplier Details</h2>
                                <div className="space-x-2">
                                    <Link
                                        href={route('suppliers.edit', supplier.id)}
                                        className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                    >
                                        Edit
                                    </Link>
                                    <Link
                                        href={route('suppliers.index')}
                                        className="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                                    >
                                        Back
                                    </Link>
                                </div>
                            </div>

                            {/* Basic Info */}
                            <div className="grid grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Supplier Code</label>
                                    <p className="mt-1 text-sm text-gray-900">{supplier.supplier_code}</p>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Name</label>
                                    <p className="mt-1 text-sm text-gray-900">{supplier.name}</p>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Category</label>
                                    <p className="mt-1 text-sm text-gray-900">{supplier.category || '-'}</p>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Tax Type</label>
                                    <p className="mt-1 text-sm text-gray-900">
                                        <span className={`px-2 py-1 rounded text-xs ${
                                            supplier.tax_type === 'pkp' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                                        }`}>
                                            {supplier.tax_type === 'pkp' ? 'PKP' : 'Non-PKP'}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Tax ID</label>
                                    <p className="mt-1 text-sm text-gray-900">{supplier.tax_id || '-'}</p>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Payment Terms</label>
                                    <p className="mt-1 text-sm text-gray-900">{supplier.payment_terms || '-'}</p>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Phone</label>
                                    <p className="mt-1 text-sm text-gray-900">{supplier.phone || '-'}</p>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Email</label>
                                    <p className="mt-1 text-sm text-gray-900">{supplier.email || '-'}</p>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Website</label>
                                    <p className="mt-1 text-sm text-gray-900">
                                        {supplier.website ? (
                                            <a href={supplier.website} target="_blank" rel="noopener noreferrer" className="text-blue-600 hover:underline">
                                                {supplier.website}
                                            </a>
                                        ) : '-'}
                                    </p>
                                </div>
                            </div>

                            {/* Contacts */}
                            {supplier.contacts && supplier.contacts.length > 0 && (
                                <div className="mb-6">
                                    <h3 className="text-lg font-semibold mb-3">Contacts</h3>
                                    <div className="space-y-3">
                                        {supplier.contacts.map((contact) => (
                                            <div key={contact.id} className="border rounded p-3">
                                                <div className="flex justify-between">
                                                    <div>
                                                        <p className="font-medium">{contact.name}</p>
                                                        {contact.position && <p className="text-sm text-gray-600">{contact.position}</p>}
                                                    </div>
                                                    {contact.is_primary && (
                                                        <span className="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Primary</span>
                                                    )}
                                                </div>
                                                <div className="mt-2 text-sm">
                                                    <p>Phone: {contact.phone}</p>
                                                    {contact.email && <p>Email: {contact.email}</p>}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {/* Addresses */}
                            {supplier.addresses && supplier.addresses.length > 0 && (
                                <div className="mb-6">
                                    <h3 className="text-lg font-semibold mb-3">Addresses</h3>
                                    <div className="space-y-3">
                                        {supplier.addresses.map((address) => (
                                            <div key={address.id} className="border rounded p-3">
                                                <div className="flex justify-between mb-2">
                                                    <span className="text-sm font-medium capitalize">{address.address_type}</span>
                                                    {address.is_default && (
                                                        <span className="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Default</span>
                                                    )}
                                                </div>
                                                <p className="text-sm">{address.street_address}</p>
                                                <p className="text-sm">{address.city}, {address.province} {address.postal_code}</p>
                                                <p className="text-sm">{address.country}</p>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {/* Performance Metrics */}
                            <div className="grid grid-cols-4 gap-4 mb-6">
                                <div className="border rounded p-3">
                                    <label className="block text-sm font-medium text-gray-700">Delivery Rating</label>
                                    <p className="mt-1 text-lg font-semibold">{supplier.delivery_rating}/5.00</p>
                                </div>
                                <div className="border rounded p-3">
                                    <label className="block text-sm font-medium text-gray-700">Quality Rating</label>
                                    <p className="mt-1 text-lg font-semibold">{supplier.quality_rating}/5.00</p>
                                </div>
                                <div className="border rounded p-3">
                                    <label className="block text-sm font-medium text-gray-700">Total Purchase</label>
                                    <p className="mt-1 text-lg font-semibold">Rp {Number(supplier.total_purchase_amount).toLocaleString()}</p>
                                </div>
                                <div className="border rounded p-3">
                                    <label className="block text-sm font-medium text-gray-700">Last Purchase</label>
                                    <p className="mt-1 text-sm">{supplier.last_purchase_date || '-'}</p>
                                </div>
                            </div>

                            {/* Notes */}
                            {supplier.notes && (
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Notes</label>
                                    <p className="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{supplier.notes}</p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
