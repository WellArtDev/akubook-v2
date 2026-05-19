import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Show({ auth, customer }) {
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`Customer: ${customer.name}`} />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex justify-between items-center mb-6">
                                <h2 className="text-2xl font-semibold">Customer Details</h2>
                                <div className="space-x-2">
                                    <Link href={route('customers.edit', customer.id)} className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Edit</Link>
                                    <Link href={route('customers.index')} className="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back</Link>
                                </div>
                            </div>
                            <div className="grid grid-cols-2 gap-6 mb-6">
                                <Info label="Customer Code" value={customer.code} />
                                <Info label="Name" value={customer.name} />
                                <Info label="Category" value={customer.category} capitalize />
                                <Info label="Tax Type" value={customer.tax_type === 'pkp' ? 'PKP' : 'Non-PKP'} />
                                <Info label="Tax ID" value={customer.tax_id || '-'} />
                                <Info label="Phone" value={customer.phone} />
                                <Info label="Email" value={customer.email || '-'} />
                                <Info label="Website" value={customer.website || '-'} />
                            </div>
                            <div className="grid grid-cols-4 gap-4 mb-6">
                                <Metric label="Credit Limit" value={`Rp ${Number(customer.credit_limit).toLocaleString()}`} />
                                <Metric label="Outstanding" value={`Rp ${Number(customer.outstanding_balance).toLocaleString()}`} />
                                <Metric label="Available" value={`Rp ${Number(customer.available_credit).toLocaleString()}`} />
                                <Metric label="Credit Status" value={customer.credit_status} capitalize />
                            </div>
                            <List title="Contacts" items={customer.contacts} empty="No contacts" render={(contact) => <div><div className="flex justify-between"><div><p className="font-medium">{contact.name}</p>{contact.position && <p className="text-sm text-gray-600">{contact.position}</p>}</div>{contact.is_primary && <span className="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Primary</span>}</div><div className="mt-2 text-sm"><p>Phone: {contact.phone}</p>{contact.email && <p>Email: {contact.email}</p>}</div></div>} />
                            <List title="Addresses" items={customer.addresses} empty="No addresses" render={(address) => <div><div className="flex justify-between mb-2"><span className="text-sm font-medium capitalize">{address.address_type}</span>{address.is_default && <span className="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Default</span>}</div><p className="text-sm">{address.street_address}</p><p className="text-sm">{address.city}, {address.province} {address.postal_code}</p><p className="text-sm">{address.country}</p></div>} />
                            <List title="Recent Sales Orders" items={customer.sales_orders || []} empty="No recent sales orders" render={(order) => <div className="text-sm"><p className="font-medium">{order.order_number || order.id}</p><p>Status: {order.status}</p></div>} />
                            <List title="Outstanding Invoices" items={(customer.sales_invoices || []).filter((invoice) => Number(invoice.outstanding_amount || 0) > 0)} empty="No outstanding invoices" render={(invoice) => <div className="text-sm"><p className="font-medium">{invoice.invoice_number || invoice.id}</p><p>Outstanding: Rp {Number(invoice.outstanding_amount || 0).toLocaleString()}</p></div>} />
                            <List title="Payment History" items={customer.payments || []} empty="No payments" render={(payment) => <div className="text-sm"><p className="font-medium">{payment.payment_number || payment.id}</p><p>Amount: Rp {Number(payment.amount || 0).toLocaleString()}</p></div>} />
                            {customer.notes && <div><label className="block text-sm font-medium text-gray-700">Notes</label><p className="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{customer.notes}</p></div>}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function Info({ label, value, capitalize }) {
    return <div><label className="block text-sm font-medium text-gray-700">{label}</label><p className={`mt-1 text-sm text-gray-900 ${capitalize ? 'capitalize' : ''}`}>{value}</p></div>;
}

function Metric({ label, value, capitalize }) {
    return <div className="border rounded p-3"><label className="block text-sm font-medium text-gray-700">{label}</label><p className={`mt-1 text-lg font-semibold ${capitalize ? 'capitalize' : ''}`}>{value}</p></div>;
}

function List({ title, items, empty, render }) {
    return <div className="mb-6"><h3 className="text-lg font-semibold mb-3">{title}</h3>{items.length === 0 ? <p className="text-sm text-gray-500">{empty}</p> : <div className="space-y-3">{items.map((item) => <div key={item.id} className="border rounded p-3">{render(item)}</div>)}</div>}</div>;
}
