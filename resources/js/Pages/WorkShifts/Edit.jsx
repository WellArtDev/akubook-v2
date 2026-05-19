import { ShiftForm } from './Create';

export default function Edit({ auth, shift }) {
    return <ShiftForm auth={auth} initialData={shift} submitRoute={route('work-shifts.update', shift.id)} method="put" />;
}
