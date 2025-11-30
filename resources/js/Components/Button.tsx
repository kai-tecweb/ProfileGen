import { ButtonHTMLAttributes, ReactNode } from 'react';

interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
    variant?: 'primary' | 'secondary' | 'danger';
    children: ReactNode;
}

export default function Button({
    variant = 'primary',
    className = '',
    children,
    disabled,
    ...props
}: ButtonProps) {
    const baseClasses = 'px-4 py-2 rounded font-medium transition-colors';
    const variantClasses = {
        primary: 'bg-blue-600 hover:bg-blue-700 text-white',
        secondary: 'bg-gray-600 hover:bg-gray-700 text-white',
        danger: 'bg-red-600 hover:bg-red-700 text-white',
    };

    return (
        <button
            className={`${baseClasses} ${variantClasses[variant]} ${
                disabled ? 'opacity-50 cursor-not-allowed' : ''
            } ${className}`}
            disabled={disabled}
            {...props}
        >
            {children}
        </button>
    );
}

