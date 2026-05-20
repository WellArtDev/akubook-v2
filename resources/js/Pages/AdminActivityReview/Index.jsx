import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

export default function Index({ auth, activities, kpis, filters, users, eventKeys, entityTypes, levels }) {
  const updateFilter = (key, value) => {
    router.get(route('admin-activity-review.index'), { ...filters, [key]: value }, { preserveState: true, replace: true });
  };

  const formatDate = (value) => (value ? new Date(value).toLocaleString('id-ID') : '-');

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Admin Activity Review</h2>}>
      <Head title="Admin Activity Review" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <Card title="Sensitive Actions" value={kpis.sensitive_total} />
            <Card title="High Severity" value={kpis.high_severity_total} />
            <Card title="Failed / Blocked" value={kpis.failed_or_blocked_total} />
          </div>

          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 grid grid-cols-1 md:grid-cols-6 gap-3">
            <select className="border-gray-300 rounded-md" value={filters.event_key || ''} onChange={(e) => updateFilter('event_key', e.target.value)}>
              <option value="">Semua event</option>
              {eventKeys.map((eventKey) => <option key={eventKey} value={eventKey}>{eventKey}</option>)}
            </select>
            <select className="border-gray-300 rounded-md" value={filters.entity_type || ''} onChange={(e) => updateFilter('entity_type', e.target.value)}>
              <option value="">Semua entity</option>
              {entityTypes.map((type) => <option key={type} value={type}>{type}</option>)}
            </select>
            <select className="border-gray-300 rounded-md" value={filters.sensitivity_level || ''} onChange={(e) => updateFilter('sensitivity_level', e.target.value)}>
              <option value="">Semua level</option>
              {levels.map((level) => <option key={level} value={level}>{level}</option>)}
            </select>
            <select className="border-gray-300 rounded-md" value={filters.actor_user_id || ''} onChange={(e) => updateFilter('actor_user_id', e.target.value)}>
              <option value="">Semua actor</option>
              {users.map((user) => <option key={user.id} value={user.id}>{user.name}</option>)}
            </select>
            <input type="date" className="border-gray-300 rounded-md" value={filters.date_from || ''} onChange={(e) => updateFilter('date_from', e.target.value)} />
            <input type="date" className="border-gray-300 rounded-md" value={filters.date_to || ''} onChange={(e) => updateFilter('date_to', e.target.value)} />
          </div>

          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200 text-sm">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-4 py-2 text-left">Waktu</th>
                    <th className="px-4 py-2 text-left">Event</th>
                    <th className="px-4 py-2 text-left">Entity</th>
                    <th className="px-4 py-2 text-left">Action</th>
                    <th className="px-4 py-2 text-left">Level</th>
                    <th className="px-4 py-2 text-left">Actor</th>
                    <th className="px-4 py-2 text-left">Metadata</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {activities.data.map((activity) => (
                    <tr key={activity.id}>
                      <td className="px-4 py-2 whitespace-nowrap">{formatDate(activity.occurred_at)}</td>
                      <td className="px-4 py-2">{activity.event_key}</td>
                      <td className="px-4 py-2">{activity.entity_type}#{activity.entity_id}</td>
                      <td className="px-4 py-2">{activity.action}</td>
                      <td className="px-4 py-2">
                        <span className="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">{activity.sensitivity_level}</span>
                      </td>
                      <td className="px-4 py-2">{activity.actor?.name || '-'}</td>
                      <td className="px-4 py-2"><pre className="whitespace-pre-wrap text-xs">{JSON.stringify(activity.metadata ?? {}, null, 2)}</pre></td>
                    </tr>
                  ))}
                  {activities.data.length === 0 && (
                    <tr>
                      <td colSpan={7} className="px-4 py-6 text-center text-gray-500">Tidak ada activity.</td>
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

function Card({ title, value }) {
  return (
    <div className="bg-white shadow-sm sm:rounded-lg p-4">
      <div className="text-sm text-gray-500">{title}</div>
      <div className="text-2xl font-bold text-gray-900">{value ?? 0}</div>
    </div>
  );
}
