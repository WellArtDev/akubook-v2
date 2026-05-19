import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

export default function Index({ auth, filters, summary, auditByEntity, sensitiveByLevel, retentionByAction, generated_at }) {
  const updateFilter = (key, value) => {
    router.get(route('compliance-reports.index'), { ...filters, [key]: value }, { preserveState: true, replace: true });
  };

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Compliance Report</h2>}>
      <Head title="Compliance Report" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
          <div className="bg-white shadow-sm sm:rounded-lg p-4 grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
            <label className="text-sm text-gray-700">
              Dari
              <input type="date" className="mt-1 w-full border-gray-300 rounded-md" value={filters.date_from || ''} onChange={(e) => updateFilter('date_from', e.target.value)} />
            </label>
            <label className="text-sm text-gray-700">
              Sampai
              <input type="date" className="mt-1 w-full border-gray-300 rounded-md" value={filters.date_to || ''} onChange={(e) => updateFilter('date_to', e.target.value)} />
            </label>
            <div className="text-sm text-gray-500">Generated: {generated_at}</div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            <Card title="Audit Logs" value={summary.audit_logs} />
            <Card title="Sensitive Actions" value={summary.sensitive_actions} />
            <Card title="Retention Policies" value={summary.active_retention_policies} />
            <Card title="Approval Workflows" value={summary.active_approval_workflows} />
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <Breakdown title="Sensitive by Level" rows={sensitiveByLevel} labelKey="sensitivity_level" />
            <Breakdown title="Audit by Entity" rows={auditByEntity} labelKey="entity_type" />
            <Breakdown title="Retention by Action" rows={retentionByAction} labelKey="action" />
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}

function Card({ title, value }) {
  return (
    <div className="bg-white shadow-sm sm:rounded-lg p-4">
      <div className="text-sm text-gray-500">{title}</div>
      <div className="text-2xl font-semibold text-gray-900">{Number(value || 0).toLocaleString('id-ID')}</div>
    </div>
  );
}

function Breakdown({ title, rows, labelKey }) {
  return (
    <div className="bg-white shadow-sm sm:rounded-lg p-4">
      <h3 className="font-semibold text-gray-900 mb-3">{title}</h3>
      <table className="w-full text-sm">
        <tbody>
          {rows.map((row) => (
            <tr key={row[labelKey]} className="border-t">
              <td className="py-2 text-gray-700">{row[labelKey]}</td>
              <td className="py-2 text-right font-semibold">{Number(row.total || 0).toLocaleString('id-ID')}</td>
            </tr>
          ))}
          {rows.length === 0 && (
            <tr>
              <td className="py-6 text-center text-gray-500" colSpan={2}>Tidak ada data.</td>
            </tr>
          )}
        </tbody>
      </table>
    </div>
  );
}
