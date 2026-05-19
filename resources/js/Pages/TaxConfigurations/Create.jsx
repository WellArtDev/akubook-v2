import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Create({ auth, accounts, taxTypes }) {
    const { data, setData, post, processing, errors } = useForm({ code: '', name: '', tax_type: 'ppn_out', rate: 11, account_id: accounts[0]?.id || '', is_default: false, is_active: true, description: '' });
    const submit = (e) => { e.preventDefault(); post(route('tax-configurations.store')); };
    return <Form auth={auth} title="Create Tax Configuration" data={data} setData={setData} submit={submit} processing={processing} errors={errors} accounts={accounts} taxTypes={taxTypes} back={route('tax-configurations.index')} button="Save" />;
}

export function Form({ auth, title, data, setData, submit, processing, errors, accounts, taxTypes, back, button }) {
    return <AuthenticatedLayout user={auth.user}><Head title={title} /><div className="py-12"><div className="max-w-3xl mx-auto sm:px-6 lg:px-8"><form onSubmit={submit} className="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
        <div className="flex justify-between items-center"><h1 className="text-2xl font-semibold">{title}</h1><Link href={back} className="text-gray-600">Back</Link></div>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <Input label="Code" value={data.code} onChange={(v) => setData('code', v)} error={errors.code} />
            <Input label="Name" value={data.name} onChange={(v) => setData('name', v)} error={errors.name} />
            <Select label="Tax Type" value={data.tax_type} onChange={(v) => setData('tax_type', v)} error={errors.tax_type} options={Object.entries(taxTypes).map(([id, label]) => ({ id, label }))} />
            <Input label="Rate %" type="number" value={data.rate} onChange={(v) => setData('rate', v)} error={errors.rate} />
            <div className="md:col-span-2"><Select label="Tax Account" value={data.account_id} onChange={(v) => setData('account_id', v)} error={errors.account_id} options={accounts.map((a) => ({ id: a.id, label: `${a.code} - ${a.name}` }))} /></div>
        </div>
        <label className="flex items-center gap-2"><input type="checkbox" checked={data.is_default} onChange={(e) => setData('is_default', e.target.checked)} /> Default</label>
        <label className="flex items-center gap-2"><input type="checkbox" checked={data.is_active} onChange={(e) => setData('is_active', e.target.checked)} /> Active</label>
        <textarea value={data.description} onChange={(e) => setData('description', e.target.value)} className="border rounded px-3 py-2 w-full" placeholder="Description" />
        <button disabled={processing} className="bg-blue-600 text-white px-4 py-2 rounded">{button}</button>
    </form></div></div></AuthenticatedLayout>;
}

function Input({ label, value, onChange, error, type = 'text' }) { return <div><label className="block text-sm font-medium">{label}</label><input type={type} value={value} onChange={(e) => onChange(e.target.value)} className="border rounded px-3 py-2 w-full" />{error && <div className="text-red-600 text-sm">{error}</div>}</div>; }
function Select({ label, value, onChange, error, options }) { return <div><label className="block text-sm font-medium">{label}</label><select value={value} onChange={(e) => onChange(e.target.value)} className="border rounded px-3 py-2 w-full">{options.map((o) => <option key={o.id} value={o.id}>{o.label}</option>)}</select>{error && <div className="text-red-600 text-sm">{error}</div>}</div>; }
