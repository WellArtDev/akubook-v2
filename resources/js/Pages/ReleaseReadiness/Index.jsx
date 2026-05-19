import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Index({ auth, checks, summary, generated_at }) {
    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold">Release Readiness</h2>}>
            <Head title="Release Readiness" />
            <div className="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <Card label="Total" value={summary.total} />
                    <Card label="Passed" value={summary.passed} color="text-green-600" />
                    <Card label="Failed" value={summary.failed} color="text-red-600" />
                </div>
                <div className="text-sm text-gray-500">Generated: {generated_at}</div>
                <div className="bg-white rounded shadow overflow-hidden">
                    <table className="min-w-full divide-y">
                        <thead className="bg-gray-50"><tr><th className="px-4 py-2 text-left">Type</th><th className="px-4 py-2 text-left">Key</th><th className="px-4 py-2 text-left">Check</th><th className="px-4 py-2 text-left">Status</th><th className="px-4 py-2 text-left">Note</th></tr></thead>
                        <tbody className="divide-y">
                            {checks.map((check, i) => (
                                <tr key={i}>
                                    <td className="px-4 py-2">{check.type}</td>
                                    <td className="px-4 py-2">{check.key}</td>
                                    <td className="px-4 py-2">{check.label}</td>
                                    <td className={`px-4 py-2 font-semibold ${check.passed ? 'text-green-600' : 'text-red-600'}`}>{check.passed ? 'PASS' : 'FAIL'}</td>
                                    <td className="px-4 py-2">{check.note}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function Card({ label, value, color = 'text-gray-900' }) {
    return (
        <div className="bg-white rounded shadow p-4">
            <div className="text-xs text-gray-500 uppercase">{label}</div>
            <div className={`text-2xl font-semibold ${color}`}>{value}</div>
        </div>
    );
}
