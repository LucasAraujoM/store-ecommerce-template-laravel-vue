const moneyFormatter = new Intl.NumberFormat('es-AR', {
    style: 'currency',
    currency: 'ARS',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
});

export function formatMoney(cents: number, currency = 'ARS'): string {
    if (currency === 'ARS') {
        return moneyFormatter.format(cents / 100);
    }

    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency,
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(cents / 100);
}

export function formatDate(value: string | null | undefined): string {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat('es-AR', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    }).format(new Date(value));
}

export function formatDateTime(value: string | null | undefined): string {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat('es-AR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value));
}

export function formatShortDate(value: string | null | undefined): string {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat('es-AR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    }).format(new Date(value));
}

export const orderStatusLabels: Record<string, string> = {
    pending: 'Pendiente de pago',
    paid: 'Pagado',
    processing: 'En preparación',
    shipped: 'Enviado',
    delivered: 'Entregado',
    canceled: 'Cancelado',
};

export const paymentStatusLabels: Record<string, string> = {
    pending: 'Pendiente',
    paid: 'Aprobado',
    failed: 'Rechazado',
    refunded: 'Reintegrado',
};

export function orderStatusLabel(status: string): string {
    return orderStatusLabels[status] ?? status;
}

export function paymentStatusLabel(status: string): string {
    return paymentStatusLabels[status] ?? status;
}

export function statusClasses(status: string): string {
    switch (status) {
        case 'paid':
        case 'delivered':
            return 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300';
        case 'processing':
            return 'bg-sky-500/10 text-sky-700 dark:text-sky-300';
        case 'shipped':
            return 'bg-indigo-500/10 text-indigo-700 dark:text-indigo-300';
        case 'canceled':
            return 'bg-red-500/10 text-red-700 dark:text-red-300';
        default:
            return 'bg-amber-500/10 text-amber-700 dark:text-amber-300';
    }
}

export function discountPercent(
    priceCents: number,
    compareAtCents: number | null,
): number | null {
    if (!compareAtCents || compareAtCents <= priceCents) {
        return null;
    }

    return Math.round((1 - priceCents / compareAtCents) * 100);
}
