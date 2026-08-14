import path from 'path';

let app: any;

try {
  // eslint-disable-next-line @typescript-eslint/no-var-requires
  const mainApp = require(path.join(__dirname, '../src/index'));
  app = mainApp.default || mainApp;
} catch (error) {
  console.error('Failed to load main app:', error);
  throw error;
}

export default app;


