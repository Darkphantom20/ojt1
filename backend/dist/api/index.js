"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
let app;
if (process.env.NODE_ENV === 'production') {
    // Vercel runs the compiled bootstrap from `dist/index.js` after TypeScript build.
    // That file sits next to `dist/src/index.js`, so it must import the built source
    // entry from `./src/index`, not a non-existent root-level route bundle.
    // eslint-disable-next-line @typescript-eslint/no-var-requires
    app = require('./src/index').default;
}
else {
    // Local development uses the TypeScript source directly.
    // eslint-disable-next-line @typescript-eslint/no-var-requires
    app = require('../src/index').default;
}
exports.default = app;
