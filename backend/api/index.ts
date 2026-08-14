let app: any;

if (process.env.NODE_ENV === 'production') {
  // Vercel runs `dist/api/index.js` from the compiled output.
  // From `dist/api/`, the path to `dist/src/index.js` is `../src/index`.
  // eslint-disable-next-line @typescript-eslint/no-var-requires
  app = require('../src/index').default;
} else {
  // Local development uses the TypeScript source directly.
  // eslint-disable-next-line @typescript-eslint/no-var-requires
  app = require('../src/index').default;
}

export default app;

