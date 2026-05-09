import { clsx } from "clsx";
import { twMerge } from "tailwind-merge";

/**
 * Combina clases de Tailwind de forma segura, resolviendo conflictos.
 * Ideal para construir componentes reutilizables (Design System).
 */
export function cn(...inputs) {
  return twMerge(clsx(inputs));
}
