import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

function SummaryCard({ label, value, danger = false }) {
    return (
        <div className={`rounded border p-4 ${danger ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-white'}`}>
            <div className="text-xs text-gray-500 uppercase tracking-wide">{label}</div>
            <div className={`text-2xl font-semibold ${danger ? 'text-red-700' : 'text-gray-900'}`}>{value}</div>
        </div>
    );
}

function RouteTable({ title, rows }) {
    return (
        <div className="bg-white rounded shadow overflow-hidden">
            <div className="px-4 py-3 border-b text-sm font-semibold">{title} ({rows.length})</div>
            <table className="min-w-full divide-y">
                <thead className="bg-gray-50">
                    <tr>
                        <th className="px-4 py-2 text-left">Name</th>
                        <th className="px-4 py-2 text-left">URI</th>
                        <th className="px-4 py-2 text-left">Methods</th>
                        <th className="px-4 py-2 text-left">Middleware</th>
                    </tr>
                </thead>
                <tbody className="divide-y">
                    {rows.length === 0 ? (
                        <tr><td className="px-4 py-3 text-sm text-gray-500" colSpan={4}>No rows</td></tr>
                    ) : rows.map((row, idx) => (
                        <tr key={`${row.uri}-${row.name}-${idx}`}>
                            <td className="px-4 py-2 text-sm">{row.name}</td>
                            <td className="px-4 py-2 text-sm">{row.uri}</td>
                            <td className="px-4 py-2 text-sm">{row.methods.join(', ')}</td>
                            <td className="px-4 py-2 text-sm">{row.middlewares.join(', ') || '-'}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

export default function Index({ auth, summary, allowedPublicRoutes, unexpectedPublicRoutes, unprotectedMutations }) {
    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Security & Permission Audit</h2>}>
            <Head title="Security Audit" />
            <div className="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                    <SummaryCard label="Total Routes" value={summary.total_routes} />
                    <SummaryCard label="Public Routes" value={summary.public_routes} />
                    <SummaryCard label="Auth Routes" value={summary.auth_routes} />
                    <SummaryCard label="Mutation Routes" value={summary.mutation_routes} />
                    <SummaryCard label="Unprotected Mutations" value={summary.unprotected_mutations} danger={summary.unprotected_mutations > 0} />
                    <SummaryCard label="Unexpected Public" value={summary.unexpected_public_routes} danger={summary.unexpected_public_routes > 0} />
                </div>

                <RouteTable title="Allowed Public Routes" rows={allowedPublicRoutes} />
                <RouteTable title="Unexpected Public Routes" rows={unexpectedPublicRoutes} />
                <RouteTable title="Unprotected Mutation Routes" rows={unprotectedMutations} />
            </div>
        </AuthenticatedLayout>
    );
}
