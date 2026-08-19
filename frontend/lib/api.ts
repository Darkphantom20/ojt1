const configuredApiUrl = process.env.NEXT_PUBLIC_API_URL?.trim();

// Use same-origin API routes in production when no separate API URL is configured.
export const apiBase = (configuredApiUrl || '').replace(/\/$/, '').replace(/\/api$/, '');
