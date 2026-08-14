"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const express_1 = __importDefault(require("express"));
const cors_1 = __importDefault(require("cors"));
const dotenv_1 = __importDefault(require("dotenv"));
const path_1 = __importDefault(require("path"));
const auth_1 = __importDefault(require("./routes/auth"));
const student_1 = __importDefault(require("./routes/student"));
const admin_1 = __importDefault(require("./routes/admin"));
const coordinator_1 = __importDefault(require("./routes/coordinator"));
const seed_1 = require("./seed");
dotenv_1.default.config({ path: path_1.default.resolve(__dirname, '../.env') });
const app = (0, express_1.default)();
const port = process.env.PORT || 5000;
const frontendUrls = [
    'http://localhost:3000',
    'http://localhost:3001',
    ...(process.env.FRONTEND_URL ? [process.env.FRONTEND_URL] : []),
    ...((process.env.FRONTEND_URLS || '')
        .split(',')
        .map((url) => url.trim())
        .filter(Boolean)),
    'https://ojt1.vercel.app',
    'https://ojt1monitoringsystem.vercel.app',
];
app.use((0, cors_1.default)({
    origin: (origin, callback) => {
        if (!origin || frontendUrls.includes(origin)) {
            callback(null, true);
        }
        else {
            callback(new Error('Not allowed by CORS'));
        }
    },
    credentials: true,
}));
app.use(express_1.default.json());
app.use('/api/auth', auth_1.default);
app.use('/api/students', student_1.default);
app.use('/api/admin', admin_1.default);
app.use('/api/coordinator', coordinator_1.default);
app.get('/', (_req, res) => {
    res.json({ status: 'ok', service: 'ojt-backend' });
});
app.get('/api/health', (_req, res) => {
    res.json({ status: 'ok' });
});
exports.default = app;
// Only start server in development mode (when not deployed to Vercel)
if (process.env.NODE_ENV !== 'production' && !process.env.VERCEL) {
    (async () => {
        try {
            await (0, seed_1.ensureTestStudent)();
            app.listen(port, () => {
                console.log(`Backend running on http://localhost:${port}`);
            });
        }
        catch (error) {
            console.error('Failed to start server:', error);
            process.exit(1);
        }
    })();
}
