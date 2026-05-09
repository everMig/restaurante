import React from 'react';

export default class ErrorBoundary extends React.Component {
    constructor(props) {
        super(props);
        this.state = { hasError: false, error: null };
    }

    static getDerivedStateFromError(error) {
        return { hasError: true, error };
    }

    componentDidCatch(error, errorInfo) {
        console.error("ErrorBoundary atrapó un error:", error, errorInfo);
        // Si Swal ya está cargado por el layout principal:
        if (window.toastError) {
            window.toastError("Ocurrió un error inesperado en la interfaz.");
        }
    }

    render() {
        if (this.state.hasError) {
            return (
                <div className="p-8 bg-rose-50 border-2 border-dashed border-rose-200 rounded-3xl text-center m-4">
                    <i className="bi bi-bug-fill text-5xl text-rose-500 mb-4 inline-block" />
                    <h2 className="text-xl font-extrabold text-slate-800 mb-2">¡Algo salió mal!</h2>
                    <p className="text-slate-500 mb-6 text-sm">
                        Ocurrió un error en este componente. Por favor, recarga la página.
                    </p>
                    <button 
                        onClick={() => window.location.reload()} 
                        className="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-xl transition-all shadow-md"
                    >
                        Recargar Página
                    </button>
                    {process.env.NODE_ENV === 'development' && (
                        <pre className="mt-4 text-left text-xs bg-white p-4 rounded-xl text-rose-600 overflow-auto border border-rose-100 max-h-40">
                            {this.state.error?.toString()}
                        </pre>
                    )}
                </div>
            );
        }
        return this.props.children;
    }
}
