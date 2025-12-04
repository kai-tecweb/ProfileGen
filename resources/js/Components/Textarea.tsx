import { forwardRef, TextareaHTMLAttributes } from 'react';

export default forwardRef(function Textarea(
    {
        className = '',
        ...props
    }: TextareaHTMLAttributes<HTMLTextAreaElement>,
    ref: React.Ref<HTMLTextAreaElement>,
) {
    return (
        <textarea
            {...props}
            className={
                'rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 ' +
                className
            }
            ref={ref}
        />
    );
});

