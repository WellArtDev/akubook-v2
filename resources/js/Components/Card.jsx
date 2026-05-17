/**
 * Card Component
 * 
 * Container component for grouping related content.
 * 
 * @param {Object} props
 * @param {string} props.title - Card title (optional)
 * @param {React.ReactNode} props.header - Custom header content (optional)
 * @param {React.ReactNode} props.footer - Footer content (optional)
 * @param {React.ReactNode} props.children - Card body content
 * @param {boolean} props.noPadding - Remove default padding (default: false)
 * @param {string} props.className - Additional CSS classes
 * 
 * @example
 * <Card title="User Profile">
 *   <p>User information goes here</p>
 * </Card>
 */
export default function Card({
    title,
    header,
    footer,
    children,
    noPadding = false,
    className = '',
}) {
    return (
        <div className={`bg-white shadow-sm rounded-lg border border-secondary-200 ${className}`}>
            {(title || header) && (
                <div className="px-6 py-4 border-b border-secondary-200">
                    {header || (
                        <h3 className="text-lg font-semibold text-secondary-900">
                            {title}
                        </h3>
                    )}
                </div>
            )}
            
            <div className={noPadding ? '' : 'px-6 py-4'}>
                {children}
            </div>

            {footer && (
                <div className="px-6 py-4 border-t border-secondary-200 bg-secondary-50">
                    {footer}
                </div>
            )}
        </div>
    );
}
