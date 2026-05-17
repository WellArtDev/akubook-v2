/**
 * Loading Component
 * 
 * Displays a loading spinner with optional text.
 * 
 * @param {Object} props
 * @param {'sm'|'md'|'lg'|'xl'} props.size - Spinner size (default: 'md')
 * @param {string} props.text - Loading text (optional)
 * @param {boolean} props.fullScreen - Show as fullscreen overlay (default: false)
 * @param {string} props.className - Additional CSS classes
 * 
 * @example
 * <Loading size="lg" text="Loading data..." />
 * <Loading fullScreen text="Please wait..." />
 */
export default function Loading({
    size = 'md',
    text,
    fullScreen = false,
    className = '',
}) {
    const sizeStyles = {
        sm: 'h-4 w-4 border-2',
        md: 'h-8 w-8 border-2',
        lg: 'h-12 w-12 border-3',
        xl: 'h-16 w-16 border-4',
    };

    const spinner = (
        <div className={`flex flex-col items-center justify-center ${className}`}>
            <div
                className={`animate-spin rounded-full border-primary-500 border-t-transparent ${sizeStyles[size]}`}
                role="status"
                aria-label="Loading"
            >
                <span className="sr-only">Loading...</span>
            </div>
            {text && (
                <p className="mt-3 text-sm text-secondary-600">{text}</p>
            )}
        </div>
    );

    if (fullScreen) {
        return (
            <div className="fixed inset-0 z-50 flex items-center justify-center bg-white bg-opacity-75">
                {spinner}
            </div>
        );
    }

    return spinner;
}
