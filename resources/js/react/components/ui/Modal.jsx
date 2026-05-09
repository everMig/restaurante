import React from 'react';
import { cn } from '../../lib/utils';

/**
 * Modal — Componente de diálogo accesible con animación suave.
 * Reemplaza todos los modales de Bootstrap en el POS.
 */
export function Modal({ open, onClose, children, className }) {
    React.useEffect(() => {
        if (!open) return;
        const handleKey = (e) => e.key === 'Escape' && onClose?.();
        window.addEventListener('keydown', handleKey);
        return () => window.removeEventListener('keydown', handleKey);
    }, [open, onClose]);

    if (!open) return null;

    return (
        <div className="fixed inset-0 z-[200] flex items-center justify-center p-4">
            {/* Backdrop */}
            <div
                className="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
                onClick={onClose}
            />
            {/* Panel */}
            <div
                className={cn(
                    'relative bg-white rounded-3xl shadow-2xl w-full overflow-hidden',
                    'animate-[fadeScaleIn_0.18s_ease-out]',
                    className
                )}
                onClick={(e) => e.stopPropagation()}
            >
                {children}
            </div>
        </div>
    );
}

export function ModalHeader({ children, className, onClose, colorClass = 'bg-slate-50 border-b border-slate-200' }) {
    return (
        <div className={cn('flex items-center justify-between px-6 py-4', colorClass, className)}>
            <div className="font-black text-[1rem] flex items-center gap-2">{children}</div>
            {onClose && (
                <button
                    type="button"
                    onClick={onClose}
                    className="text-current opacity-60 hover:opacity-100 transition-opacity rounded-lg p-1 hover:bg-black/10"
                    aria-label="Cerrar"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" className="w-5 h-5">
                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                    </svg>
                </button>
            )}
        </div>
    );
}

export function ModalBody({ children, className }) {
    return <div className={cn('p-6', className)}>{children}</div>;
}

export function ModalFooter({ children, className }) {
    return <div className={cn('px-6 py-4 bg-slate-50 border-t border-slate-100 flex gap-3 justify-end', className)}>{children}</div>;
}
