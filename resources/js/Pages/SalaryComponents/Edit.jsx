import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Edit({ auth, component, accounts, componentTypes, calculationMethods }) {
    const { data, setData, put, processing, errors } = useForm({
        code: component.code,
        name: component.name,
        component_type: component.component_type,
        calculation_method: component.calculation_method,
        default_amount: component.default_amount,
        default_percentage: component.default_percentage,
        is_taxable: component.is_taxable,
        is_active: component.is_active,
        account_id: component.account_id || '',
        description: component.description || '',
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('salary-components.update', component.id));
    };

    return <AuthenticatedLayout user={auth.user}><Head title="Edit Salary Component" /><div className="py-12"><div className="max-w-3xl mx-auto sm:px-6 lg:px-8"><form onSubmit={submit} className="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
        <div className="flex justify-between items-center"><h1 className="text-2xl font-semibold">Edit Salary Component</h1><Link href={route('salary-components.index')} className="text-gray-600">Back</Link></div>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <Input label="Code" value={data.code} onChange={(v) => setData('code', v)} error={errors.code} />
            <Input label="Name" value={data.name} onChange={(v) => setData('name', v)} error={errors.name} />
            <Select label="Component Type" value={data.component_type} onChange={(v) => setData('component_type', v)} error={errors.component_type} options={componentTypes.map((v) => ({ id: v, label: v }))} />
            <Select label="Calculation Method" value={data.calculation_method} onChange={(v) => setData('calculation_method', v)} error={errors.calculation_method} options={calculationMethods.map((v) => ({ id: v, label: v }))} />
            <Input label="Default Amount" type="number" value={data.default_amount} onChange={(v) => setData('default_amount', v)} error={errors.default_amount} />
            <Input label="Default Percentage" type="number" value={data.default_percentage} onChange={(v) => setData('default_percentage', v)} error={errors.default_percentage} />
            <div className="md:col-span-2"><Select label="Account (Optional)" value={data.account_id} onChange={(v) => setData('account_id', v)} error={errors.account_id} options={[{ id: '', label: '-' }, ...accounts.map((a) => ({ id: a.id, label: `${a.code} - ${a.name}` }))]} /></div>
        </div>
        <label className="flex items-center gap-2"><input type="checkbox" checked={data.is_taxable} onChange={(e) => setData('is_taxable', e.target.checked)} /> Taxable</label>
        <label className="flex items-center gap-2"><input type="checkbox" checked={data.is_active} onChange={(e) => setData('is_active', e.target.checked)} /> Active</label>
        <textarea value={data.description} onChange={(e) => setData('description', e.target.value)} className="border rounded px-3 py-2 w-full" placeholder="Description" />
        <button disabled={processing} className="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
    </form></div></div></AuthenticatedLayout>;
}

function Input({ label, value, onChange, error, type = 'text' }) { return <div><label className="block text-sm font-medium">{label}</label><input type={type} value={value} onChange={(e) => onChange(e.target.value)} className="border rounded px-3 py-2 w-full" />{error && <div className="text-red-600 text-sm">{error}</div>}</div>; }
function Select({ label, value, onChange, error, options }) { return <div><label className="block text-sm font-medium">{label}</label><select value={value} onChange={(e) => onChange(e.target.value)} className="border rounded px-3 py-2 w-full">{options.map((o) => <option key={o.id} value={o.id}>{o.label}</option>)}</select>{error && <div className="text-red-600 text-sm">{error}</div>}</div>; }
