import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

export default function Index({ auth, actions, filters, users, eventKeys, entityTypes, levels }) {
  const updateFilter = (key, value) => {
    router.get(route('sensitive-actions.index'), { ...filters, [key]: value }, { preserveState: true, replace: true });
  };

  const formatDate = (value) => (value ? new Date(value).toLocaleString('id-ID') : '-');

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Sensitive Actions</h2>}>
      <Head title="Sensitive Actions" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
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
                    <th className="px-4 py-2 text-left">Level</th>
                    <th className="px-4 py-2 text-left">Reason</th>
                    <th className="px-4 py-2 text-left">Actor</th>
                    <th className="px-4 py-2 text-left">IP</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {actions.data.map((action) => (
                    <tr key={action.id}>
                      <td className="px-4 py-2 whitespace-nowrap">{formatDate(action.occurred_at)}</td>
                      <td className="px-4 py-2">{action.event_key}</td>
                      <td className="px-4 py-2">{action.entity_type}#{action.entity_id}</td>
                      <td className="px-4 py-2">
                        <span className="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">{action.sensitivity_level}</span>
                      </td>
                      <td className="px-4 py-2">{action.sensitivity_reason || '-'}</td>
                      <td className="px-4 py-2">{action.actor?.name || '-'}</td>
                      <td className="px-4 py-2">{action.ip_address || '-'}</td>
                    </tr>
                  ))}
                  {actions.data.length === 0 && (
                    <tr>
                      <td colSpan={7} className="px-4 py-6 text-center text-gray-500">Tidak ada sensitive action.</td>
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
