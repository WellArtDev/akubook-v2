import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Index({ auth, alerts }) {
  const { post, processing } = useForm({ threshold: 3, window_minutes: 60 });
  const formatDate = (value) => (value ? new Date(value).toLocaleString('id-ID') : '-');

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Sensitive Alerts</h2>}>
      <Head title="Sensitive Alerts" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 flex items-center justify-between">
            <div>
              <h3 className="font-semibold text-gray-900">High Sensitivity Alert Batch</h3>
              <p className="text-sm text-gray-500">Threshold default: 3 event high dalam 60 menit.</p>
            </div>
            <button
              type="button"
              onClick={() => post(route('sensitive-alerts.store'))}
              disabled={processing}
              className="px-4 py-2 bg-red-600 text-white rounded-md disabled:opacity-50"
            >
              Generate Alert
            </button>
          </div>

          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200 text-sm">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-4 py-2 text-left">Generated</th>
                    <th className="px-4 py-2 text-left">Window</th>
                    <th className="px-4 py-2 text-left">Count</th>
                    <th className="px-4 py-2 text-left">Threshold</th>
                    <th className="px-4 py-2 text-left">Top Entities</th>
                    <th className="px-4 py-2 text-left">Status</th>
                    <th className="px-4 py-2 text-left">Generator</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {alerts.data.map((alert) => (
                    <tr key={alert.id}>
                      <td className="px-4 py-2 whitespace-nowrap">{formatDate(alert.generated_at)}</td>
                      <td className="px-4 py-2">{alert.window}</td>
                      <td className="px-4 py-2">{alert.high_count}</td>
                      <td className="px-4 py-2">{alert.threshold}</td>
                      <td className="px-4 py-2">
                        {(alert.top_entities || []).map((entity) => `${entity.entity_type}: ${entity.count}`).join(', ') || '-'}
                      </td>
                      <td className="px-4 py-2">
                        <span className="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">{alert.status}</span>
                      </td>
                      <td className="px-4 py-2">{alert.generator?.name || '-'}</td>
                    </tr>
                  ))}
                  {alerts.data.length === 0 && (
                    <tr>
                      <td colSpan={7} className="px-4 py-6 text-center text-gray-500">Belum ada sensitive alert.</td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
