let app: any;

if (process.env.NODE_ENV === 'production') {
  // Use the compiled serverless bundle in Vercel production.
  app = require('../dist/src/index').default;
} else {
  // Keep local TypeScript development behavior.
  app = require('../src/index').default;
}

export default app;

