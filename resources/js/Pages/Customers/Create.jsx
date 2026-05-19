import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

const emptyContact = { name: '', position: '', phone: '', email: '', is_primary: false };
const emptyAddress = { address_type: 'both', street_address: '', city: '', province: '', postal_code: '', country: 'Indonesia', is_default: false };

export default function Create({ auth, customer }) {
    const isEdit = Boolean(customer);
    const { data, setData, post, put, processing, errors } = useForm({
        name: customer?.name || '',
        category: customer?.category || 'retail',
        tax_id: customer?.tax_id || '',
        tax_type: customer?.tax_type || 'non_pkp',
        phone: customer?.phone || '',
        email: customer?.email || '',
        website: customer?.website || '',
        credit_limit: customer?.credit_limit || 0,
        payment_terms: customer?.payment_terms || 0,
        notes: customer?.notes || '',
        contacts: customer?.contacts?.length ? customer.contacts : [{ ...emptyContact, is_primary: true }],
        addresses: customer?.addresses?.length ? customer.addresses : [{ ...emptyAddress, is_default: true }],
    });

    const updateNested = (group, index, key, value) => {
        setData(group, data[group].map((item, itemIndex) => itemIndex === index ? { ...item, [key]: value } : item));
    };

    const removeNested = (group, index) => {
        setData(group, data[group].filter((_, itemIndex) => itemIndex !== index));
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        isEdit ? put(route('customers.update', customer.id)) : post(route('customers.store'));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={isEdit ? 'Edit Customer' : 'Create Customer'} />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex justify-between items-center mb-6">
                                <h2 className="text-2xl font-semibold">{isEdit ? 'Edit Customer' : 'Create Customer'}</h2>
                                <Link href={route('customers.index')} className="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back</Link>
                            </div>
                            <form onSubmit={handleSubmit}>
                                <div className="grid grid-cols-2 gap-6">
                                    <Field label="Name *" error={errors.name}><input value={data.name} onChange={(e) => setData('name', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required /></Field>
                                    <Field label="Category"><select value={data.category} onChange={(e) => setData('category', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"><option value="retail">Retail</option><option value="wholesale">Wholesale</option><option value="corporate">Corporate</option></select></Field>
                                    <Field label="Tax ID (NPWP)"><input value={data.tax_id} onChange={(e) => setData('tax_id', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm" /></Field>
                                    <Field label="Tax Type"><select value={data.tax_type} onChange={(e) => setData('tax_type', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"><option value="pkp">PKP</option><option value="non_pkp">Non-PKP</option></select></Field>
                                    <Field label="Phone *" error={errors.phone}><input value={data.phone} onChange={(e) => setData('phone', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required /></Field>
                                    <Field label="Email"><input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm" /></Field>
                                    <Field label="Website"><input type="url" value={data.website} onChange={(e) => setData('website', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm" /></Field>
                                    <Field label="Credit Limit"><input type="number" min="0" value={data.credit_limit} onChange={(e) => setData('credit_limit', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm" /></Field>
                                    <Field label="Payment Terms"><select value={data.payment_terms} onChange={(e) => setData('payment_terms', Number(e.target.value))} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{[0, 7, 14, 30, 45, 60].map((term) => <option key={term} value={term}>Net {term}</option>)}</select></Field>
                                    <div className="col-span-2"><Field label="Notes"><textarea value={data.notes} onChange={(e) => setData('notes', e.target.value)} rows="3" className="mt-1 block w-full border-gray-300 rounded-md shadow-sm" /></Field></div>
                                </div>
                                <NestedSection title="Contacts" items={data.contacts} add={() => setData('contacts', [...data.contacts, emptyContact])} remove={(index) => removeNested('contacts', index)} render={(contact, index) => <div className="grid grid-cols-5 gap-3"><input placeholder="Name" value={contact.name} onChange={(e) => updateNested('contacts', index, 'name', e.target.value)} className="border rounded px-3 py-2" /><input placeholder="Position" value={contact.position || ''} onChange={(e) => updateNested('contacts', index, 'position', e.target.value)} className="border rounded px-3 py-2" /><input placeholder="Phone" value={contact.phone} onChange={(e) => updateNested('contacts', index, 'phone', e.target.value)} className="border rounded px-3 py-2" /><input placeholder="Email" value={contact.email || ''} onChange={(e) => updateNested('contacts', index, 'email', e.target.value)} className="border rounded px-3 py-2" /><label className="flex items-center gap-2"><input type="checkbox" checked={contact.is_primary || false} onChange={(e) => updateNested('contacts', index, 'is_primary', e.target.checked)} /> Primary</label></div>} />
                                <NestedSection title="Addresses" items={data.addresses} add={() => setData('addresses', [...data.addresses, emptyAddress])} remove={(index) => removeNested('addresses', index)} render={(address, index) => <div className="grid grid-cols-4 gap-3"><select value={address.address_type} onChange={(e) => updateNested('addresses', index, 'address_type', e.target.value)} className="border rounded px-3 py-2"><option value="billing">Billing</option><option value="shipping">Shipping</option><option value="both">Both</option></select><input placeholder="Street" value={address.street_address} onChange={(e) => updateNested('addresses', index, 'street_address', e.target.value)} className="border rounded px-3 py-2" /><input placeholder="City" value={address.city} onChange={(e) => updateNested('addresses', index, 'city', e.target.value)} className="border rounded px-3 py-2" /><input placeholder="Province" value={address.province} onChange={(e) => updateNested('addresses', index, 'province', e.target.value)} className="border rounded px-3 py-2" /><input placeholder="Postal Code" value={address.postal_code || ''} onChange={(e) => updateNested('addresses', index, 'postal_code', e.target.value)} className="border rounded px-3 py-2" /><input placeholder="Country" value={address.country} onChange={(e) => updateNested('addresses', index, 'country', e.target.value)} className="border rounded px-3 py-2" /><label className="flex items-center gap-2"><input type="checkbox" checked={address.is_default || false} onChange={(e) => updateNested('addresses', index, 'is_default', e.target.checked)} /> Default</label></div>} />
                                <div className="mt-6 flex justify-end space-x-3"><Link href={route('customers.index')} className="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Cancel</Link><button type="submit" disabled={processing} className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50">{isEdit ? 'Update Customer' : 'Create Customer'}</button></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function Field({ label, error, children }) {
    return <div><label className="block text-sm font-medium text-gray-700">{label}</label>{children}{error && <p className="text-red-500 text-xs mt-1">{error}</p>}</div>;
}

function NestedSection({ title, items, add, remove, render }) {
    return <div className="mt-8"><div className="flex justify-between mb-3"><h3 className="text-lg font-semibold">{title}</h3><button type="button" onClick={add} className="text-blue-600">Add {title.slice(0, -1)}</button></div>{items.map((item, index) => <div key={index} className="border rounded p-3 mb-3">{render(item, index)}<button type="button" onClick={() => remove(index)} className="mt-2 text-red-600">Remove</button></div>)}</div>;
}
