import React from 'react';
import { cn } from '../../lib/utils';

function Badge({ className, variant = "default", ...props }) {
  const variants = {
    default: "border-transparent bg-indigo-100 text-indigo-800",
    secondary: "border-transparent bg-slate-100 text-slate-800",
    success: "border-transparent bg-emerald-100 text-emerald-800",
    destructive: "border-transparent bg-red-100 text-red-800",
    outline: "text-slate-950 border-slate-200",
  };

  return (
    <div
      className={cn(
        "inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500",
        variants[variant],
        className
      )}
      {...props}
    />
  );
}

export { Badge };
