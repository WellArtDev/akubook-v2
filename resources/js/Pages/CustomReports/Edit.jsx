import { CustomReportForm } from './Create';

export default function Edit({ auth, sources, report }) {
    return <CustomReportForm auth={auth} sources={sources} report={report} method="put" submitRoute={route('custom-reports.update', report.id)} />;
}
