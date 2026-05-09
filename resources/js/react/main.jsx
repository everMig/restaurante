import React, { Suspense, lazy } from 'react';
import { createRoot } from 'react-dom/client';
import ErrorBoundary from './components/ErrorBoundary';

const registry = {
    DashboardPulse: lazy(() => import('./pages/DashboardPulse')),
    Login: lazy(() => import('./pages/Login')),
    ProductList: lazy(() => import('./pages/ProductList')),
    PosTableMap: lazy(() => import('./pages/PosTableMap')),
    PosOrder: lazy(() => import('./pages/PosOrder')),
    KitchenDisplay: lazy(() => import('./pages/KitchenDisplay')),
    PosSplit: lazy(() => import('./pages/PosSplit')),
};

const LoadingFallback = () => (
    <div className="w-full h-40 flex flex-col items-center justify-center text-slate-400">
        <div className="w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin mb-3"></div>
        <span className="font-bold text-sm tracking-wide">Cargando módulo...</span>
    </div>
);

document.querySelectorAll('[data-react-component]').forEach((element) => {
    const componentName = element.dataset.reactComponent;
    const Component = registry[componentName];

    if (!Component) {
        return;
    }

    const props = element.dataset.reactProps
        ? JSON.parse(element.dataset.reactProps)
        : {};

    createRoot(element).render(
        <React.StrictMode>
            <ErrorBoundary>
                <Suspense fallback={<LoadingFallback />}>
                    <Component {...props} />
                </Suspense>
            </ErrorBoundary>
        </React.StrictMode>
    );
});