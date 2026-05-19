import React from 'react';
import { useForm } from '@inertiajs/react';
import { Form } from './Create';

export default function Edit({ auth, taxConfiguration, accounts, taxTypes }) {
    const { data, setData, put, processing, errors } = useForm({
        code: taxConfiguration.code,
        name: taxConfiguration.name,
        tax_type: taxConfiguration.tax_type,
        rate: taxConfiguration.rate,
        account_id: taxConfiguration.account_id,
        is_default: taxConfiguration.is_default,
        is_active: taxConfiguration.is_active,
        description: taxConfiguration.description || '',
    });
    const submit = (e) => { e.preventDefault(); put(route('tax-configurations.update', taxConfiguration.id)); };
    return <Form auth={auth} title="Edit Tax Configuration" data={data} setData={setData} submit={submit} processing={processing} errors={errors} accounts={accounts} taxTypes={taxTypes} back={route('tax-configurations.show', taxConfiguration.id)} button="Update" />;
}
