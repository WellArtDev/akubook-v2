import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export default function RoleIndex({ auth, role, widgets, generated_at, refresh_seconds, auto_refresh_enabled, refresh_options }) {
    const [payload, setPayload] = useState({ role, widgets, generated_at, refresh_seconds, auto_refresh_enabled, refresh_options });
    const [loading, setLoading] = useState(false);
    const [saving, setSaving] = useState(false);

    const refresh = async () => {
        setLoading(true);
        try {
            const response = await fetch(route('role-dashboard.metrics'), { headers: { Accept: 'application/json' } });
            setPayload(await response.json());
        } finally {
            setLoading(false);
        }
    };

    const savePreference = async (changes) => {
        const next = { ...payload, ...changes };
        setPayload(next);
        setSaving(true);
        try {
            const response = await fetch(route('role-dashboard.preference'), {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({
                    refresh_seconds: Number(next.refresh_seconds || 60),
                    auto_refresh_enabled: Boolean(next.auto_refresh_enabled),
                }),
            });
            const preference = await response.json();
            setPayload((current) => ({ ...current, ...preference }));
        } finally {
            setSaving(false);
        }
    };

    useEffect(() => {
        if (!payload.auto_refresh_enabled) return undefined;
        const interval = window.setInterval(refresh, (payload.refresh_seconds || 60) * 1000);
        return () => window.clearInterval(interval);
    }, [payload.refresh_seconds, payload.auto_refresh_enabled]);

    const widgetHref = (widget) => widget.widget_key ? route('role-dashboard.drilldown', widget.widget_key) : route(widget.route);

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Role Dashboard</h2>}>
            <Head title="Role Dashboard" />
            <div className="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div className="bg-white rounded shadow p-4 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                    <div>
                        <div className="text-sm text-gray-500">Role Profile</div>
                        <div className="text-lg font-semibold uppercase">{payload.role}</div>
                        <div className="text-xs text-gray-500">Last refresh: {payload.generated_at || '-'}</div>
                    </div>
                    <div className="flex flex-wrap items-center gap-3">
                        <label className="text-sm flex items-center gap-2">
                            <input type="checkbox" checked={Boolean(payload.auto_refresh_enabled)} onChange={(e) => savePreference({ auto_refresh_enabled: e.target.checked })} /> Auto
                        </label>
                        <select className="border rounded px-3 py-2" value={payload.refresh_seconds || 60} onChange={(e) => savePreference({ refresh_seconds: Number(e.target.value) })}>
                            {(payload.refresh_options || [15, 30, 60, 120, 300]).map((seconds) => <option key={seconds} value={seconds}>{seconds}s</option>)}
                        </select>
                        <button onClick={refresh} disabled={loading} className="px-4 py-2 bg-indigo-600 text-white rounded">{loading ? 'Refreshing...' : 'Refresh'}</button>
                        {saving && <span className="text-xs text-gray-500">Saving...</span>}
                    </div>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {payload.widgets.map((widget) => (
                        <Link key={widget.title} href={widgetHref(widget)} className="bg-white rounded shadow p-4 block hover:bg-gray-50">
                            <div className="text-sm text-gray-500">{widget.title}</div>
                            <div className="text-2xl font-bold">{Number(widget.value || 0).toLocaleString('id-ID')}</div>
                            <div className="text-xs text-indigo-600 mt-2">Drill down</div>
                        </Link>
                    ))}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
