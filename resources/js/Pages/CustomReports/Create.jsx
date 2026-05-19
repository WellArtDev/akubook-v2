import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export function CustomReportForm({ auth, sources, report = null, submitRoute, method = 'post' }) {
    const selectedSource = sources.find((source) => source.key === (report?.source_key || 'employees')) || sources[0];
    const form = useForm({
        code: report?.code || '',
        name: report?.name || '',
        source_key: report?.source_key || selectedSource?.key || 'employees',
        selected_columns: report?.selected_columns || selectedSource?.columns?.slice(0, 3) || [],
        default_filters: report?.default_filters || {},
        is_active: report?.is_active ?? true,
        description: report?.description || '',
    });

    const sourceMeta = sources.find((source) => source.key === form.data.source_key) || selectedSource;

    const toggleColumn = (column) => {
        if (form.data.selected_columns.includes(column)) {
            form.setData('selected_columns', form.data.selected_columns.filter((value) => value !== column));
            return;
        }
        form.setData('selected_columns', [...form.data.selected_columns, column]);
    };

    const submit = (e) => {
        e.preventDefault();
        if (method === 'put') form.put(submitRoute);
        else form.post(submitRoute);
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Custom Report Form</h2>}>
            <Head title="Custom Report Form" />
            <div className="py-6 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <form onSubmit={submit} className="bg-white rounded shadow p-6 space-y-4">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input className="border rounded px-3 py-2" placeholder="Code" value={form.data.code} onChange={(e) => form.setData('code', e.target.value)} />
                        <input className="border rounded px-3 py-2" placeholder="Name" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} />
                    </div>
                    <select className="border rounded px-3 py-2 w-full" value={form.data.source_key} onChange={(e) => { const sourceKey = e.target.value; const source = sources.find((item) => item.key === sourceKey); form.setData('source_key', sourceKey); form.setData('selected_columns', source ? source.columns.slice(0, 3) : []); }}>
                        {sources.map((source) => <option key={source.key} value={source.key}>{source.label}</option>)}
                    </select>
                    <div>
                        <div className="text-sm font-medium mb-2">Selected Columns</div>
                        <div className="grid grid-cols-2 md:grid-cols-3 gap-2">
                            {sourceMeta.columns.map((column) => (
                                <label key={column} className="inline-flex items-center gap-2 text-sm border rounded px-2 py-1">
                                    <input type="checkbox" checked={form.data.selected_columns.includes(column)} onChange={() => toggleColumn(column)} />
                                    <span>{column}</span>
                                </label>
                            ))}
                        </div>
                    </div>
                    <textarea className="border rounded px-3 py-2 w-full" placeholder="Description" value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} />
                    <label className="inline-flex items-center gap-2"><input type="checkbox" checked={form.data.is_active} onChange={(e) => form.setData('is_active', e.target.checked)} /> Aktif</label>
                    {Object.values(form.errors).length > 0 && <div className="text-sm text-red-600">{Object.values(form.errors).join(' ')}</div>}
                    <button className="px-4 py-2 bg-indigo-600 text-white rounded" disabled={form.processing}>Simpan</button>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}

export default function Create({ auth, sources }) {
    return <CustomReportForm auth={auth} sources={sources} submitRoute={route('custom-reports.store')} />;
}
