import { Link } from '@inertiajs/react';

/**
 * Breadcrumb Component
 * 
 * Displays navigation breadcrumbs for hierarchical navigation.
 * 
 * @param {Object} props
 * @param {Array<{label: string, href?: string}>} props.items - Breadcrumb items
 * @param {string} props.separator - Custom separator (default: '/')
 * @param {string} props.className - Additional CSS classes
 * 
 * @example
 * <Breadcrumb items={[
 *   { label: 'Home', href: '/' },
 *   { label: 'Products', href: '/products' },
 *   { label: 'Details' }
 * ]} />
 */
export default function Breadcrumb({
    items = [],
    separator = '/',
    className = '',
}) {
    if (!items || items.length === 0) {
        return null;
    }

    return (
        <nav
            className={`flex ${className}`}
            aria-label="Breadcrumb"
        >
            <ol className="inline-flex items-center space-x-1 md:space-x-3">
                {items.map((item, index) => {
                    const isLast = index === items.length - 1;

                    return (
                        <li key={index} className="inline-flex items-center">
                            {index > 0 && (
                                <span className="mx-2 text-secondary-400">
                                    {separator}
                                </span>
                            )}
                            {item.href && !isLast ? (
                                <Link
                                    href={item.href}
                                    className="inline-flex items-center text-sm font-medium text-secondary-700 hover:text-primary-600"
                                >
                                    {item.label}
                                </Link>
                            ) : (
                                <span
                                    className={`text-sm font-medium ${
                                        isLast
                                            ? 'text-secondary-500'
                                            : 'text-secondary-700'
                                    }`}
                                    aria-current={isLast ? 'page' : undefined}
                                >
                                    {item.label}
                                </span>
                            )}
                        </li>
                    );
                })}
            </ol>
        </nav>
    );
}
