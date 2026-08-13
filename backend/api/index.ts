let app: any;

if (process.env.NODE_ENV === 'production') {
  // In Vercel production the compiled files live under `dist/`.
  // From `dist/api/index.js` the compiled server entry is at `../index.js`.
  // Require the built entry so runtime loads the compiled JS, not TS sources.
  // Use a relative path that matches the compiled layout.
  // eslint-disable-next-line @typescript-eslint/no-var-requires
  app = require('../index').default;
} else {
  // Local development: load TypeScript source for ts-node-dev workflows.
  // eslint-disable-next-line @typescript-eslint/no-var-requires
  app = require('../src/index').default;
}

export default app;

