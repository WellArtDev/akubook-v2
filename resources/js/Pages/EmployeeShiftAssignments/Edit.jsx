import { ShiftAssignmentForm } from './Create';

export default function Edit({ auth, employees, shifts, statuses, assignment }) {
    return <ShiftAssignmentForm auth={auth} employees={employees} shifts={shifts} statuses={statuses} assignment={assignment} method="put" submitRoute={route('employee-shift-assignments.update', assignment.id)} />;
}
