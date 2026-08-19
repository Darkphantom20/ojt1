const configuredApiUrl = process.env.NEXT_PUBLIC_API_URL?.trim();
const defaultApiUrl = process.env.NODE_ENV === 'production'
	? 'https://ojtbackend.vercel.app'
	: 'http://localhost:4000';

export const apiBase = (configuredApiUrl || defaultApiUrl).replace(/\/$/, '').replace(/\/api$/, '');
